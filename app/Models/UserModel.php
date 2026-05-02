<?php
class UserModel extends BaseModel {

    // Tìm user theo email hoặc username (dùng cho login)
    public function findByEmailOrUsername(string $input): array|false {
        return $this->fetchOne(
            "SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1",
            [$input, $input]
        );
    }

    // Tìm theo email
    public function findByEmail(string $email): array|false {
        return $this->fetchOne(
            "SELECT * FROM users WHERE email = ? LIMIT 1",
            [$email]
        );
    }

    // Tìm theo username
    public function findByUsername(string $username): array|false {
        return $this->fetchOne(
            "SELECT * FROM users WHERE username = ? LIMIT 1",
            [$username]
        );
    }

    // Tìm theo ID
    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT * FROM users WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    // Tạo user mới — trả về ID
    public function create(array $data): int {
        // Role mặc định là 'manager' nếu tự đăng ký
        // (người tự đăng ký = owner workspace = có quyền tạo project)
        $role = $data['role'] ?? 'manager';

        return $this->insert(
            "INSERT INTO users
                (username, email, password_hash, full_name, role, is_active, email_verified)
            VALUES (?, ?, ?, ?, ?, 1, 0)",
            [
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
                $data['full_name'],
                $role,
            ]
        );
    }

    // Cập nhật thông tin profile
    public function updateProfile(int $userId, array $data): bool {
        return $this->execute(
            "UPDATE users SET full_name = ?, bio = ?, avatar = ? WHERE id = ?",
            [$data['full_name'], $data['bio'] ?? null, $data['avatar'] ?? null, $userId]
        ) > 0;
    }

    // Cập nhật lần đăng nhập cuối
    public function updateLastLogin(int $userId): void {
        $this->execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$userId]
        );
    }

    // Đổi mật khẩu
    public function updatePassword(int $userId, string $newPassword): bool {
        return $this->execute(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]), $userId]
        ) > 0;
    }

    // Kiểm tra email đã tồn tại chưa (dùng cho AJAX check realtime)
    public function emailExists(string $email, int $excludeId = 0): bool {
        $row = $this->fetchOne(
            "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1",
            [$email, $excludeId]
        );
        return (bool) $row;
    }

    // Kiểm tra username đã tồn tại chưa
    public function usernameExists(string $username, int $excludeId = 0): bool {
        $row = $this->fetchOne(
            "SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1",
            [$username, $excludeId]
        );
        return (bool) $row;
    }

    // ── Admin methods ─────────────────────────────────────────────

    public function count(): int {
        $row = $this->fetchOne("SELECT COUNT(*) AS cnt FROM users", []);
        return (int)($row['cnt'] ?? 0);
    }

    public function countActive(): int {
        $row = $this->fetchOne("SELECT COUNT(*) AS cnt FROM users WHERE is_active = 1", []);
        return (int)($row['cnt'] ?? 0);
    }

    public function getRecent(int $limit = 5): array {
        return $this->fetchAll(
            "SELECT id, username, email, full_name, role, is_active, avatar, created_at
             FROM users ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function paginate(int $page, int $perPage, array $filters = []): array {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = "(full_name LIKE ? OR email LIKE ? OR username LIKE ?)";
            $term     = '%' . $filters['search'] . '%';
            $params   = array_merge($params, [$term, $term, $term]);
        }
        if (!empty($filters['role'])) {
            $where[]  = "role = ?";
            $params[] = $filters['role'];
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[]  = "is_active = ?";
            $params[] = (int)$filters['status'];
        }

        $whereSQL = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $totalRow = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM users WHERE {$whereSQL}", $params
        );
        $total = (int)($totalRow['cnt'] ?? 0);

        $data = $this->fetchAll(
            "SELECT id, username, email, full_name, role, is_active, avatar, created_at, last_login
             FROM users WHERE {$whereSQL}
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return [
            'data'       => $data,
            'pagination' => [
                'total'       => $total,
                'per_page'    => $perPage,
                'current'     => $page,
                'total_pages' => max(1, (int)ceil($total / $perPage)),
            ],
        ];
    }

    public function setActive(int $userId, int $status): void {
        $this->execute("UPDATE users SET is_active = ? WHERE id = ?", [$status, $userId]);
    }

    public function setRole(int $userId, string $role): void {
        $this->execute("UPDATE users SET role = ? WHERE id = ?", [$role, $userId]);
    }

    public function adminCreate(array $data): int {
        return $this->insert(
            "INSERT INTO users (username, email, password_hash, full_name, role, is_active, email_verified)
             VALUES (?, ?, ?, ?, ?, 1, 1)",
            [
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
                $data['full_name'],
                $data['role'] ?? 'developer',
            ]
        );
    }
}