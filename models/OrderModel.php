<?php

require_once __DIR__ . '/../core/Model.php';

class OrderModel extends Model
{
    /** Stocke le dernier message d'erreur pour débogage */
    private string $lastError = '';

    public function getLastError(): string
    {
        return $this->lastError;
    }

   public function createOrder(
    $userId,
    $cart,
    $deliveryType    = 'home',
    $deliveryFee     = 0,
    $deliveryAddress = ''
) {
    try {
        $this->db->beginTransaction();

        // Calcul du total depuis le panier
        $total = 0;

        foreach ($cart as $item) {
            $price    = intval($item['product']['price'] ?? 0);
            $quantity = intval($item['quantity'] ?? 1);

            $total += $price * $quantity;
        }

        $total += intval($deliveryFee);


        // Insertion de la commande avec position GPS
        $stmt = $this->db->prepare(
            "INSERT INTO orders 
            (
                user_id,
                total_price,
                delivery_type,
                delivery_fee,
                delivery_address,
                status,
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );

        $stmt->execute([
            $userId,
            $total,
            $deliveryType,
            intval($deliveryFee),
            $deliveryAddress,
            'pending'
        ]);


        $orderId = $this->db->lastInsertId();


        // Insertion des articles + mise à jour du stock
        $itemStmt = $this->db->prepare(
            '
            INSERT INTO order_items 
            (
                order_id,
                product_id,
                quantity,
                unit_price
            )
            VALUES (?, ?, ?, ?)
            '
        );


        $stockStmt = $this->db->prepare(
            '
            UPDATE products 
            SET stock = stock - ? 
            WHERE id = ?
            '
        );


        foreach ($cart as $item) {

            $productId = intval($item['product']['id'] ?? 0);
            $quantity  = intval($item['quantity'] ?? 1);
            $price     = intval($item['product']['price'] ?? 0);


            if ($productId <= 0) {
                throw new Exception(
                    'Produit invalide dans le panier (id manquant).'
                );
            }


            $itemStmt->execute([
                $orderId,
                $productId,
                $quantity,
                $price
            ]);


            $stockStmt->execute([
                $quantity,
                $productId
            ]);
        }


        $this->db->commit();

        return $orderId;


    } catch (Exception $e) {

        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }

        $this->lastError = $e->getMessage();

        error_log(
            '[OrderModel::createOrder] ' . $e->getMessage()
        );

        return false;
    }
}

    public function getByUser($userId)
    {
        $stmt = $this->db->prepare('
            SELECT o.*, COUNT(oi.id) AS items_count
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteByUser($userId)
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM orders WHERE user_id = ?');
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log('[OrderModel::deleteByUser] ' . $e->getMessage());
            return false;
        }
    }

    public function findByFarmerId($farmerId)
    {
        $stmt = $this->db->prepare('
            SELECT DISTINCT o.*,
                   u.name  AS customer_name,
                   u.email AS customer_email,
                   u.phone AS customer_phone,
                   u.address AS customer_address,
                   d.name  AS delivery_person_name,
                   d.phone AS delivery_person_phone,
                   d.email AS delivery_person_email
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p     ON oi.product_id = p.id
            JOIN users u        ON o.user_id = u.id
            LEFT JOIN users d   ON o.delivery_person_id = d.id
            WHERE p.farmer_id = ?
            ORDER BY o.created_at DESC
        ');
        $stmt->execute([$farmerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderItems($orderId)
    {
        $stmt = $this->db->prepare('
            SELECT oi.*, p.name AS product_name, p.price, p.farmer_id, u.name AS farmer_name
            FROM order_items oi
            JOIN products p    ON oi.product_id = p.id
            LEFT JOIN users u  ON p.farmer_id = u.id
            WHERE oi.order_id = ?
        ');
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderItemsByFarmer($orderId, $farmerId)
    {
        $stmt = $this->db->prepare('
            SELECT oi.*, p.name AS product_name, p.price
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ? AND p.farmer_id = ?
        ');
        $stmt->execute([$orderId, $farmerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($orderId)
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserOrderSequence($orderId)
    {
        $stmt = $this->db->prepare('SELECT user_id, created_at FROM orders WHERE id = ?');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM orders WHERE user_id = ? AND (created_at < ? OR (created_at = ? AND id <= ?))'
        );
        $stmt->execute([
            $order['user_id'],
            $order['created_at'],
            $order['created_at'],
            $orderId,
        ]);

        return intval($stmt->fetchColumn());
    }

    public function getByIdForFarmer($orderId, $farmerId)
    {
        $stmt = $this->db->prepare('
            SELECT DISTINCT o.*,
                   u.name AS customer_name,
                   u.email AS customer_email,
                   u.phone,
                   u.address AS customer_address,
                   d.name AS delivery_person_name,
                   d.phone AS delivery_person_phone,
                   d.email AS delivery_person_email,
                   COALESCE(o.delivery_address, u.address) AS address
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p     ON oi.product_id = p.id
            JOIN users u        ON o.user_id = u.id
            LEFT JOIN users d   ON o.delivery_person_id = d.id
            WHERE o.id = ? AND p.farmer_id = ?
        ');
        $stmt->execute([$orderId, $farmerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrderWithDetails($orderId)
    {
        $stmt = $this->db->prepare('
            SELECT o.*,
                   u.name  AS customer_name,
                   u.email AS customer_email,
                   u.phone,
                   u.address,
                   d.name  AS delivery_person_name,
                   d.phone AS delivery_person_phone,
                   d.email AS delivery_person_email,
                   COALESCE(o.delivery_address, u.address) AS address
            FROM orders o
            JOIN users u       ON o.user_id = u.id
            LEFT JOIN users d  ON o.delivery_person_id = d.id
            WHERE o.id = ?
        ');
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function assignDelivery($orderId, $deliveryId)
    {
        $stmt = $this->db->prepare('
            UPDATE orders SET delivery_person_id = ?, status = ? WHERE id = ?
        ');
        return $stmt->execute([$deliveryId, 'in_progress', $orderId]);
    }

    public function updateOrderStatus($orderId, $status)
    {
        $stmt = $this->db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $orderId]);
    }

    public function updateFailedReason($orderId, $reason)
    {
        try {
            $this->ensureFailedReasonColumn();
            $stmt = $this->db->prepare('UPDATE orders SET failed_reason = ? WHERE id = ?');
            return $stmt->execute([$reason, $orderId]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    private function ensureFailedReasonColumn()
    {
        $columns = $this->db->query('SHOW COLUMNS FROM orders')->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('failed_reason', $columns)) {
            $this->db->exec('ALTER TABLE orders ADD COLUMN failed_reason TEXT NULL AFTER status');
        }
    }

    public function findByDeliveryAndStatus($deliveryId, $status = null)
    {
        if ($status === null) {
            $stmt = $this->db->prepare('
                SELECT o.*, u.name AS customer_name, u.phone, u.address
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.delivery_person_id = ?
                ORDER BY o.created_at DESC
            ');
            $stmt->execute([$deliveryId]);
        } else {
            $stmt = $this->db->prepare('
                SELECT o.*, u.name AS customer_name, u.phone, u.address
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.delivery_person_id = ? AND o.status = ?
                ORDER BY o.created_at DESC
            ');
            $stmt->execute([$deliveryId, $status]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findPendingAssignments()
    {
        $stmt = $this->db->prepare('
            SELECT o.*, u.name AS customer_name, u.phone, u.address
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.delivery_person_id IS NULL AND o.status = "pending"
            ORDER BY o.created_at DESC
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}