<?php
class DashboardController extends BaseController {
    public function index(): void {
        $this->requireAuth();
        $this->view('dashboard/index', ['title' => 'Dashboard']);
    }
}