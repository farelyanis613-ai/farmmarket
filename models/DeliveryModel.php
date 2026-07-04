<?php

require_once __DIR__ . '/../core/Model.php';

class DeliveryModel extends Model
{
    public function findByDelivery($deliveryId)
    {
        $stmt = $this->db->prepare('
            SELECT o.*, u.name AS user_name, u.email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.delivery_person_id = ?
            ORDER BY o.created_at DESC
        ');
        $stmt->execute([$deliveryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByDeliveryAndStatus($deliveryId, $status)
    {
        $stmt = $this->db->prepare('
            SELECT o.*, u.name AS user_name, u.email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.delivery_person_id = ? AND o.status = ?
            ORDER BY o.created_at DESC
        ');
        $stmt->execute([$deliveryId, $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByOrderAndDelivery($orderId, $deliveryId)
    {
        $stmt = $this->db->prepare('
            SELECT o.*, u.name AS user_name, u.email, oi.quantity, oi.unit_price, p.name AS product_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE o.id = ? AND o.delivery_person_id = ?
        ');
        $stmt->execute([$orderId, $deliveryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($orderId, $status)
    {
        $stmt = $this->db->prepare('INSERT INTO delivery_status_history (order_id, status, updated_at) VALUES (?, ?, NOW())');
        $stmt->execute([$orderId, $status]);
        
        $stmt = $this->db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $orderId]);
    }

    public function getStats($deliveryId)
    {
        $stmt = $this->db->prepare('
            SELECT 
                COUNT(*) as total_deliveries,
                SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status IN ("pending", "in_progress", "accepted") THEN 1 ELSE 0 END) as pending,
                SUM(total_price) as total_earnings
            FROM orders
            WHERE delivery_person_id = ?
        ');
        $stmt->execute([$deliveryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}