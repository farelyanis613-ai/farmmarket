<?php

require_once __DIR__ . '/../core/Model.php';

class UserModel extends Model
{
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $fields = ['name', 'email', 'password', 'role', 'created_at'];
        $values = [$data['name'], $data['email'], $data['password'], $data['role'], date('Y-m-d H:i:s')];
        $placeholders = [];

        if (isset($data['farm_name'])) {
            $fields[] = 'farm_name';
            $values[] = $data['farm_name'];
        }

        if (isset($data['phone'])) {
            $fields[] = 'phone';
            $values[] = $data['phone'];
        }

        if (isset($data['address'])) {
            $fields[] = 'address';
            $values[] = $data['address'];
        }

        $placeholders = array_fill(0, count($fields), '?');
        $fieldsStr = implode(',', $fields);
        $placeholdersStr = implode(',', $placeholders);

        $stmt = $this->db->prepare("INSERT INTO users ({$fieldsStr}) VALUES ({$placeholdersStr})");
        return $stmt->execute($values);
    }

    public function update($id, $data)
    {
        $setClauses = [];
        $values = [];

        foreach (['name', 'email', 'farm_name', 'phone', 'address', 'password'] as $field) {
            if (isset($data[$field])) {
                $setClauses[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($setClauses)) {
            return false;
        }

        $values[] = $id;
        $setStr = implode(', ', $setClauses);
        $stmt = $this->db->prepare("UPDATE users SET {$setStr} WHERE id = ?");
        return $stmt->execute($values);
    }

    public function findByRole($role)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE role = ? ORDER BY id DESC');
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
