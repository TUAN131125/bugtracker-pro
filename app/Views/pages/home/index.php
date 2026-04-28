<?php
// Landing page dùng layout riêng (không có sidebar)
// HomeController::index() sẽ gọi viewLanding() — Dev B cần thêm method này vào BaseController
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BugTracker Pro — Hệ thống quản lý bug miễn phí, chuyên nghiệp cho team của bạn.">
    <title>BugTracker Pro — Theo Dõi Bug Chuyên Nghiệp & Miễn Phí</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/public/css/landing.css" rel="stylesheet">
</head>
<body>

<!-- ════════════════════════════════════
     NAVBAR
════════════════════════════════════ -->
<nav class="lp-navbar">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">

            <!-- Logo -->
            <a href="<?= APP_URL ?>/" class="brand">
                <i class="fa-solid fa-bug me-2"></i>BugTracker Pro
            </a>

            <!-- Menu desktop -->
            <div class="d-none d-lg-flex align-items-center gap-1">
                <a href="#features"   class="nav-link">Tính năng</a>
                <a href="#howitworks" class="nav-link">Cách hoạt động</a>
                <a href="#pricing"    class="nav-link">Bảng giá</a>
                <a href="#testimonials" class="nav-link">Đánh giá</a>
            </div>

            <!-- CTA buttons -->
            <div class="d-flex align-items-center gap-2">
                <a href="<?= APP_URL ?>/login"
                   class="btn btn-sm btn-outline-light d-none d-md-inline-flex">
                    Đăng nhập
                </a>
                <a href="<?= APP_URL ?>/register"
                   class="btn btn-sm btn-primary fw-bold">
                    <i class="fa fa-rocket me-1"></i>Bắt đầu miễn phí
                </a>
            </div>
        </div>
    </div>
</nav>


<!-- ════════════════════════════════════
     HERO
════════════════════════════════════ -->
<section class="hero">
    <div class="container position-relative">
        <div class="row align-items-center g-5">

            <!-- Left: text -->
            <div class="col-lg-6">
                <div class="hero-badge">
                    <i class="fa fa-star me-1"></i> Miễn phí mãi mãi · Không cần thẻ tín dụng
                </div>

                <h1>
                    Theo Dõi Bug,<br>
                    Quản Lý Issue<br>
                    <span>Chuyên Nghiệp</span>
                </h1>

                <p class="lead">
                    Giải pháp tracking bug mạnh mẽ cho team của bạn.
                    Lấy cảm hứng từ Jira nhưng nhẹ hơn, miễn phí hơn —
                    triển khai trong 5 phút.
                </p>

                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="<?= APP_URL ?>/register" class="btn-hero-primary">
                        <i class="fa fa-rocket me-2"></i>Dùng ngay miễn phí
                    </a>
                    <a href="#features" class="btn-hero-outline">
                        <i class="fa fa-play me-2"></i>Xem tính năng
                    </a>
                </div>

                <!-- Social proof stats -->
                <div class="hero-stats">
                    <div class="stat">
                        <div class="stat-num">500+</div>
                        <div class="stat-label">Người dùng</div>
                    </div>
                    <div class="stat">
                        <div class="stat-num">1,200+</div>
                        <div class="stat-label">Dự án</div>
                    </div>
                    <div class="stat">
                        <div class="stat-num">48,000+</div>
                        <div class="stat-label">Bugs đã giải quyết</div>
                    </div>
                </div>
            </div>

            <!-- Right: app mockup -->
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-mockup">
                    <!-- Giả lập thanh tiêu đề app -->
                    <div class="mockup-bar">
                        <div class="mockup-dot" style="background:#FF5F57;"></div>
                        <div class="mockup-dot" style="background:#FFBD2E;"></div>
                        <div class="mockup-dot" style="background:#28CA41;"></div>
                        <span style="margin-left:8px;font-size:12px;color:rgba(255,255,255,.4);">
                            BugTracker Pro — Dashboard
                        </span>
                    </div>

                    <!-- Giả lập các issue rows -->
                    <?php
                    $mockIssues = [
                        ['BUG-042', 'Login form không validate email', 'critical', 'Cao',     '#DC3545', 'open',       '#0078D4'],
                        ['BUG-041', 'Kanban card không kéo thả được', 'bug',      'Cao',     '#DC3545', 'in_progress','#FD7E14'],
                        ['BUG-040', 'Export CSV bị lỗi encoding UTF-8','bug',     'TB',      '#FFC107', 'review',     '#6A1B9A'],
                        ['FEA-015', 'Thêm tính năng dark mode',        'feature', 'Thấp',   '#28A745', 'open',       '#0078D4'],
                        ['BUG-039', 'Avatar upload > 2MB bị timeout',  'bug',     'TB',      '#FFC107', 'resolved',   '#28A745'],
                    ];
                    foreach ($mockIssues as [$key, $title, $type, $pri, $priColor, $status, $statusColor]):
                    ?>
                    <div class="mockup-row">
                        <span style="color:rgba(255,255,255,.45);font-size:11px;min-width:52px;"><?= $key ?></span>
                        <span style="flex:1;font-size:12px;"><?= $title ?></span>
                        <span class="mockup-badge" style="background:<?= $priColor ?>22;color:<?= $priColor ?>;"><?= $pri ?></span>
                        <span class="mockup-badge" style="background:<?= $statusColor ?>22;color:<?= $statusColor ?>;"><?= $status ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</section>


