<?php
class UserController extends BaseController {

    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    // ══════════════════════════════════
    // HỒ SƠ CÁ NHÂN (/profile)
    // ══════════════════════════════════

    public function profile(): void {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $user   = $this->userModel->findById($userId);

        if (!$user) {
            flashMessage('danger', 'Không tìm thấy thông tin người dùng.');
            $this->redirect('/dashboard');
        }

        $this->view('user/profile', [
            'title'      => 'Hồ Sơ Cá Nhân — ' . $user['full_name'],
            'user'       => $user,
            'csrf_token' => $this->generateCsrfToken(),
            'errors'     => $_SESSION['profile_errors'] ?? [],
            'old'        => $_SESSION['profile_old']    ?? [],
        ]);

        unset($_SESSION['profile_errors'], $_SESSION['profile_old']);
    }

    public function updateProfile(): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId   = $_SESSION['user_id'];
        $fullName = trim($this->post('full_name', ''));
        $bio      = trim($this->post('bio', ''));

        $errors = [];

        if (mb_strlen($fullName) < 2) {
            $errors['full_name'] = 'Họ tên tối thiểu 2 ký tự';
        }

        if ($errors) {
            $_SESSION['profile_errors'] = $errors;
            $_SESSION['profile_old']    = compact('fullName', 'bio');
            $this->redirect('/profile');
        }

        // Xử lý upload avatar
        $avatarPath = null;
        if (!empty($_FILES['avatar']['name'])) {
            $uploadErrors = validateUpload(
                $_FILES['avatar'],
                2 * 1024 * 1024,
                ['image/jpeg', 'image/png', 'image/gif']
            );

            if ($uploadErrors) {
                $_SESSION['profile_errors'] = ['avatar' => implode(', ', $uploadErrors)];
                $this->redirect('/profile');
            }

            $uploadDir = ROOT_PATH . '/uploads/avatars/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $filename = generateFilename($_FILES['avatar']['name']);
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename)) {
                $avatarPath = 'avatars/' . $filename;
            }
        }

        // Cập nhật DB
        $this->userModel->updateProfile($userId, [
            'full_name' => $fullName,
            'bio'       => $bio,
            'avatar'    => $avatarPath,
        ]);

        // Cập nhật session
        $_SESSION['user_name']   = $fullName;
        if ($avatarPath) {
            $_SESSION['user_avatar'] = $avatarPath;
        }

        flashMessage('success', 'Đã cập nhật hồ sơ thành công!');
        $this->redirect('/profile');
    }

    // ══════════════════════════════════
    // CÀI ĐẶT TÀI KHOẢN (/settings)
    // ══════════════════════════════════

    public function settings(): void {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $user   = $this->userModel->findById($userId);

        $this->view('user/settings', [
            'title'      => 'Cài Đặt Tài Khoản',
            'user'       => $user,
            'csrf_token' => $this->generateCsrfToken(),
            'errors'     => $_SESSION['settings_errors'] ?? [],
            'success'    => $_SESSION['settings_success'] ?? null,
        ]);

        unset($_SESSION['settings_errors'], $_SESSION['settings_success']);
    }

    public function updatePassword(): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId      = $_SESSION['user_id'];
        $oldPassword = $this->post('old_password', '');
        $newPassword = $this->post('new_password', '');
        $confirm     = $this->post('confirm_password', '');

        $errors = [];
        $user   = $this->userModel->findById($userId);

        // Kiểm tra mật khẩu cũ
        if (!password_verify($oldPassword, $user['password_hash'])) {
            $errors['old_password'] = 'Mật khẩu hiện tại không đúng';
        }

        // Validate mật khẩu mới
        $pwErrors = validatePassword($newPassword);
        if ($pwErrors) {
            $errors['new_password'] = implode(', ', $pwErrors);
        } elseif ($newPassword !== $confirm) {
            $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp';
        }

        if ($errors) {
            $_SESSION['settings_errors'] = $errors;
            $this->redirect('/settings#security');
        }

        $this->userModel->updatePassword($userId, $newPassword);

        // Regenerate session sau khi đổi mật khẩu
        session_regenerate_id(true);

        flashMessage('success', '🔒 Đổi mật khẩu thành công!');
        $this->redirect('/settings');
    }
}