<?php
/** @var array       $projects     */
/** @var array|null  $project      */
/** @var string      $selectedKey  */
/** @var array       $stats        */
/** @var array       $trend        */
/** @var array       $byStatus     */
/** @var array       $byPriority   */
/** @var array       $byType       */
/** @var array       $topReporters */
/** @var array       $topResolvers */
$projects     = $projects     ?? [];
$stats        = $stats        ?? [];
$trend        = $trend        ?? [];
$byStatus     = $byStatus     ?? [];
$byPriority   = $byPriority   ?? [];
$byType       = $byType       ?? [];
$topReporters = $topReporters ?? [];
$topResolvers = $topResolvers ?? [];
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0">
        <i class="fa fa-chart-bar me-2 text-primary"></i>Báo Cáo & Thống Kê
    </h4>

    <!-- Chọn project -->
    <form method="GET" class="d-flex gap-2 align-items-center">
        <label class="text-muted" style="font-size:13px;">Dự án:</label>
        <select name="project"
                class="form-select form-select-sm"
                style="width:200px;"
                onchange="this.form.submit()">
            <?php foreach ($projects as $proj): ?>
            <option value="<?= e(strtolower($proj['key'])) ?>"
                    <?= $selectedKey === strtolower($proj['key']) ? 'selected' : '' ?>>
                <?= e($proj['name']) ?> (<?= e($proj['key']) ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if (!$project): ?>
<div class="card text-center py-5">
    <i class="fa fa-chart-bar fa-3x text-muted mb-3 d-block"></i>
    <h5 class="text-muted">Chưa có dự án nào để xem báo cáo</h5>
    <a href="<?= APP_URL ?>/projects/new" class="btn btn-primary mt-3">
        Tạo dự án mới
    </a>
</div>
<?php else: ?>

<!-- Tổng quan stats -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['Tổng Issues',    $stats['total']       ?? 0, '#6C757D', 'fa-list'],
        ['Đang Mở',        $stats['open']        ?? 0, '#0078D4', 'fa-circle-dot'],
        ['Đang Xử Lý',     $stats['in_progress'] ?? 0, '#FD7E14', 'fa-spinner'],
        ['Resolved',       $stats['resolved']    ?? 0, '#28A745', 'fa-check-circle'],
        ['Critical',       $stats['critical']    ?? 0, '#DC3545', 'fa-fire'],
    ];
    foreach ($statCards as [$label, $count, $color, $icon]):
    ?>
    <div class="col-sm-6 col-lg">
        <div class="card text-center p-3" style="border-top:3px solid <?= $color ?>;">
            <i class="fa <?= $icon ?> mb-1" style="color:<?= $color ?>;font-size:1.2rem;"></i>
            <div style="font-size:1.8rem;font-weight:800;color:<?= $color ?>;"><?= $count ?></div>
            <small class="text-muted"><?= $label ?></small>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Charts row 1 -->
<div class="row g-4 mb-4">

    <!-- Bug Trend -->
    <div class="col-lg-8">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-3">
                <i class="fa fa-chart-line me-2 text-primary"></i>
                Xu Hướng Bug (30 ngày qua)
            </h6>
            <canvas id="trendChart" height="100"></canvas>
        </div>
    </div>

    <!-- By Status -->
    <div class="col-lg-4">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-3">
                <i class="fa fa-chart-pie me-2 text-primary"></i>
                Phân Bố Theo Status
            </h6>
            <canvas id="statusChart" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Charts row 2 -->
<div class="row g-4 mb-4">

    <!-- By Priority -->
    <div class="col-lg-6">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-3">
                <i class="fa fa-flag me-2 text-warning"></i>
                Phân Bố Theo Priority
            </h6>
            <canvas id="priorityChart" height="150"></canvas>
        </div>
    </div>

    <!-- By Type -->
    <div class="col-lg-6">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-3">
                <i class="fa fa-tag me-2 text-info"></i>
                Phân Bố Theo Loại Issue
            </h6>
            <canvas id="typeChart" height="150"></canvas>
        </div>
    </div>
</div>