<!-- ════════════════════════════════════
     FEATURES
════════════════════════════════════ -->
<section class="features" id="features">
    <div class="container">
        <div class="text-center">
            <span class="section-label">Tính năng</span>
            <h2 class="section-title">Mọi thứ bạn cần để quản lý bug</h2>
            <p class="section-sub">
                Được thiết kế cho team nhỏ và dự án cá nhân —
                đầy đủ tính năng mà không cần trả phí.
            </p>
        </div>

        <?php
        $features = [
            ['fa-bolt',        '#0078D4', '#E3F2FD', 'Theo Dõi Realtime',        'Mọi thay đổi trên issue được ghi nhận tức thì vào Activity Log. Không bao giờ bỏ lỡ cập nhật quan trọng.'],
            ['fa-table-columns','#6A1B9A','#F3E5F5', 'Kanban Board Kéo Thả',     'Trực quan hoá workflow với bảng Kanban 5 cột. Kéo thả card để cập nhật status không cần reload trang.'],
            ['fa-users-gear',  '#2E7D32', '#E8F5E9', 'Phân Quyền Linh Hoạt',     '5 cấp vai trò: Admin, Manager, Developer, Reporter, Viewer. Kiểm soát ai được xem và làm gì trong dự án.'],
            ['fa-chart-line',  '#E65100', '#FFF3E0', 'Báo Cáo Chi Tiết',         'Biểu đồ bug trend, phân tích theo priority/status/type. Export CSV để chia sẻ với stakeholder.'],
            ['fa-bell',        '#C62828', '#FFEBEE', 'Thông Báo Tức Thì',        'Nhận notification khi được giao bug, có comment mới hoặc issue sắp đến hạn. Tuỳ chỉnh loại thông báo.'],
            ['fa-flag-checkered','#00796B','#E0F2F1','Sprint & Milestone',        'Lập kế hoạch sprint, theo dõi burndown chart, quản lý milestone. Đủ dùng cho Agile team nhỏ.'],
        ];
        ?>
        <div class="row g-4 mt-2">
            <?php foreach ($features as [$icon, $color, $bg, $title, $desc]): ?>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:<?= $bg ?>;">
                        <i class="fa <?= $icon ?>" style="color:<?= $color ?>;"></i>
                    </div>
                    <h5><?= $title ?></h5>
                    <p><?= $desc ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════
     HOW IT WORKS
════════════════════════════════════ -->
<section class="how-it-works" id="howitworks">
    <div class="container">
        <div class="text-center">
            <span class="section-label">Cách hoạt động</span>
            <h2 class="section-title">Bắt đầu trong 3 bước đơn giản</h2>
            <p class="section-sub">Không cần cài đặt phức tạp, không cần thẻ tín dụng.</p>
        </div>

        <div class="row align-items-center mt-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-num">1</div>
                    <h5>Tạo tài khoản & Workspace</h5>
                    <p>Đăng ký miễn phí trong 30 giây. Tạo workspace cho công ty hoặc team của bạn ngay sau đó.</p>
                </div>
            </div>
            <div class="col-md-1 d-none d-md-flex step-arrow">
                <i class="fa fa-arrow-right text-muted"></i>
            </div>
            <div class="col-md-3">
                <div class="step-card">
                    <div class="step-num">2</div>
                    <h5>Tạo dự án & Mời team</h5>
                    <p>Tạo dự án, gán role cho từng thành viên. Mời qua email — họ tham gia trong 1 click.</p>
                </div>
            </div>
            <div class="col-md-1 d-none d-md-flex step-arrow">
                <i class="fa fa-arrow-right text-muted"></i>
            </div>
            <div class="col-md-3">
                <div class="step-card">
                    <div class="step-num">3</div>
                    <h5>Bắt đầu track bugs</h5>
                    <p>Tạo issue, giao việc, theo dõi trên Kanban. Mọi thứ tập trung một chỗ.</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════
     PRICING
