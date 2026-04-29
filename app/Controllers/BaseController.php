<?php
class BaseController {

    // Render một view file, truyền data vào
    protected function view(string $viewPath, array $data = [], bool $withLayout = true): void {
        // Giải nén array thành biến (vd: $data['title'] → $title)
        extract($data);

        $viewFile = APP_PATH . '/Views/pages/' . $viewPath . '.php';

        if (!file_exists($viewFile)) {
            die("View không tìm thấy: $viewPath");
        }

        if ($withLayout) {
            // Ghi nội dung view vào buffer, sau đó nhét vào layout
            ob_start();
            require $viewFile;
            $content = ob_get_clean();

            require APP_PATH . '/Views/layouts/main.php';
        } else {
            require $viewFile;
        }
    }

    // Redirect đến URL khác
    protected function redirect(string $path): void {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    // Trả về JSON (dùng cho AJAX)
    protected function json(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Lấy dữ liệu POST đã được làm sạch
    protected function post(string $key, mixed $default = null): mixed {
        return isset($_POST[$key]) ? $this->sanitize($_POST[$key]) : $default;
    }

    // Lấy dữ liệu GET đã được làm sạch
    protected function get(string $key, mixed $default = null): mixed {
        return isset($_GET[$key]) ? $this->sanitize($_GET[$key]) : $default;
    }

    // Làm sạch input cơ bản
    private function sanitize(mixed $value): mixed {
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }
        return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
    }

    // Kiểm tra user đã đăng nhập chưa, nếu chưa thì redirect login
    protected function requireAuth(): void {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    // Kiểm tra role (dùng ở các trang admin/manager)
    protected function requireRole(string ...$roles): void {
        $this->requireAuth();
        if (!in_array($_SESSION['user_role'] ?? '', $roles)) {
            http_response_code(403);
            die('<h1>403 — Bạn không có quyền truy cập trang này</h1>');
        }
    }

    // Tạo CSRF token mới và lưu vào session
    protected function generateCsrfToken(): string {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    // Xác minh CSRF token từ form POST
    protected function verifyCsrf(): void {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            http_response_code(403);
            die('CSRF token không hợp lệ. Vui lòng thử lại.');
        }
        // Xóa token sau khi dùng (one-time use)
        unset($_SESSION['csrf_token']);
    }

    protected function viewFull(string $viewPath, array $data = []): void {
        extract($data);
        require APP_PATH . '/Views/pages/' . $viewPath . '.php';
    }

    // Render landing page — không layout, không sidebar, file nằm thẳng trong /Views/
    protected function viewLanding(string $viewPath, array $data = []): void {
        extract($data);
        $viewFile = APP_PATH . '/Views/' . $viewPath . '.php';
        if (!file_exists($viewFile)) {
            die("Landing view không tìm thấy: $viewPath");
        }
        require $viewFile;
    }

    protected function viewAuth(string $viewPath, array $data = []): void {
        extract($data);
        $viewFile = APP_PATH . '/Views/pages/' . $viewPath . '.php';
        if (!file_exists($viewFile)) {
            die("View auth không tìm thấy: $viewPath");
        }
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/auth.php';
    }
}