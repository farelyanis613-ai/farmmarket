<?php

require_once __DIR__ . '/../core/Model.php';

class CategoryModel extends Model
{
    public function all()
    {
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name)
    {
        $stmt = $this->db->prepare('INSERT INTO categories (name) VALUES (?)');
        return $stmt->execute([trim($name)]);
    }

    public function update($id, $name)
    {
        $stmt = $this->db->prepare('UPDATE categories SET name = ? WHERE id = ?');
        return $stmt->execute([trim($name), $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
