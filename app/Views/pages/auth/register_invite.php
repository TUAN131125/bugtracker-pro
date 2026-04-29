<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tách các email dựa vào dấu phẩy hoặc xuống dòng
    $raw_emails = $_POST['emails'] ?? '';
    $role = $_POST['role'] ?? 'viewer';
    
    // Tách và làm sạch mảng email
    $emails = array_filter(array_map('trim', preg_split('/[\n,]+/', $raw_emails)));
    
    // Lặp qua danh sách để tạo Invitation Token giả lập
    foreach ($emails as $email) {
        $token = bin2hex(random_bytes(16));
        // TODO: PDO Insert vào bảng workspace_invitations ở đây
    }
    
    // Gửi tín hiệu thành công ra view để bắn pháo giấy
    $success = true;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bước 4 - Mời đồng đội</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-3">Mời Đồng Đội Vào Dự Án</h3>
                        <p class="text-muted text-center">Cùng nhau giải quyết bug nhanh hơn!</p>
                        
                        <form method="POST" action="" id="inviteForm">
                            <div class="mb-3">
                                <label class="form-label">Email người được mời</label>
                                <textarea name="emails" id="emailInput" class="form-control" rows="3" placeholder="email1@gmail.com, email2@gmail.com..." required></textarea>
                                <small class="text-muted">Nhập nhiều email cách nhau bằng dấu phẩy.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Vai trò (Role)</label>
                                <select name="role" class="form-select">
                                    <option value="manager">Manager</option>
                                    <option value="developer" selected>Developer</option>
                                    <option value="reporter">Reporter</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                            </div>

                            <div id="previewArea" class="mb-3 d-none">
                                <strong>Danh sách chuẩn bị mời:</strong>
                                <ul id="previewList" class="list-group mt-2"></ul>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="dashboard.php" class="text-decoration-none text-secondary">Bỏ qua, làm sau</a>
                                <button type="submit" class="btn btn-primary">Gửi Lời Mời</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // JS Preview danh sách Email trước khi gửi
        const emailInput = document.getElementById('emailInput');
        const previewArea = document.getElementById('previewArea');
        const previewList = document.getElementById('previewList');

        emailInput.addEventListener('input', function() {
            const emails = this.value.split(/[\n,]+/).map(e => e.trim()).filter(e => e);
            if(emails.length > 0) {
                previewArea.classList.remove('d-none');
                previewList.innerHTML = emails.map(email => `<li class="list-group-item py-1">${email}</li>`).join('');
            } else {
                previewArea.classList.add('d-none');
            }
        });

        // Kích hoạt pháo giấy nếu có biến success từ PHP
        <?php if(isset($success) && $success): ?>
            confetti({
                particleCount: 150,
                spread: 70,
                origin: { y: 0.6 }
            });
            // Bắn pháo xong 2 giây sau tự nhảy sang Dashboard
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>