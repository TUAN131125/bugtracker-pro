<?php
class AuthController extends BaseController {

    private UserModel      $userModel;
    private WorkspaceModel $wsModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->wsModel   = new WorkspaceModel();
    }

    // ══════════════════════════════════
    // REGISTER — Bước 1: Email + Password
    // ══════════════════════════════════

    public function registerForm(): void {
        // Nếu đã đăng nhập rồi thì về dashboard
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        // Tạo captcha số cho register
        $n1 = rand(1, 9);
        $n2 = rand(1, 9);
        $_SESSION['captcha_sum'] = $n1 + $n2;

        $this->viewAuth('auth/register', [
            'title'       => 'Tạo tài khoản — BugTracker Pro',
            'step'        => 1,
            'csrf_token'  => $this->generateCsrfToken(),
            'captcha_n1'  => $n1,
            'captcha_n2'  => $n2,
            'errors'      => $_SESSION['reg_errors'] ?? [],
            'old'         => $_SESSION['reg_old']    ?? [],
        ]);

        // Xóa sau khi đã truyền vào view
        unset($_SESSION['reg_errors'], $_SESSION['reg_old']);
    }

    public function register(): void {
        $this->verifyCsrf();

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['password_confirm'] ?? '');
        $agree    = $_POST['agree_terms'] ?? '';

        $errors = [];

        // Validate
        if (!validateEmail($email))
            $errors['email'] = 'Email không hợp lệ';
        elseif ($this->userModel->emailExists($email))
            $errors['email'] = 'Email này đã được dùng';

        $pwErrors = validatePassword($password);
        if ($pwErrors) $errors['password'] = implode(', ', $pwErrors);
        elseif ($password !== $confirm)
            $errors['password_confirm'] = 'Mật khẩu xác nhận không khớp';

        if (!$agree)
            $errors['agree'] = 'Bạn cần đồng ý điều khoản để tiếp tục';

        // Kiểm tra captcha đơn giản (tổng 2 số)
        $captchaAnswer = (int) ($_POST['captcha_answer'] ?? -1);
        $captchaExpect = (int) ($_SESSION['captcha_sum'] ?? -999);
        if ($captchaAnswer !== $captchaExpect)
            $errors['captcha'] = 'Kết quả tính toán không đúng';

        if ($errors) {
            $_SESSION['reg_errors'] = $errors;
            $_SESSION['reg_old']    = ['email' => $email];
            $this->redirect('/register');
        }

        // Lưu tạm vào session, chưa lưu DB (chờ bước 2 có thêm username)
        $_SESSION['reg_step1'] = [
            'email'    => $email,
            'password' => $password, // hash ở bước cuối
        ];

        $this->redirect('/register/profile');
    }

    // ══════════════════════════════════
    // REGISTER — Bước 2: Profile
    // ══════════════════════════════════

    public function profileForm(): void {
        // Phải qua bước 1 trước
        if (empty($_SESSION['reg_step1'])) {
            $this->redirect('/register');
        }

        $this->viewAuth('auth/register_profile', [
            'title'      => 'Thông tin cá nhân — Bước 2/4',
            'step'       => 2,
            'csrf_token' => $this->generateCsrfToken(),
            'errors'     => $_SESSION['reg_errors'] ?? [],
            'old'        => $_SESSION['reg_old']    ?? [],
        ]);

        unset($_SESSION['reg_errors'], $_SESSION['reg_old']);
    }

    public function saveProfile(): void {
        if (empty($_SESSION['reg_step1'])) $this->redirect('/register');
        $this->verifyCsrf();

        $fullName = trim($_POST['full_name'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));
        $errors   = [];

        if (mb_strlen($fullName) < 2)
            $errors['full_name'] = 'Họ tên tối thiểu 2 ký tự';

        $unErrors = validateUsername($username);
        if ($unErrors) $errors['username'] = implode(', ', $unErrors);
        elseif ($this->userModel->usernameExists($username))
            $errors['username'] = 'Username này đã được dùng';

        if ($errors) {
            $_SESSION['reg_errors'] = $errors;
            $_SESSION['reg_old']    = compact('fullName', 'username');
            $this->redirect('/register/profile');
        }

        // Xử lý upload avatar (optional)
        $avatarPath = null;
        if (!empty($_FILES['avatar']['name'])) {
            $uploadErrors = validateUpload($_FILES['avatar'], 2 * 1024 * 1024, ['image/jpeg', 'image/png']);
            if (!$uploadErrors) {
                $filename   = generateFilename($_FILES['avatar']['name']);
                $uploadDir  = ROOT_PATH . '/uploads/avatars/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename)) {
                    $avatarPath = 'avatars/' . $filename;
                }
            }
        }

        // Tạo user trong DB ở bước này
        $userId = $this->userModel->create([
            'email'     => $_SESSION['reg_step1']['email'],
            'password'  => $_SESSION['reg_step1']['password'],
            'username'  => $username,
            'full_name' => $fullName,
            'role'      => 'manager', // mặc định role manager cho user tự đăng ký (có quyền tạo project)
        ]);

        if ($avatarPath) {
            $this->userModel->updateProfile($userId, [
                'full_name' => $fullName,
                'avatar'    => $avatarPath,
            ]);
        }

        // Lưu vào session để dùng bước tiếp
        $_SESSION['reg_user_id']  = $userId;
        $_SESSION['reg_step1']    = null; // xóa password khỏi session
        $_SESSION['reg_step2']    = ['full_name' => $fullName, 'username' => $username];

        $this->redirect('/register/workspace');
    }

    // ══════════════════════════════════
    // REGISTER — Bước 3: Workspace
    // ══════════════════════════════════

    public function workspaceForm(): void {
        if (empty($_SESSION['reg_user_id'])) $this->redirect('/register');

        $this->viewAuth('auth/register_workspace', [
            'title'      => 'Tạo Workspace — Bước 3/4',
            'step'       => 3,
            'csrf_token' => $this->generateCsrfToken(),
            'errors'     => $_SESSION['reg_errors'] ?? [],
            'old'        => $_SESSION['reg_old']    ?? [],
            // Gợi ý tên workspace từ tên user
            'suggested_name' => ($_SESSION['reg_step2']['full_name'] ?? '') . "'s Workspace",
        ]);

        unset($_SESSION['reg_errors'], $_SESSION['reg_old']);
    }

    public function saveWorkspace(): void {
        if (empty($_SESSION['reg_user_id'])) $this->redirect('/register');

        // Bỏ qua — đăng nhập luôn rồi làm sau
        if (isset($_POST['skip'])) {
            $this->loginUserById($_SESSION['reg_user_id']);
            $this->cleanupRegSession();
            $this->redirect('/dashboard');
        }

        $this->verifyCsrf();

        $name = trim($_POST['workspace_name'] ?? '');
        $type = $_POST['workspace_type'] ?? 'team';
        $slug = slugify(trim($_POST['workspace_slug'] ?? $name));

        $errors = [];
        if (mb_strlen($name) < 2)
            $errors['workspace_name'] = 'Tên workspace tối thiểu 2 ký tự';
        if (empty($slug))
            $errors['workspace_slug'] = 'Slug không hợp lệ';
        elseif ($this->wsModel->slugExists($slug))
            $errors['workspace_slug'] = 'Slug này đã được dùng, thử tên khác';
        if (!in_array($type, ['personal', 'team', 'enterprise']))
            $type = 'team';

        if ($errors) {
            $_SESSION['reg_errors'] = $errors;
            $_SESSION['reg_old']    = compact('name', 'slug', 'type');
            $this->redirect('/register/workspace');
        }

        $wsId = $this->wsModel->create([
            'name'     => $name,
            'slug'     => $slug,
            'type'     => $type,
            'owner_id' => $_SESSION['reg_user_id'],
        ]);

        // Thêm owner vào workspace_members
        $this->wsModel->addMember($wsId, $_SESSION['reg_user_id'], 'admin');

        $_SESSION['reg_workspace_id'] = $wsId;

        $this->redirect('/register/invite');
    }

    // ══════════════════════════════════
    // REGISTER — Bước 4: Mời thành viên
    // ══════════════════════════════════

    public function inviteForm(): void {
        if (empty($_SESSION['reg_user_id'])) $this->redirect('/register');

        $this->viewAuth('auth/register_invite', [
            'title'      => 'Mời thành viên — Bước 4/4',
            'step'       => 4,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    public function sendInvite(): void {
        if (empty($_SESSION['reg_user_id'])) $this->redirect('/register');

        // Bỏ qua bước mời → vào dashboard luôn
        if (isset($_POST['skip'])) {
            $this->finishRegistration();
        }

        $this->verifyCsrf();

        // Lấy danh sách email (cách nhau bằng dấu , hoặc xuống dòng)
        $rawEmails = $_POST['invite_emails'] ?? '';
        $role      = $_POST['invite_role']   ?? 'developer';
        $emails    = array_filter(
            array_map('trim', preg_split('/[\n,]+/', $rawEmails))
        );

        $wsId    = $_SESSION['reg_workspace_id'] ?? null;
        $inviter = $_SESSION['reg_user_id'];

        if ($wsId && $emails) {
            $wsModel = new WorkspaceModel();
            foreach ($emails as $email) {
                if (!validateEmail($email)) continue;

                $token = bin2hex(random_bytes(32));
                $wsModel->createInvitation([
                    'workspace_id' => $wsId,
                    'email'        => $email,
                    'role'         => $role,
                    'token'        => $token,
                    'invited_by'   => $inviter,
                ]);

                // Gửi email (nếu đã cấu hình SMTP)
                $this->sendInvitationEmail($email, $token);
            }
        }

        $this->finishRegistration();
    }

    // Hoàn tất đăng ký — đăng nhập và redirect
    private function finishRegistration(): void {
        $this->loginUserById($_SESSION['reg_user_id']);
        $this->cleanupRegSession();
        flashMessage('success', '🎉 Chào mừng bạn đến với BugTracker Pro!');
        $this->redirect('/dashboard');
    }

    private function cleanupRegSession(): void {
        unset(
            $_SESSION['reg_step1'],
            $_SESSION['reg_step2'],
            $_SESSION['reg_user_id'],
            $_SESSION['reg_workspace_id']
        );
    }

    // ══════════════════════════════════
    // LOGIN
    // ══════════════════════════════════

    public function loginForm(): void {
        if (!empty($_SESSION['user_id'])) $this->redirect('/dashboard');

        // Tạo captcha số cho login
        $n1 = rand(1, 9);
        $n2 = rand(1, 9);
        $_SESSION['captcha_sum'] = $n1 + $n2;

        $this->viewAuth('auth/login', [
            'title'       => 'Đăng nhập — BugTracker Pro',
            'step'        => null,
            'csrf_token'  => $this->generateCsrfToken(),
            'errors'      => $_SESSION['login_errors'] ?? [],
            'old'         => $_SESSION['login_old']    ?? [],
            'captcha_n1'  => $n1,
            'captcha_n2'  => $n2,
        ]);

        unset($_SESSION['login_errors'], $_SESSION['login_old']);
    }

    public function login(): void {
        $this->verifyCsrf();

        $input    = trim($_POST['login_input'] ?? '');
        $password = $_POST['password']         ?? '';
        $remember = !empty($_POST['remember']);
        $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // ── Rate limiting ──
        $lockKey     = 'login_lock_'  . md5($ip);
        $attemptsKey = 'login_tries_' . md5($ip);

        if (!empty($_SESSION[$lockKey]) && $_SESSION[$lockKey] > time()) {
            $wait = ceil(($_SESSION[$lockKey] - time()) / 60);
            $_SESSION['login_errors'] = ['general' => "Quá nhiều lần thử. Vui lòng đợi {$wait} phút."];
            $this->redirect('/login');
        }

        $errors = [];

        if (empty($input))    $errors['login_input'] = 'Vui lòng nhập email hoặc username';
        if (empty($password)) $errors['password']    = 'Vui lòng nhập mật khẩu';

        if (!$errors) {
            $user = $this->userModel->findByEmailOrUsername($input);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                // Tăng số lần thử sai
                $_SESSION[$attemptsKey] = ($_SESSION[$attemptsKey] ?? 0) + 1;

                if ($_SESSION[$attemptsKey] >= MAX_LOGIN_ATTEMPTS) {
                    $_SESSION[$lockKey]      = time() + LOCKOUT_TIME;
                    $_SESSION[$attemptsKey]  = 0;
                    $errors['general'] = 'Sai quá 5 lần. Tài khoản bị khóa 15 phút.';
                } else {
                    $remaining = MAX_LOGIN_ATTEMPTS - $_SESSION[$attemptsKey];
                    $errors['general'] = "Email/username hoặc mật khẩu không đúng. Còn {$remaining} lần thử.";
                }

            } elseif (!$user['is_active']) {
                $errors['general'] = 'Tài khoản này đã bị vô hiệu hóa. Liên hệ admin.';

            } else {
                // ── Đăng nhập thành công ──
                unset($_SESSION[$attemptsKey], $_SESSION[$lockKey]);

                $this->loginUserById($user['id'], $remember);
                $this->userModel->updateLastLogin($user['id']);

                flashMessage('success', 'Đăng nhập thành công! Chào mừng trở lại.');
                $this->redirect('/dashboard');
            }
        }

        $_SESSION['login_errors'] = $errors;
        $_SESSION['login_old']    = ['login_input' => $input];
        $this->redirect('/login');
    }

    // ══════════════════════════════════
    // LOGOUT
    // ══════════════════════════════════

    public function logout(): void {
        session_destroy();
        session_start();
        flashMessage('info', 'Bạn đã đăng xuất thành công.');
        $this->redirect('/login');
    }

    // ══════════════════════════════════
    // QUÊN MẬT KHẨU
    // ══════════════════════════════════

    public function forgotForm(): void {
        $this->viewAuth('auth/forgot', [
            'title'      => 'Quên mật khẩu',
            'step'       => null,
            'csrf_token' => $this->generateCsrfToken(),
            'errors'     => $_SESSION['forgot_errors'] ?? [],
            'success'    => $_SESSION['forgot_success'] ?? null,
        ]);
        unset($_SESSION['forgot_errors'], $_SESSION['forgot_success']);
    }

    public function sendReset(): void {
        $this->verifyCsrf();

        $email = trim($_POST['email'] ?? '');

        if (!validateEmail($email)) {
            $_SESSION['forgot_errors'] = ['email' => 'Email không hợp lệ'];
            $this->redirect('/forgot-password');
        }

        $user = $this->userModel->findByEmail($email);

        // Không tiết lộ email có tồn tại hay không (bảo mật)
        if ($user) {
            $resetModel = new PasswordResetModel();
            $token      = $resetModel->create($email);
            $this->sendResetEmail($email, $token);
        }

        // Luôn hiện thông báo thành công dù email có tồn tại hay không
        $_SESSION['forgot_success'] = 'Nếu email tồn tại, chúng tôi đã gửi link đặt lại mật khẩu. Kiểm tra hộp thư của bạn.';
        $this->redirect('/forgot-password');
    }

    public function resetForm(): void {
        $token      = $_GET['token'] ?? '';
        $resetModel = new PasswordResetModel();
        $record     = $resetModel->findValidToken($token);

        if (!$record) {
            flashMessage('danger', 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            $this->redirect('/forgot-password');
        }

        $this->viewAuth('auth/reset', [
            'title'      => 'Đặt lại mật khẩu',
            'step'       => null,
            'token'      => $token,
            'csrf_token' => $this->generateCsrfToken(),
            'errors'     => $_SESSION['reset_errors'] ?? [],
        ]);
        unset($_SESSION['reset_errors']);
    }

    public function doReset(): void {
        $this->verifyCsrf();

        $token    = $_POST['token']            ?? '';
        $password = $_POST['password']         ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        $resetModel = new PasswordResetModel();
        $record     = $resetModel->findValidToken($token);

        if (!$record) {
            flashMessage('danger', 'Token không hợp lệ hoặc đã hết hạn.');
            $this->redirect('/forgot-password');
        }

        $errors   = [];
        $pwErrors = validatePassword($password);
        if ($pwErrors)             $errors['password'] = implode(', ', $pwErrors);
        elseif ($password !== $confirm) $errors['password_confirm'] = 'Mật khẩu không khớp';

        if ($errors) {
            $_SESSION['reset_errors'] = $errors;
            $this->redirect('/reset-password?token=' . urlencode($token));
        }

        // Cập nhật mật khẩu mới
        $user = $this->userModel->findByEmail($record['email']);
        if ($user) {
            $this->userModel->updatePassword($user['id'], $password);
            $resetModel->markUsed($token);
            flashMessage('success', 'Đặt lại mật khẩu thành công! Đăng nhập với mật khẩu mới.');
        }

        $this->redirect('/login');
    }

    // ══════════════════════════════════
    // AJAX CHECK (realtime validation)
    // ══════════════════════════════════

    // GET /api/check-email?email=xxx
    public function checkEmail(): void {
        $email  = trim($_GET['email'] ?? '');
        $exists = validateEmail($email) && $this->userModel->emailExists($email);
        $this->json(['available' => !$exists]);
    }

    // GET /api/check-username?username=xxx
    public function checkUsername(): void {
        $username = strtolower(trim($_GET['username'] ?? ''));
        $errors   = validateUsername($username);
        $exists   = empty($errors) && $this->userModel->usernameExists($username);
        $this->json([
            'available' => !$exists && empty($errors),
            'errors'    => $errors,
        ]);
    }

    // POST /api/slug-from-name
    public function slugFromName(): void {
        $name = trim($_POST['name'] ?? '');
        $slug = slugify($name);
        $this->json([
            'slug'      => $slug,
            'available' => !$this->wsModel->slugExists($slug),
        ]);
    }

    // ══════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════

    // Tạo session sau khi đăng nhập thành công
    private function loginUserById(int $userId, bool $remember = false): void {
        session_regenerate_id(true);

        $user = $this->userModel->findById($userId);
        if (!$user) return;

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['user_avatar']= $user['avatar'];

        // ── THÊM ĐOẠN NÀY ── lấy workspace đầu tiên của user
        $wsModel   = new WorkspaceModel();
        $workspaces = $wsModel->findByOwner($userId);
        if (!empty($workspaces)) {
            $_SESSION['workspace_id'] = $workspaces[0]['id'];
        } else {
            // Kiểm tra workspace được member (không phải owner)
            $db  = Database::getInstance();
            $stmt = $db->prepare(
                "SELECT workspace_id FROM workspace_members
                WHERE user_id = ? LIMIT 1"
            );
            $stmt->execute([$userId]);
            $row = $stmt->fetch();
            $_SESSION['workspace_id'] = $row['workspace_id'] ?? 1;
        }

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + 2592000, '/', '', false, true);
        }
    }

    // Gửi email reset mật khẩu (stub — cần cấu hình SMTP)
    private function sendResetEmail(string $email, string $token): void {
        $link    = APP_URL . '/reset-password?token=' . urlencode($token);
        $subject = '[BugTracker Pro] Đặt lại mật khẩu';
        $body    = "Bạn nhận được email này vì đã yêu cầu đặt lại mật khẩu.\n\n"
                 . "Click link sau (có hiệu lực trong 2 giờ):\n{$link}\n\n"
                 . "Nếu không phải bạn yêu cầu, hãy bỏ qua email này.";

        // Dùng PHP mail() — InfinityFree hỗ trợ
        @mail($email, $subject, $body, "From: noreply@" . parse_url(APP_URL, PHP_URL_HOST));
    }

    // Gửi email mời thành viên workspace
    private function sendInvitationEmail(string $email, string $token): void {
        $link    = APP_URL . '/invite?token=' . urlencode($token);
        $subject = '[BugTracker Pro] Bạn được mời tham gia Workspace';
        $body    = "Bạn được mời tham gia một Workspace trên BugTracker Pro.\n\n"
                 . "Click link sau để chấp nhận (hết hạn sau 7 ngày):\n{$link}";

        @mail($email, $subject, $body, "From: noreply@" . parse_url(APP_URL, PHP_URL_HOST));
    }
}