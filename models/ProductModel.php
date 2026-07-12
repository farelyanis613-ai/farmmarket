<?php

require_once __DIR__ . '/../core/Model.php';

class ProductModel extends Model
{
    private function hasColumn($columnName)
    {
        try {
            $columns = $this->db->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_COLUMN);
            return in_array($columnName, $columns, true);
        } catch (PDOException $e) {
            error_log('[ProductModel] unable to inspect columns: ' . $e->getMessage());
            return false;
        }
    }

    private function getProductsOrderClause()
    {
        return $this->hasColumn('updated_at')
            ? 'ORDER BY COALESCE(p.updated_at, p.created_at) DESC, p.created_at DESC'
            : 'ORDER BY p.created_at DESC';
    }

    public function getAll()
    {
        $stmt = $this->db->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ' . $this->getProductsOrderClause());
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function normalizeCategoryName($value)
    {
        $normalized = trim($value);
        $normalized = str_replace(['Œ', 'œ', 'É', 'È', 'Ê', 'Ë', 'é', 'è', 'ê', 'ë', 'À', 'Â', 'à', 'â'], ['oe', 'oe', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'a', 'a', 'a', 'a'], $normalized);
        return mb_strtolower($normalized, 'UTF-8');
    }

    public function findByCategory($category)
    {
        if (is_numeric($category)) {
            $stmt = $this->db->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? ORDER BY p.created_at DESC');
            $stmt->execute([intval($category)]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $category = $this->normalizeCategoryName($category);
        $stmt = $this->db->prepare(
            'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE LOWER(REPLACE(c.name, "œ", "oe")) = ? ' . $this->getProductsOrderClause()
        );
        $stmt->execute([$category]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($products)) {
            $stmt = $this->db->prepare(
                'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE LOWER(REPLACE(c.name, "œ", "oe")) LIKE ? ' . $this->getProductsOrderClause()
            );
            $stmt->execute(['%' . $category . '%']);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $products;
    }

    public function find($id)
    {
        $stmt = $this->db->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByFarmerId($farmerId)
    {
        $stmt = $this->db->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.farmer_id = ? ' . $this->getProductsOrderClause());
        $stmt->execute([$farmerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $hasUpdatedAt = $this->hasColumn('updated_at');
        if ($hasUpdatedAt) {
            $stmt = $this->db->prepare('INSERT INTO products (name, description, price, stock, category_id, image, farmer_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        } else {
            $stmt = $this->db->prepare('INSERT INTO products (name, description, price, stock, category_id, image, farmer_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        }

        $category = null;
        if (isset($data['category_id']) && (string)$data['category_id'] !== '') {
            $catInt = intval($data['category_id']);
            if ($catInt > 0) {
                $category = $catInt;
            }
        }

        // Verify the category exists; if not, treat as NULL to avoid FK violations
        if ($category !== null) {
            $cstmt = $this->db->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
            $cstmt->execute([$category]);
            $count = $cstmt->fetchColumn();
            if (!$count) {
                $category = null;
            }
        }

        $params = [
            $data['name'],
            $data['description'] ?? '',
            $data['price'],
            $data['stock'],
            $category,
            $data['image'] ?? '',
            $data['farmer_id'] ?? null,
        ];

        return $stmt->execute($params);
    }

    public function update($id, $data)
    {
        $setClauses = [];
        $values = [];

        foreach (['name', 'description', 'price', 'stock', 'category_id', 'image'] as $field) {
            if (isset($data[$field])) {
                $setClauses[] = "{$field} = ?";
                if ($field === 'category_id') {
                    $val = null;
                    if ((string)$data[$field] !== '') {
                        $vInt = intval($data[$field]);
                        if ($vInt > 0) {
                            $val = $vInt;
                        }
                    }
                    $values[] = $val;
                } else {
                    $values[] = $data[$field];
                }
            }
        }

        if (empty($setClauses)) {
            return false;
        }

        if ($this->hasColumn('updated_at')) {
            $setClauses[] = 'updated_at = NOW()';
        }

        $values[] = $id;
        $setStr = implode(', ', $setClauses);
        $stmt = $this->db->prepare("UPDATE products SET {$setStr} WHERE id = ?");
        return $stmt->execute($values);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
