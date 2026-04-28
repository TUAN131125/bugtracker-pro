<?php
class BaseModel {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Chạy query SELECT, trả về nhiều dòng
    protected function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Chạy query SELECT, trả về 1 dòng
    protected function fetchOne(string $sql, array $params = []): array|false {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // Chạy INSERT/UPDATE/DELETE, trả về số dòng bị ảnh hưởng
    protected function execute(string $sql, array $params = []): int {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    // Chạy INSERT, trả về ID vừa tạo
    protected function insert(string $sql, array $params = []): int {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $this->db->lastInsertId();
    }
}