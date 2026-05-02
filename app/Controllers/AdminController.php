<?php
class AdminController extends BaseController {

    private UserModel        $userModel;
    private UserSettingModel $settingModel;
    private ProjectModel     $projectModel;

    public function __construct() {
        $this->userModel    = new UserModel();
        $this->settingModel = new UserSettingModel();
        $this->projectModel = new ProjectModel();
    }

    // ══════════════════════════════════════════
    // GET /admin — Trang tổng quan Admin
    // ══════════════════════════════════════════

    public function index(): void {
        $this->requireRole('admin');

        $stats = [
            'total_users'    => $this->userModel->count(),
            'active_users'   => $this->userModel->countActive(),
            'total_projects' => $this->projectModel->countAll(),
            'recent_users'   => $this->userModel->getRecent(5),
        ];

        $this->view('admin/index', [
            'title' => 'Quản trị hệ thống',
            'stats' => $stats,
        ]);
    }

    // ══════════════════════════════════════════
    // GET /admin/users — Danh sách users
    // ══════════════════════════════════════════

    public function users(): void {
        $this->requireRole('admin');

        $page    = max(1, (int)($this->get('page', 1)));
        $search  = $this->get('search', '');
        $role    = $this->get('role', '');
        $status  = $this->get('status', '');
        $perPage = 20;

        $result = $this->userModel->paginate($page, $perPage, [
            'search' => $search,
            'role'   => $role,
            'status' => $status,
        ]);

        $this->view('admin/users', [
            'title'      => 'Quản lý người dùng',
            'users'      => $result['data'],
            'pagination' => $result['pagination'],
            'search'     => $search,
            'filterRole' => $role,
            'filterStatus'=> $status,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    // ══════════════════════════════════════════
    // POST /admin/users/:id/toggle — Kích hoạt / Vô hiệu user
    // ══════════════════════════════════════════

    public function toggleUser(string $userId): void {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $uid = (int)$userId;

        // Không cho vô hiệu chính mình
        if ($uid === (int)$_SESSION['user_id']) {
            flashMessage('danger', 'Bạn không thể vô hiệu hóa tài khoản của chính mình.');
            $this->redirect('/admin/users');
        }

        $user = $this->userModel->findById($uid);
        if (!$user) {
            flashMessage('danger', 'Người dùng không tồn tại.');
            $this->redirect('/admin/users');
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $this->userModel->setActive($uid, $newStatus);

        $action = $newStatus ? 'kích hoạt' : 'vô hiệu hóa';
        flashMessage('success', "Tài khoản <strong>{$user['full_name']}</strong> đã được {$action}.");
        $this->redirect('/admin/users');
    }

    // ══════════════════════════════════════════
    // POST /admin/users/:id/role — Đổi role user
    // ══════════════════════════════════════════

    public function changeRole(string $userId): void {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $uid     = (int)$userId;
        $newRole = $this->post('role', '');
        $allowed = ['admin', 'manager', 'developer', 'reporter', 'viewer'];

        if (!in_array($newRole, $allowed)) {
            flashMessage('danger', 'Role không hợp lệ.');
            $this->redirect('/admin/users');
        }

        // Không tự đổi role của mình
        if ($uid === (int)$_SESSION['user_id']) {
            flashMessage('danger', 'Không thể đổi role của chính mình.');
            $this->redirect('/admin/users');
        }

        $user = $this->userModel->findById($uid);
        if (!$user) {
            flashMessage('danger', 'Người dùng không tồn tại.');
            $this->redirect('/admin/users');
        }

        $this->userModel->setRole($uid, $newRole);
        flashMessage('success', "Đã đổi role của <strong>{$user['full_name']}</strong> thành <strong>{$newRole}</strong>.");
        $this->redirect('/admin/users');
    }

    // ══════════════════════════════════════════
    // POST /admin/users/create — Tạo user mới từ admin
    // ══════════════════════════════════════════

    public function createUser(): void {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $username  = trim($this->post('username', ''));
        $email     = trim($this->post('email', ''));
        $fullName  = trim($this->post('full_name', ''));
        $role      = $this->post('role', 'developer');
        $password  = $_POST['password'] ?? '';

        // Validations
        $errors = [];
        if (mb_strlen($username) < 3) $errors[] = 'Username tối thiểu 3 ký tự.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
        if (mb_strlen($fullName) < 2) $errors[] = 'Họ tên tối thiểu 2 ký tự.';
        if (strlen($password) < 8) $errors[] = 'Mật khẩu tối thiểu 8 ký tự.';
        if ($this->userModel->emailExists($email)) $errors[] = 'Email đã tồn tại.';
        if ($this->userModel->usernameExists($username)) $errors[] = 'Username đã tồn tại.';

        if (!empty($errors)) {
            flashMessage('danger', implode('<br>', $errors));
            $this->redirect('/admin/users');
        }

        $this->userModel->adminCreate([
            'username'  => $username,
            'email'     => $email,
            'full_name' => $fullName,
            'role'      => $role,
            'password'  => $password,
        ]);

        flashMessage('success', "Tài khoản <strong>{$fullName}</strong> đã được tạo.");
        $this->redirect('/admin/users');
    }

    // ══════════════════════════════════════════
    // GET /admin/settings — Cài đặt hệ thống (SMTP, app config)
    // ══════════════════════════════════════════

    public function siteSettings(): void {
        $this->requireRole('admin');

        // Lấy settings của admin user_id=1 như global settings
        // hoặc từ file config nếu có
        $smtpSettings = [
            'smtp_host'     => getenv('SMTP_HOST')     ?: '',
            'smtp_port'     => getenv('SMTP_PORT')     ?: '587',
            'smtp_username' => getenv('SMTP_USER')     ?: '',
            'smtp_password' => '', // Không hiển thị password
            'smtp_from'     => getenv('SMTP_FROM')     ?: '',
            'smtp_from_name'=> getenv('SMTP_FROM_NAME')?: 'BugTracker Pro',
        ];

        $appSettings = [
            'app_name'          => defined('APP_NAME')    ? APP_NAME    : 'BugTracker Pro',
            'app_url'           => defined('APP_URL')     ? APP_URL     : '',
            'max_upload_size'   => '10',
            'session_lifetime'  => '7200',
            'registration_open' => '1',
        ];

        $this->view('admin/settings', [
            'title'       => 'Cài đặt hệ thống',
            'smtp'        => $smtpSettings,
            'appSettings' => $appSettings,
            'csrf_token'  => $this->generateCsrfToken(),
        ]);
    }

    // POST /admin/settings/smtp — Lưu cài đặt SMTP vào .env
    public function saveSmtp(): void {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $envPath = APP_PATH . '/../.env';

        $lines = file_exists($envPath) ? file($envPath, FILE_IGNORE_NEW_LINES) : [];
        $map   = [];
        foreach ($lines as $line) {
            if (str_contains($line, '=') && !str_starts_with(trim($line), '#')) {
                [$k, $v] = explode('=', $line, 2);
                $map[trim($k)] = trim($v);
            }
        }

        // Chỉ cập nhật nếu có giá trị mới
        $keys = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_FROM', 'SMTP_FROM_NAME'];
        $postKeys = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_from', 'smtp_from_name'];

        foreach (array_combine($keys, $postKeys) as $envKey => $postKey) {
            $val = trim($_POST[$postKey] ?? '');
            if ($val !== '') {
                $map[$envKey] = $val;
            }
        }

        // Hanya update password jika diisi
        if (!empty(trim($_POST['smtp_password'] ?? ''))) {
            $map['SMTP_PASS'] = trim($_POST['smtp_password']);
        }

        // Tulis ulang .env
        $output = '';
        foreach ($map as $k => $v) {
            $output .= "{$k}={$v}\n";
        }
        file_put_contents($envPath, $output);

        flashMessage('success', 'Cài đặt SMTP đã được lưu. Khởi động lại server để áp dụng.');
        $this->redirect('/admin/settings');
    }

    // POST /admin/settings/test-smtp — Gửi email test
    public function testSmtp(): void {
        $this->requireRole('admin');

        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['ok' => false, 'error' => 'CSRF'], 403);
        }

        $toEmail = $_SESSION['user_email'] ?? '';
        if (!$toEmail) {
            $this->json(['ok' => false, 'error' => 'Không tìm thấy email admin']);
        }

        // Thử kết nối SMTP đơn giản
        $host = getenv('SMTP_HOST') ?: '';
        $port = (int)(getenv('SMTP_PORT') ?: 587);

        if (empty($host)) {
            $this->json(['ok' => false, 'error' => 'SMTP_HOST chưa được cấu hình']);
        }

        $conn = @fsockopen($host, $port, $errno, $errstr, 5);
        if (!$conn) {
            $this->json(['ok' => false, 'error' => "Không kết nối được đến {$host}:{$port} — {$errstr}"]);
        }
        fclose($conn);

        $this->json(['ok' => true, 'message' => "Kết nối {$host}:{$port} thành công!"]);
    }
}
