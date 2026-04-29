<?php
class DashboardController extends BaseController {

    private BugModel          $bugModel;
    private ProjectModel      $projectModel;
    private ActivityLogModel  $activityModel;
    private NotificationModel $notifModel;

    public function __construct() {
        $this->bugModel      = new BugModel();
        $this->projectModel  = new ProjectModel();
        $this->activityModel = new ActivityLogModel();
        $this->notifModel    = new NotificationModel();
    }

    public function index(): void {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        // ── 4 widget thống kê nhanh ──
        $stats = $this->bugModel->getDashboardStats($userId);

        // ── My bugs (10 bug gần nhất được giao) ──
        $myBugs = $this->bugModel->getAssignedTo($userId, 10);

        // ── Recent activity (20 hoạt động gần nhất) ──
        $recentActivity = $this->activityModel->getRecent($userId, 20);

        // ── Projects đang tham gia ──
        $projects = $this->projectModel->getByUser($userId);

        // ── Upcoming deadlines (7 ngày tới) ──
        $upcomingDeadlines = $this->bugModel->getUpcomingDeadlines($userId, 7);

        // ── Notification count cho badge navbar ──
        $unreadNotifications = $this->notifModel->countUnread($userId);

        $this->view('dashboard/index', [
            'title'               => 'Dashboard — ' . APP_NAME,
            'stats'               => $stats,
            'myBugs'              => $myBugs,
            'recentActivity'      => $recentActivity,
            'projects'            => $projects,
            'upcomingDeadlines'   => $upcomingDeadlines,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }
}