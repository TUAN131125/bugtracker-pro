<?php
class ProjectController extends BaseController {
    public function index(): void {
        $this->requireAuth();
        $this->view('projects/index', ['title' => 'Dự án của tôi']);
    }
    public function createForm(): void {
        $this->requireAuth();
        $this->view('projects/create', ['title' => 'Tạo dự án mới', 'csrf_token' => $this->generateCsrfToken()]);
    }
    public function create(): void { $this->redirect('/projects'); }
    public function show(string $key): void {
        $this->requireAuth();
        $this->view('projects/show', ['title' => 'Dự án', 'key' => $key]);
    }
}