════════════════════════════════════ -->
<section class="pricing" id="pricing">
    <div class="container">
        <div class="text-center">
            <span class="section-label">Bảng giá</span>
            <h2 class="section-title">Minh bạch, không có chi phí ẩn</h2>
            <p class="section-sub">Gói Free đủ dùng cho team nhỏ và dự án cá nhân mãi mãi.</p>
        </div>

        <?php
        $plans = [
            [
                'name'    => 'Free',
                'price'   => '0đ',
                'period'  => '/ mãi mãi',
                'color'   => '#28A745',
                'featured'=> false,
                'badge'   => null,
                'btnText' => 'Bắt đầu miễn phí',
                'btnClass'=> 'btn-outline-success',
                'items'   => [
                    ['check', '#28A745', 'Tối đa 5 người dùng'],
                    ['check', '#28A745', '3 dự án'],
                    ['check', '#28A745', '500MB storage'],
                    ['check', '#28A745', 'Kanban Board'],
                    ['check', '#28A745', 'Issue tracking đầy đủ'],
                    ['times', '#ccc',    'Sprint management'],
                    ['times', '#ccc',    'API access'],
                ],
            ],
            [
                'name'    => 'Pro',
                'price'   => 'Liên hệ',
                'period'  => '',
                'color'   => '#0078D4',
                'featured'=> true,
                'badge'   => 'Phổ biến nhất',
                'btnText' => 'Liên hệ tư vấn',
                'btnClass'=> 'btn-primary',
                'items'   => [
                    ['check', '#0078D4', 'Tối đa 25 người dùng'],
                    ['check', '#0078D4', 'Không giới hạn dự án'],
                    ['check', '#0078D4', '10GB storage'],
                    ['check', '#0078D4', 'Sprint & Milestone'],
                    ['check', '#0078D4', 'Báo cáo nâng cao'],
                    ['check', '#0078D4', 'API access'],
                    ['check', '#0078D4', 'Webhook integrations'],
                ],
            ],
            [
                'name'    => 'Enterprise',
                'price'   => 'Liên hệ',
                'period'  => '',
                'color'   => '#6A1B9A',
                'featured'=> false,
                'badge'   => null,
                'btnText' => 'Liên hệ tư vấn',
                'btnClass'=> 'btn-outline-secondary',
                'items'   => [
                    ['check', '#6A1B9A', 'Không giới hạn người dùng'],
                    ['check', '#6A1B9A', 'Không giới hạn dự án'],
                    ['check', '#6A1B9A', '100GB storage'],
                    ['check', '#6A1B9A', 'Custom workflow'],
                    ['check', '#6A1B9A', 'SSO / LDAP'],
                    ['check', '#6A1B9A', 'Dedicated support'],
                    ['check', '#6A1B9A', 'SLA 99.9% uptime'],
                ],
            ],
        ];
        ?>

        <div class="row g-4 justify-content-center mt-2">
            <?php foreach ($plans as $plan): ?>
            <div class="col-md-4">
                <div class="pricing-card <?= $plan['featured'] ? 'featured' : '' ?>">
                    <?php if ($plan['badge']): ?>
                    <div class="pricing-badge"><?= $plan['badge'] ?></div>
                    <?php endif; ?>

                    <div class="pricing-name"><?= $plan['name'] ?></div>
                    <div class="pricing-price" style="color:<?= $plan['color'] ?>;">
                        <?= $plan['price'] ?>
                        <span><?= $plan['period'] ?></span>
                    </div>

                    <ul class="pricing-list">
                        <?php foreach ($plan['items'] as [$ico, $clr, $text]): ?>
                        <li>
                            <i class="fa fa-<?= $ico ?>-circle" style="color:<?= $clr ?>;"></i>
                            <?= $text ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <a href="<?= APP_URL ?>/register"
                       class="btn <?= $plan['btnClass'] ?> w-100 fw-bold">
                        <?= $plan['btnText'] ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════
     TESTIMONIALS
