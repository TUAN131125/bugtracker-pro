<?php
// ══════════════════════════════════════
// BugTracker Pro — Global Helper Functions
// ══════════════════════════════════════

// ── OUTPUT ──────────────────────────

// Escape HTML output an toàn — dùng mọi nơi thay vì echo trực tiếp
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Cắt ngắn chuỗi dài, thêm ... ở cuối
function truncate(string $str, int $length = 80): string {
    return mb_strlen($str) > $length
        ? mb_substr($str, 0, $length) . '...'
        : $str;
}

// Chuyển Markdown đơn giản thành HTML (bold, italic, code, link)
function parseMarkdown(string $text): string {
    $text = e($text); // escape trước
    $text = preg_replace('/\*\*(.+?)\*\*/s',  '<strong>$1</strong>', $text); // **bold**
    $text = preg_replace('/\*(.+?)\*/s',       '<em>$1</em>',        $text); // *italic*
    $text = preg_replace('/`(.+?)`/',           '<code>$1</code>',    $text); // `code`
    $text = preg_replace('/\[(.+?)\]\((.+?)\)/','<a href="$2" target="_blank">$1</a>', $text); // [link](url)
    $text = nl2br($text); // xuống dòng
    return $text;
}


// ── DATE & TIME ─────────────────────

// Hiển thị thời gian dạng "3 phút trước", "2 ngày trước"
function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60)     return 'Vừa xong';
    if ($diff < 3600)   return (int)($diff / 60)   . ' phút trước';
    if ($diff < 86400)  return (int)($diff / 3600)  . ' giờ trước';
    if ($diff < 604800) return (int)($diff / 86400) . ' ngày trước';
    if ($diff < 2592000)return (int)($diff / 604800). ' tuần trước';

    return date('d/m/Y', $time);
}

// Format ngày theo cài đặt user (mặc định DD/MM/YYYY)
function formatDate(?string $date, string $format = 'd/m/Y'): string {
    if (!$date) return '—';
    return date($format, strtotime($date));
}

// Kiểm tra issue có bị overdue không
function isOverdue(?string $dueDate, string $status = 'open'): bool {
    if (!$dueDate || in_array($status, ['resolved', 'closed'])) return false;
    return strtotime($dueDate) < time();
}


// ── BADGE HTML ───────────────────────

// Render HTML badge cho priority
function priorityBadge(string $priority): string {
    $map = [
        'critical' => ['#DC3545', 'Nghiêm trọng'],
        'high'     => ['#FD7E14', 'Cao'],
        'medium'   => ['#FFC107', 'Trung bình'],
        'low'      => ['#28A745', 'Thấp'],
        'trivial'  => ['#6C757D', 'Không đáng kể'],
    ];
    [$color, $label] = $map[$priority] ?? ['#6C757D', $priority];
    return "<span class='badge' style='background:{$color};'>{$label}</span>";
}

// Render HTML badge cho status
function statusBadge(string $status): string {
    $map = [
        'open'        => ['#0078D4', 'Mở'],
        'in_progress' => ['#FD7E14', 'Đang xử lý'],
        'review'      => ['#6A1B9A', 'Review'],
        'resolved'    => ['#28A745', 'Đã giải quyết'],
        'closed'      => ['#6C757D', 'Đóng'],
    ];
    [$color, $label] = $map[$status] ?? ['#6C757D', $status];
    return "<span class='badge' style='background:{$color};'>{$label}</span>";
}

// Render HTML badge cho type
function typeBadge(string $type): string {
    $map = [
        'bug'         => ['#DC3545', 'fa-bug',          'Bug'],
        'feature'     => ['#0078D4', 'fa-star',         'Feature'],
        'task'        => ['#17A2B8', 'fa-check-square', 'Task'],
        'improvement' => ['#FD7E14', 'fa-arrow-up',     'Cải tiến'],
        'question'    => ['#6A1B9A', 'fa-question',     'Câu hỏi'],
        'epic'        => ['#28A745', 'fa-layer-group',  'Epic'],
    ];
    [$color, $icon, $label] = $map[$type] ?? ['#6C757D', 'fa-circle', $type];
    return "<span style='color:{$color};font-size:12px;'><i class='fa {$icon} me-1'></i>{$label}</span>";
}


// ── VALIDATION ───────────────────────