<!-- Top reporters / resolvers -->
<div class="row g-4">

    <!-- Top Reporters -->
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="fw-bold mb-3">
                <i class="fa fa-pen-to-square me-2 text-primary"></i>
                Top Báo Cáo Bug
            </h6>
            <?php if (empty($topReporters)): ?>
            <p class="text-muted text-center py-2" style="font-size:13px;">Chưa có dữ liệu</p>
            <?php else: ?>
            <?php foreach ($topReporters as $i => $reporter): ?>
            <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                <span class="fw-bold text-muted" style="width:20px;font-size:13px;">
                    #<?= $i + 1 ?>
                </span>
                <?php if (!empty($reporter['avatar'])): ?>
                <img src="<?= APP_URL ?>/uploads/<?= e($reporter['avatar']) ?>"
                     style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="">
                <?php else: ?>
                <div style="width:32px;height:32px;border-radius:50%;background:#E3F2FD;
                            display:flex;align-items:center;justify-content:center;
                            color:#0078D4;font-weight:700;font-size:13px;">
                    <?= mb_strtoupper(mb_substr($reporter['full_name'], 0, 1)) ?>
                </div>
                <?php endif; ?>
                <span style="font-size:13px;flex:1;"><?= e($reporter['full_name']) ?></span>
                <span class="badge bg-primary rounded-pill"><?= $reporter['count'] ?> bugs</span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Resolvers -->
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="fw-bold mb-3">
                <i class="fa fa-trophy me-2 text-warning"></i>
                Top Giải Quyết Bug
            </h6>
            <?php if (empty($topResolvers)): ?>
            <p class="text-muted text-center py-2" style="font-size:13px;">Chưa có dữ liệu</p>
            <?php else: ?>
            <?php foreach ($topResolvers as $i => $resolver): ?>
            <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                <span class="fw-bold" style="width:20px;font-size:13px;
                      color:<?= ['#FFD700','#C0C0C0','#CD7F32'][$i] ?? '#6C757D' ?>;">
                    #<?= $i + 1 ?>
                </span>
                <?php if (!empty($resolver['avatar'])): ?>
                <img src="<?= APP_URL ?>/uploads/<?= e($resolver['avatar']) ?>"
                     style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="">
                <?php else: ?>
                <div style="width:32px;height:32px;border-radius:50%;background:#E8F5E9;
                            display:flex;align-items:center;justify-content:center;
                            color:#28A745;font-weight:700;font-size:13px;">
                    <?= mb_strtoupper(mb_substr($resolver['full_name'], 0, 1)) ?>
                </div>
                <?php endif; ?>
                <span style="font-size:13px;flex:1;"><?= e($resolver['full_name']) ?></span>
                <span class="badge bg-success rounded-pill"><?= $resolver['count'] ?> resolved</span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- ════ CHART.JS SCRIPTS ════ -->
<script>
// Dữ liệu từ PHP → JS
const trendData   = <?= json_encode($trend,      JSON_UNESCAPED_UNICODE) ?>;
const statusData  = <?= json_encode($byStatus,   JSON_UNESCAPED_UNICODE) ?>;
const priorityData= <?= json_encode($byPriority, JSON_UNESCAPED_UNICODE) ?>;
const typeData    = <?= json_encode($byType,     JSON_UNESCAPED_UNICODE) ?>;

// Colors
const statusColors = {
    open:        '#0078D4',
    in_progress: '#FD7E14',
    review:      '#6A1B9A',
    resolved:    '#28A745',
    closed:      '#6C757D',
};
const priorityColors = {
    critical: '#DC3545',
    high:     '#FD7E14',
    medium:   '#FFC107',
    low:      '#28A745',
    trivial:  '#6C757D',
};
const typeColors = {
    bug:         '#DC3545',
    feature:     '#0078D4',
    task:        '#17A2B8',
    improvement: '#FD7E14',
    question:    '#6A1B9A',
    epic:        '#28A745',
};

// ── TREND CHART ──
const trendCtx = document.getElementById('trendChart');
if (trendCtx && trendData.length > 0) {
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.map(d => d.date),
            datasets: [
                {
                    label: 'Tạo mới',
                    data: trendData.map(d => d.created),
                    borderColor: '#DC3545',
                    backgroundColor: 'rgba(220,53,69,.1)',
                    fill: true,
                    tension: .4,
                    pointRadius: 3,
                },
                {
                    label: 'Resolved',
                    data: trendData.map(d => d.resolved),
                    borderColor: '#28A745',
                    backgroundColor: 'rgba(40,167,69,.1)',
                    fill: true,
                    tension: .4,
                    pointRadius: 3,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
} else if (trendCtx) {
    trendCtx.parentElement.innerHTML +=
        '<p class="text-center text-muted py-3" style="font-size:13px;">Chưa có dữ liệu</p>';
    trendCtx.remove();
}

// ── STATUS CHART ──
const statusCtx = document.getElementById('statusChart');
if (statusCtx && statusData.length > 0) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(d => d.status),
            datasets: [{
                data: statusData.map(d => d.count),
                backgroundColor: statusData.map(d => statusColors[d.status] || '#6C757D'),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 12 } } } },
            cutout: '60%',
        }
    });
}

// ── PRIORITY CHART ──
const priorityCtx = document.getElementById('priorityChart');
if (priorityCtx && priorityData.length > 0) {
    new Chart(priorityCtx, {
        type: 'bar',
        data: {
            labels: priorityData.map(d => d.priority),
            datasets: [{
                label: 'Số lượng',
                data: priorityData.map(d => d.count),
                backgroundColor: priorityData.map(d => priorityColors[d.priority] || '#6C757D'),
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

// ── TYPE CHART ──
const typeCtx = document.getElementById('typeChart');
if (typeCtx && typeData.length > 0) {
    new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: typeData.map(d => d.type),
            datasets: [{
                data: typeData.map(d => d.count),
                backgroundColor: typeData.map(d => typeColors[d.type] || '#6C757D'),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 12 } } } }
        }
    });
}
</script>