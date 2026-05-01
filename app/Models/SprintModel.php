<?php
class SprintModel extends BaseModel {

    // Lấy tất cả sprint của project
    public function getByProject(int $projectId): array {
        return $this->fetchAll(
            "SELECT s.*,
                    COUNT(b.id)                                          AS total_issues,
                    SUM(b.status IN ('resolved','closed'))               AS done_issues,
                    SUM(b.estimated_hours)                               AS total_hours
             FROM sprints s
             LEFT JOIN bugs b ON b.sprint_id = s.id
             WHERE s.project_id = ?
             GROUP BY s.id
             ORDER BY s.created_at DESC",
            [$projectId]
        );
    }

    // Lấy sprint đang active của project
    public function getActive(int $projectId): array|false {
        return $this->fetchOne(
            "SELECT * FROM sprints
             WHERE project_id = ? AND status = 'active'
             ORDER BY created_at DESC
             LIMIT 1",
            [$projectId]
        );
    }

    // Lấy 1 sprint theo ID
    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT s.*, p.key AS project_key, p.name AS project_name
             FROM sprints s
             JOIN projects p ON p.id = s.project_id
             WHERE s.id = ?
             LIMIT 1",
            [$id]
        );
    }

    // Tạo sprint mới
    public function create(array $data): int {
        return $this->insert(
            "INSERT INTO sprints (project_id, name, goal, start_date, end_date, status)
             VALUES (?, ?, ?, ?, ?, 'planning')",
            [
                $data['project_id'],
                $data['name'],
                $data['goal']       ?? null,
                $data['start_date'] ?? null,
                $data['end_date']   ?? null,
            ]
        );
    }

    // Cập nhật sprint
    public function update(int $id, array $data): bool {
        return $this->execute(
            "UPDATE sprints
             SET name = ?, goal = ?, start_date = ?, end_date = ?, status = ?
             WHERE id = ?",
            [
                $data['name'],
                $data['goal']       ?? null,
                $data['start_date'] ?? null,
                $data['end_date']   ?? null,
                $data['status']     ?? 'planning',
                $id,
            ]
        ) > 0;
    }

    // Xóa sprint (chỉ khi planning)
    public function delete(int $id): bool {
        return $this->execute(
            "DELETE FROM sprints WHERE id = ? AND status = 'planning'",
            [$id]
        ) > 0;
    }

    // Bắt đầu sprint (planning → active)
    public function start(int $id): bool {
        return $this->execute(
            "UPDATE sprints SET status = 'active' WHERE id = ? AND status = 'planning'",
            [$id]
        ) > 0;
    }

    // Hoàn thành sprint (active → completed)
    public function complete(int $id): bool {
        return $this->execute(
            "UPDATE sprints SET status = 'completed' WHERE id = ? AND status = 'active'",
            [$id]
        ) > 0;
    }

    // Lấy issues trong sprint
    public function getIssues(int $sprintId): array {
        return $this->fetchAll(
            "SELECT b.*,
                    u.full_name AS assignee_name,
                    u.avatar    AS assignee_avatar
             FROM bugs b
             LEFT JOIN users u ON u.id = b.assignee_id
             WHERE b.sprint_id = ?
             ORDER BY FIELD(b.priority,'critical','high','medium','low','trivial'), b.id",
            [$sprintId]
        );
    }

    // Lấy backlog của project (issues chưa vào sprint nào)
    public function getBacklog(int $projectId): array {
        return $this->fetchAll(
            "SELECT b.*,
                    u.full_name AS assignee_name,
                    u.avatar    AS assignee_avatar
             FROM bugs b
             LEFT JOIN users u ON u.id = b.assignee_id
             WHERE b.project_id = ?
               AND b.sprint_id IS NULL
               AND b.status NOT IN ('resolved','closed')
             ORDER BY FIELD(b.priority,'critical','high','medium','low','trivial'), b.id",
            [$projectId]
        );
    }

    // Gán issue vào sprint
    public function assignIssue(int $bugId, ?int $sprintId): bool {
        return $this->execute(
            "UPDATE bugs SET sprint_id = ? WHERE id = ?",
            [$sprintId, $bugId]
        ) > 0;
    }

    // Burndown chart: số issue còn lại theo từng ngày trong sprint
    public function getBurndownData(int $sprintId): array {
        $sprint = $this->findById($sprintId);
        if (!$sprint || !$sprint['start_date'] || !$sprint['end_date']) {
            return [];
        }

        // Tổng số issue khi sprint bắt đầu
        $totalRow = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM bugs WHERE sprint_id = ?",
            [$sprintId]
        );
        $total = (int)($totalRow['cnt'] ?? 0);

        // Số issue đã done mỗi ngày
        $donePerDay = $this->fetchAll(
            "SELECT DATE(resolved_at) AS day, COUNT(*) AS cnt
             FROM bugs
             WHERE sprint_id = ?
               AND resolved_at IS NOT NULL
               AND status IN ('resolved','closed')
             GROUP BY DATE(resolved_at)
             ORDER BY day ASC",
            [$sprintId]
        );

        // Map ngày → số done tích lũy
        $doneMap = [];
        foreach ($donePerDay as $row) {
            $doneMap[$row['day']] = (int)$row['cnt'];
        }

        // Tạo dãy ngày từ start → end (hoặc hôm nay nếu chưa kết thúc)
        $start    = new DateTime($sprint['start_date']);
        $end      = new DateTime(min($sprint['end_date'], date('Y-m-d')));
        $endFull  = new DateTime($sprint['end_date']);
        $interval = new DateInterval('P1D');
        $dateRange= new DatePeriod($start, $interval, $end->modify('+1 day'));

        $totalDays   = (int)$start->diff($endFull)->days + 1;
        $idealPerDay = $totalDays > 1 ? ($total / ($totalDays - 1)) : 0;

        $result    = [];
        $cumDone   = 0;
        $dayIndex  = 0;

        foreach ($dateRange as $dt) {
            $dayStr   = $dt->format('Y-m-d');
            $cumDone += $doneMap[$dayStr] ?? 0;
            $result[] = [
                'date'      => $dayStr,
                'remaining' => max(0, $total - $cumDone),
                'ideal'     => max(0, round($total - $idealPerDay * $dayIndex, 1)),
            ];
            $dayIndex++;
        }

        return $result;
    }
}
