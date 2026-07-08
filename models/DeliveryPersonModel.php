<?php

require_once __DIR__ . '/../core/Model.php';

class DeliveryPersonModel extends Model
{
    /**
     * S'assure que la colonne `image` existe sur la table `users`.
     * Cette colonne n'existe PAS dans le schéma d'origine (database.sql),
     * ce qui fait échouer create()/update() avec une erreur SQL
     * "Unknown column 'image'" — avalée silencieusement par le catch,
     * d'où le bouton qui "ne fait rien". On la crée ici à la volée,
     * une seule fois par requête.
     */
    private function ensureImageColumn()
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM users LIKE 'image'");
            if ($stmt && $stmt->rowCount() === 0) {
                $this->db->exec('ALTER TABLE users ADD COLUMN image VARCHAR(255) NULL AFTER address');
            }
        } catch (Exception $e) {
            error_log('[DeliveryPersonModel] ensureImageColumn error: ' . $e->getMessage());
        }
    }

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

    public function create($name, $email, $phone, $address = '', $image = '', $password = null)
    {
        $this->ensureImageColumn();

        try {
            // Check if email already exists
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                return false;
            }

            $passwordHash = password_hash(
                ($password !== null && $password !== '') ? $password : 'delivery123',
                PASSWORD_DEFAULT
            );
            $stmt = $this->db->prepare('
                INSERT INTO users (name, email, password, role, phone, address, image, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ');
            return $stmt->execute([$name, $email, $passwordHash, 'delivery', $phone, $address, $image]);
        } catch (Exception $e) {
            error_log('[DeliveryPersonModel] create error: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $name, $email, $phone, $address = '', $image = null)
    {
        $this->ensureImageColumn();

        try {
            // Check if email is already used by another user
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$email, $id]);
            if ($stmt->rowCount() > 0) {
                return false;
            }

            $set = 'name = ?, email = ?, phone = ?, address = ?';
            $values = [$name, $email, $phone, $address];
            if ($image !== null) {
                $set .= ', image = ?';
                $values[] = $image;
            }
            $values[] = $id;
            $values[] = 'delivery';

            $stmt = $this->db->prepare("UPDATE users SET {$set} WHERE id = ? AND role = ?");
            return $stmt->execute($values);
        } catch (Exception $e) {
            error_log('[DeliveryPersonModel] update error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM users WHERE id = ? AND role = ?');
            return $stmt->execute([$id, 'delivery']);
        } catch (Exception $e) {
            // If deletion fails due to foreign-key constraints, anonymize the record instead.
            $fallbackEmail = 'deleted+' . intval($id) . '@farmmarket.local';
            $stmt = $this->db->prepare('UPDATE users SET name = ?, email = ?, phone = ?, address = ?, image = ?, role = ? WHERE id = ? AND role = ?');
            return $stmt->execute([
                'Livreur supprimé',
                $fallbackEmail,
                '',
                '',
                'placeholder.png',
                'deleted_delivery',
                $id,
                'delivery'
            ]);
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