════════════════════════════════════ -->
<section class="testimonials" id="testimonials">
    <div class="container">
        <div class="text-center">
            <span class="section-label">Đánh giá</span>
            <h2 class="section-title">Người dùng nói gì về chúng tôi</h2>
        </div>

        <?php
        $testimonials = [
            [
                'content' => 'BugTracker Pro giúp team mình quản lý bug rõ ràng hơn hẳn. Kanban board trực quan, dễ dùng. Quan trọng nhất là miễn phí và không bị giới hạn tính năng cơ bản.',
                'author'  => 'Nguyễn Văn Minh',
                'role'    => 'Tech Lead · Startup FinTech',
                'stars'   => 5,
            ],
            [
                'content' => 'Mình đã thử nhiều tool nhưng cái này là phù hợp nhất cho team 5 người. Setup nhanh, giao diện Tiếng Việt, không mất thời gian training cho team mới.',
                'author'  => 'Trần Thị Lan',
                'role'    => 'Project Manager · Agency',
                'stars'   => 5,
            ],
            [
                'content' => 'Phần phân quyền theo role rất hay — reporter chỉ tạo bug được, developer mới được sửa. Không còn tình trạng ai cũng vào xóa issue của nhau nữa.',
                'author'  => 'Lê Quốc Hùng',
                'role'    => 'Senior Developer · Outsource company',
                'stars'   => 4,
            ],
        ];
        ?>

        <div class="row g-4 mt-2">
            <?php foreach ($testimonials as $t): ?>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="stars">
                        <?= str_repeat('<i class="fa fa-star"></i>', $t['stars']) ?>
                        <?= str_repeat('<i class="fa fa-star text-muted" style="opacity:.3"></i>', 5 - $t['stars']) ?>
                    </div>
                    <p>"<?= $t['content'] ?>"</p>
                    <div class="author"><?= $t['author'] ?></div>
                    <div class="role"><?= $t['role'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════
     CTA BANNER
════════════════════════════════════ -->
<section class="cta-banner">
    <div class="container">
        <h2>Sẵn sàng bắt đầu chưa?</h2>
        <p>Tạo tài khoản miễn phí ngay hôm nay. Không cần thẻ tín dụng.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?= APP_URL ?>/register" class="btn-hero-primary">
                <i class="fa fa-rocket me-2"></i>Tạo tài khoản miễn phí
            </a>
            <a href="<?= APP_URL ?>/login" class="btn-hero-outline">
                Đăng nhập
            </a>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════
     FOOTER
════════════════════════════════════ -->
<footer class="lp-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="mb-3">
                    <i class="fa-solid fa-bug me-2" style="color:#0078D4;font-size:1.2rem;"></i>
                    <span style="color:#fff;font-size:1.1rem;font-weight:700;">BugTracker Pro</span>
                </div>
                <p style="font-size:13px;line-height:1.7;">
                    Hệ thống quản lý bug chuyên nghiệp, miễn phí mãi mãi.
                    Xây dựng bằng PHP 8.3, tối ưu cho InfinityFree hosting.
                </p>
                <div class="mt-3">
                    <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-3 col-6">
                <h6>Sản phẩm</h6>
                <a href="#features">Tính năng</a>
                <a href="#pricing">Bảng giá</a>
                <a href="#howitworks">Cách hoạt động</a>
                <a href="<?= APP_URL ?>/register">Đăng ký</a>
            </div>

            <div class="col-lg-2 col-md-3 col-6">
                <h6>Tài nguyên</h6>
                <a href="#">Tài liệu</a>
                <a href="#">Hướng dẫn</a>
                <a href="#">API Docs</a>
                <a href="#">Changelog</a>
            </div>

            <div class="col-lg-4 col-md-6">
                <h6>Liên hệ</h6>
                <p style="font-size:13px;">
                    <i class="fa fa-envelope me-2" style="color:#0078D4;"></i>support@bugtracker.pro<br>
                    <i class="fa fa-globe me-2 mt-2" style="color:#0078D4;"></i>bugtracker.pro
                </p>
                <div class="mt-2" style="font-size:12px;">
                    <a href="#" class="me-3">Chính sách bảo mật</a>
                    <a href="#">Điều khoản dịch vụ</a>
                </div>
            </div>
        </div>

        <hr class="divider">
        <div class="text-center" style="font-size:12px;">
            &copy; 2025 BugTracker Pro. Xây dựng với ❤️ bằng PHP 8.3 · Miễn phí mãi mãi
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Smooth scroll cho anchor links
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', function(e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// Navbar đổi màu khi scroll xuống
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.lp-navbar');
    navbar.style.background = window.scrollY > 60
        ? 'rgba(15,34,51,1)'
        : 'rgba(15,34,51,.96)';
});
</script>
</body>
</html>