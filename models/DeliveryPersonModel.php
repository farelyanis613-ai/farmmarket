<?php

require_once __DIR__ . '/../core/Model.php';

class DeliveryPersonModel extends Model
{
    public function getAll()
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE role = ? ORDER BY name ASC');
        $stmt->execute(['delivery']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? AND role = ?');
        $stmt->execute([$id, 'delivery']);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $email, $phone, $address = '')
    {
        try {
            // Check if email already exists
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                return false;
            }

            $password = password_hash('delivery123', PASSWORD_DEFAULT);
            $stmt = $this->db->prepare('
                INSERT INTO users (name, email, password, role, phone, address, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ');
            return $stmt->execute([$name, $email, $password, 'delivery', $phone, $address]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function update($id, $name, $email, $phone, $address = '')
    {
        try {
            // Check if email is already used by another user
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$email, $id]);
            if ($stmt->rowCount() > 0) {
                return false;
            }

            $stmt = $this->db->prepare('
                UPDATE users 
                SET name = ?, email = ?, phone = ?, address = ?
                WHERE id = ? AND role = ?
            ');
            return $stmt->execute([$name, $email, $phone, $address, $id, 'delivery']);
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM users WHERE id = ? AND role = ?');
            return $stmt->execute([$id, 'delivery']);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAvailable()
    {
        $stmt = $this->db->prepare('
            SELECT u.*, COUNT(o.id) as current_orders
            FROM users u
            LEFT JOIN orders o ON u.id = o.delivery_person_id AND o.status IN ("in_progress", "pending", "accepted", "En attente", "en cours", "acceptée", "acceptee")
            WHERE u.role = ?
            GROUP BY u.id
            ORDER BY current_orders ASC, u.name ASC
        ');
        $stmt->execute(['delivery']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
