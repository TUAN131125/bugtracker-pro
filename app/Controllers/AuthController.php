<?php
class AuthController extends BaseController {

    public function loginForm(): void {
        $this->viewAuth('auth/login', [
            'title'      => 'Đăng nhập',
            'step'       => null, // không hiện progress bar
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    public function login(): void {
        // Dev A sẽ viết logic ngày 2
        $this->redirect('/dashboard');
    }

    public function registerForm(): void {
        $this->viewAuth('auth/register', [
            'title'      => 'Tạo tài khoản',
            'step'       => 1,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    public function register(): void {
        // Dev A sẽ viết logic ngày 2
        $this->redirect('/register/profile');
    }

    public function profileForm(): void {
        $this->view('auth/register_profile', ['title' => 'Thông tin cá nhân', 'step' => 2, 'csrf_token' => $this->generateCsrfToken()], false);
    }
    public function saveProfile(): void { $this->redirect('/register/workspace'); }

    public function workspaceForm(): void {
        $this->view('auth/register_workspace', ['title' => 'Tạo Workspace', 'step' => 3, 'csrf_token' => $this->generateCsrfToken()], false);
    }
    public function saveWorkspace(): void { $this->redirect('/register/invite'); }

    public function inviteForm(): void {
        $this->view('auth/register_invite', ['title' => 'Mời thành viên', 'step' => 4, 'csrf_token' => $this->generateCsrfToken()], false);
    }
    public function sendInvite(): void { $this->redirect('/dashboard'); }

    public function logout(): void {
        session_destroy();
        $this->redirect('/login');
    }

    public function forgotForm(): void {
        $this->view('auth/forgot', ['title' => 'Quên mật khẩu', 'csrf_token' => $this->generateCsrfToken()], false);
    }
    public function sendReset(): void { $this->redirect('/login'); }

    public function resetForm(): void {
        $this->view('auth/reset', ['title' => 'Đặt lại mật khẩu', 'csrf_token' => $this->generateCsrfToken()], false);
    }
    public function doReset(): void { $this->redirect('/login'); }
}