function validateEmail(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword(string $password): array {
    $errors = [];
    if (strlen($password) < 8)           $errors[] = 'Mật khẩu tối thiểu 8 ký tự';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Cần ít nhất 1 chữ hoa';
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'Cần ít nhất 1 chữ số';
    return $errors;
}

function validateUsername(string $username): array {
    $errors = [];
    if (strlen($username) < 4 || strlen($username) > 50) $errors[] = 'Username từ 4–50 ký tự';
    if (!preg_match('/^[a-z0-9_]+$/', $username))        $errors[] = 'Chỉ dùng a-z, 0-9, dấu _';
    return $errors;
}


// ── SESSION / FLASH ──────────────────

// Lưu flash message để hiển thị 1 lần ở lần load tiếp theo
function flashMessage(string $type, string $message): void {
    $_SESSION['flash'] = compact('type', 'message');
}

// Lấy URL hiện tại (dùng cho redirect-back)
function currentUrl(): string {
    return APP_URL . '/' . ltrim($_GET['url'] ?? '', '/');
}


// ── FILE UPLOAD ──────────────────────

// Kiểm tra file upload hợp lệ
function validateUpload(array $file, int $maxSize = null, array $allowedMimes = null): array {
    $errors   = [];
    $maxSize  = $maxSize ?? UPLOAD_MAX_SIZE;
    $mimes    = $allowedMimes ?? ALLOWED_MIME_TYPES;

    if ($file['error'] !== UPLOAD_ERR_OK)     $errors[] = 'Lỗi khi upload file';
    if ($file['size'] > $maxSize)             $errors[] = 'File vượt quá ' . ($maxSize / 1048576) . 'MB';
    if (!in_array($file['type'], $mimes))     $errors[] = 'Loại file không được phép';

    return $errors;
}

// Tạo tên file ngẫu nhiên an toàn
function generateFilename(string $originalName): string {
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    return bin2hex(random_bytes(16)) . '.' . $ext;
}


// ── SLUG ─────────────────────────────

// Chuyển tên thành slug URL (Tiếng Việt → ASCII)
function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');

    // Bảng chuyển tiếng Việt
    $map = [
        'à','á','ả','ã','ạ','ă','ắ','ặ','ằ','ẳ','ẵ','â','ấ','ầ','ẩ','ẫ','ậ',
        'đ',
        'è','é','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ',
        'ì','í','ỉ','ĩ','ị',
        'ò','ó','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ',
        'ù','ú','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự',
        'ỳ','ý','ỷ','ỹ','ỵ',
    ];
    $rep = [
        'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
        'd',
        'e','e','e','e','e','e','e','e','e','e','e',
        'i','i','i','i','i',
        'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
        'u','u','u','u','u','u','u','u','u','u','u',
        'y','y','y','y','y',
    ];
    $text = str_replace($map, $rep, $text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return $text;

    // ── SECURITY HELPERS ──

    // Output an toàn cho attribute HTML
    function eAttr(string $str): string {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Output an toàn cho JavaScript string
    function eJs(string $str): string {
        return json_encode($str, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    }

    // Validate và sanitize URL redirect (chống Open Redirect)
    function safeRedirectUrl(string $url): string {
        // Chỉ cho phép relative URLs
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }
        return '/dashboard'; // fallback an toàn
    }

    // Kiểm tra file upload an toàn hơn
    function validateUploadStrict(array $file): array {
        $errors = [];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = match($file['error']) {
                UPLOAD_ERR_INI_SIZE   => 'File vượt quá kích thước cho phép của server',
                UPLOAD_ERR_FORM_SIZE  => 'File vượt quá kích thước cho phép của form',
                UPLOAD_ERR_PARTIAL    => 'File chỉ được upload một phần',
                UPLOAD_ERR_NO_FILE    => 'Không có file nào được upload',
                default               => 'Lỗi upload không xác định',
            };
            return $errors;
        }

        // Kiểm tra kích thước
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            $errors[] = 'File vượt quá ' . (UPLOAD_MAX_SIZE / 1048576) . 'MB';
        }

        // Kiểm tra MIME type thực tế bằng finfo (không tin vào $_FILES['type'])
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);

        if (!in_array($realMime, ALLOWED_MIME_TYPES)) {
            $errors[] = "Loại file không được phép: {$realMime}";
        }

        // Kiểm tra extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'log'];
        if (!in_array($ext, $allowedExts)) {
            $errors[] = "Đuôi file .{$ext} không được phép";
        }

        // Kiểm tra file không chứa PHP code (bảo mật thêm)
        $content = file_get_contents($file['tmp_name'], false, null, 0, 512);
        if (str_contains($content, '<?php') || str_contains($content, '<?=')) {
            $errors[] = 'File chứa nội dung không hợp lệ';
        }

        return $errors;
    }

    // Rate limiting đơn giản dùng DB
    function checkRateLimit(string $key, int $maxAttempts, int $decaySeconds): bool {
        // Lưu vào session thay vì DB (đơn giản hơn cho InfinityFree)
        $sessionKey = 'rate_' . md5($key);
        $now        = time();

        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = ['count' => 0, 'reset_at' => $now + $decaySeconds];
        }

        // Reset nếu đã qua thời gian
        if ($_SESSION[$sessionKey]['reset_at'] <= $now) {
            $_SESSION[$sessionKey] = ['count' => 0, 'reset_at' => $now + $decaySeconds];
        }

        $_SESSION[$sessionKey]['count']++;

        return $_SESSION[$sessionKey]['count'] <= $maxAttempts;
    }
}