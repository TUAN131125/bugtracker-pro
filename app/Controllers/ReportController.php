<?php
class ReportController extends BaseController {

    public function index(): void {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        // Lấy danh sách projects để chọn
        $projModel = new ProjectModel();
        $projects  = $projModel->getByUser($userId);

        // Project được chọn (mặc định project đầu tiên)
        $selectedKey = $this->get('project', '');
        $project     = null;

        if ($selectedKey) {
            $project = $projModel->findByKey($selectedKey);
        } elseif (!empty($projects)) {
            $project = $projects[0];
            $selectedKey = strtolower($project['key']);
        }

        $stats    = [];
        $trend    = [];
        $byStatus = [];
        $byPriority = [];
        $byType   = [];
        $topReporters  = [];
        $topResolvers  = [];

        if ($project) {
            $bugModel = new BugModel();
            $db       = Database::getInstance();

            // Thống kê tổng quan
            $stats = $projModel->getStats($project['id']) ?? [];

            // Bug trend 30 ngày
            $trend = $bugModel->getTrend($project['id'], 30);

            // Phân bố theo status
            $stmt = $db->prepare(
                "SELECT status, COUNT(*) AS count
                 FROM bugs WHERE project_id = ?
                 GROUP BY status"
            );
            $stmt->execute([$project['id']]);
            $byStatus = $stmt->fetchAll();

            // Phân bố theo priority
            $stmt = $db->prepare(
                "SELECT priority, COUNT(*) AS count
                 FROM bugs WHERE project_id = ?
                 GROUP BY priority"
            );
            $stmt->execute([$project['id']]);
            $byPriority = $stmt->fetchAll();

            // Phân bố theo type
            $stmt = $db->prepare(
                "SELECT type, COUNT(*) AS count
                 FROM bugs WHERE project_id = ?
                 GROUP BY type"
            );
            $stmt->execute([$project['id']]);
            $byType = $stmt->fetchAll();

            // Top reporters
            $stmt = $db->prepare(
                "SELECT u.full_name, u.avatar, COUNT(b.id) AS count
                 FROM bugs b
                 JOIN users u ON u.id = b.reporter_id
                 WHERE b.project_id = ?
                 GROUP BY b.reporter_id
                 ORDER BY count DESC LIMIT 5"
            );
            $stmt->execute([$project['id']]);
            $topReporters = $stmt->fetchAll();

            // Top resolvers
            $stmt = $db->prepare(
                "SELECT u.full_name, u.avatar, COUNT(b.id) AS count
                 FROM bugs b
                 JOIN users u ON u.id = b.assignee_id
                 WHERE b.project_id = ?
                   AND b.status IN ('resolved','closed')
                 GROUP BY b.assignee_id
                 ORDER BY count DESC LIMIT 5"
            );
            $stmt->execute([$project['id']]);
            $topResolvers = $stmt->fetchAll();
        }

        $this->view('reports/index', [
            'title'       => 'Báo cáo & Thống kê',
            'projects'    => $projects,
            'project'     => $project,
            'selectedKey' => $selectedKey,
            'stats'       => $stats,
            'trend'       => $trend,
            'byStatus'    => $byStatus,
            'byPriority'  => $byPriority,
            'byType'      => $byType,
            'topReporters'=> $topReporters,
            'topResolvers'=> $topResolvers,
        ]);
    }
}