<?php
class ProfileController extends BaseController {

    private UserModel        $userModel;
    private UserSettingModel $settingModel;
    private ActivityLogModel $activityModel;

    // Avatar: chỉ cho phép các định dạng này
    const AVATAR_ALLOWED = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const AVATAR_MAX_SIZE = 2 * 1024 * 1024; // 2MB
    const AVATAR_DIR      = 'avatars';        // trong /uploads/

    public function __construct() {
        $this->userModel     = new UserModel();
        $this->settingModel  = new UserSettingModel();
        $this->activityModel = new ActivityLogModel();
    }

    // ══════════════════════════════════════════
    // GET /profile — Hiện trang profile
    // ══════════════════════════════════════════

    public function index(): void {
        $this->requireAuth();
        $userId  = (int)$_SESSION['user_id'];
        $user    = $this->userModel->findById($userId);
        $settings= $this->settingModel->getAll($userId);

        // Activity log của user (20 bản ghi gần nhất)
        $recentActivity = $this->activityModel->getByUser($userId, 20);

        $this->view('profile/index', [
            'title'          => 'Hồ sơ cá nhân',
            'user'           => $user,
            'settings'       => $settings,
            'recentActivity' => $recentActivity,
            'csrf_token'     => $this->generateCsrfToken(),
        ]);
    }

    // ══════════════════════════════════════════
    // POST /profile/update — Lưu thông tin cá nhân
    // ══════════════════════════════════════════

    public function update(): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId   = (int)$_SESSION['user_id'];
        $user     = $this->userModel->findById($userId);
        $fullName = trim($this->post('full_name', ''));
        $bio      = trim($this->post('bio', ''));
        $timezone = $this->post('timezone', 'Asia/Ho_Chi_Minh');
        $language = $this->post('language', 'vi');

        // Validate
        if (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 100) {
            flashMessage('danger', 'Họ tên phải từ 2–100 ký tự.');
            $this->redirect('/profile');
        }

        // Xử lý upload avatar
        $avatarFilename = $user['avatar'] ?? null;

        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];

            // Validate type & size
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, self::AVATAR_ALLOWED)) {
                flashMessage('danger', 'Chỉ chấp nhận ảnh JPG, PNG, GIF, WebP.');
                $this->redirect('/profile');
            }
            if ($file['size'] > self::AVATAR_MAX_SIZE) {
                flashMessage('danger', 'Ảnh đại diện tối đa 2MB.');
                $this->redirect('/profile');
            }

            // Tạo thư mục nếu chưa có
            $uploadDir = APP_PATH . '/../uploads/' . self::AVATAR_DIR;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Xóa avatar cũ
            if ($user['avatar'] && file_exists(APP_PATH . '/../uploads/' . $user['avatar'])) {
                @unlink(APP_PATH . '/../uploads/' . $user['avatar']);
            }

            $ext     = match ($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
                default      => 'jpg',
            };
            $newName        = self::AVATAR_DIR . '/user_' . $userId . '_' . time() . '.' . $ext;
            $destPath       = APP_PATH . '/../uploads/' . $newName;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                flashMessage('danger', 'Upload ảnh thất bại. Vui lòng thử lại.');
                $this->redirect('/profile');
            }
            $avatarFilename = $newName;
        }

        // Cập nhật users
        $this->userModel->updateProfile($userId, [
            'full_name' => $fullName,
            'bio'       => $bio ?: null,
            'avatar'    => $avatarFilename,
        ]);

        // Cập nhật user_settings
        $this->settingModel->setMany($userId, [
            'timezone' => $timezone,
            'language' => $language,
        ]);

        // Cập nhật session
        $_SESSION['user_name']   = $fullName;
        $_SESSION['user_avatar'] = $avatarFilename;

        flashMessage('success', 'Hồ sơ đã được cập nhật!');
        $this->redirect('/profile');
    }

    // ══════════════════════════════════════════
    // POST /profile/password — Đổi mật khẩu
    // ══════════════════════════════════════════

    public function changePassword(): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId      = (int)$_SESSION['user_id'];
        $user        = $this->userModel->findById($userId);
        $currentPw   = $_POST['current_password']      ?? '';
        $newPw       = $_POST['new_password']           ?? '';
        $confirmPw   = $_POST['confirm_password']       ?? '';

        // Xác minh mật khẩu hiện tại
        if (!password_verify($currentPw, $user['password_hash'])) {
            flashMessage('danger', 'Mật khẩu hiện tại không đúng.');
            $this->redirect('/profile#security');
        }

        // Validate mật khẩu mới
        if (strlen($newPw) < 8) {
            flashMessage('danger', 'Mật khẩu mới phải có ít nhất 8 ký tự.');
            $this->redirect('/profile#security');
        }

        if ($newPw !== $confirmPw) {
            flashMessage('danger', 'Xác nhận mật khẩu không khớp.');
            $this->redirect('/profile#security');
        }

        if ($newPw === $currentPw) {
            flashMessage('warning', 'Mật khẩu mới phải khác mật khẩu cũ.');
            $this->redirect('/profile#security');
        }

        $this->userModel->updatePassword($userId, $newPw);
        $this->activityModel->log($userId, 'password_changed', [
            'project_id' => null,
            'bug_id'     => null,
            'old' => [], 'new' => [],
        ]);

        flashMessage('success', 'Mật khẩu đã được đổi thành công!');
        $this->redirect('/profile#security');
    }

    // ══════════════════════════════════════════
    // POST /profile/theme — Chuyển light/dark
    // ══════════════════════════════════════════

    public function toggleTheme(): void {
        $this->requireAuth();

        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['ok' => false, 'error' => 'CSRF'], 403);
        }

        $userId   = (int)$_SESSION['user_id'];
        $current  = $this->settingModel->get($userId, 'theme', 'light');
        $newTheme = $current === 'dark' ? 'light' : 'dark';

        $this->settingModel->set($userId, 'theme', $newTheme);
        $_SESSION['user_theme'] = $newTheme;

        $this->json(['ok' => true, 'theme' => $newTheme]);
    }

    // ══════════════════════════════════════════
    // GET /settings — Cài đặt thông báo
    // ══════════════════════════════════════════

    public function settings(): void {
        $this->requireAuth();
        $userId   = (int)$_SESSION['user_id'];
        $settings = $this->settingModel->getAll($userId);

        $this->view('profile/settings', [
            'title'      => 'Cài đặt thông báo',
            'settings'   => $settings,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    // POST /settings/save
    public function saveSettings(): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int)$_SESSION['user_id'];
        $keys   = [
            'notify_assigned', 'notify_comment', 'notify_status_change',
            'notify_mention', 'notify_sprint', 'email_digest',
        ];

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = isset($_POST[$key]) ? '1' : '0';
        }

        $this->settingModel->setMany($userId, $data);
        flashMessage('success', 'Cài đặt thông báo đã được lưu!');
        $this->redirect('/settings');
    }
}
