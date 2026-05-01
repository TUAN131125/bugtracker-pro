<?php
class NotificationController extends BaseController {

    private NotificationModel $notifModel;

    public function __construct() {
        $this->notifModel = new NotificationModel();
    }

    // Trang notifications đầy đủ
    public function index(): void {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        $page       = max(1, (int) $this->get('page', 1));
        $perPage    = 20;
        $offset     = ($page - 1) * $perPage;

        $notifications = $this->notifModel->getPaginated($userId, $perPage, $offset);
        $total         = $this->notifModel->countAll($userId);
        $totalPages    = ceil($total / $perPage);
        $unread        = $this->notifModel->countUnread($userId);

        $this->view('notifications/index', [
            'title'         => 'Thông báo',
            'notifications' => $notifications,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'total'         => $total,
            'unread'        => $unread,
        ]);
    }

    // Đánh dấu tất cả đã đọc
    public function readAll(): void {
        $this->requireAuth();
        $this->notifModel->markAllRead($_SESSION['user_id']);
        flashMessage('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
        $this->redirect('/notifications');
    }

    // Đánh dấu 1 thông báo đã đọc + redirect
    public function read(int $id): void {
        $this->requireAuth();
        $notif = $this->notifModel->findById($id);

        if ($notif && $notif['user_id'] == $_SESSION['user_id']) {
            $this->notifModel->markRead($id);
            $link = $notif['link'] ?? '/dashboard';
            $this->redirect($link);
        }

        $this->redirect('/notifications');
    }
}