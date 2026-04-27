<?php
class HomeController {
    public function index(): void {
        // Tạm thời echo ra để test, Dev C sẽ thay bằng Landing Page thật
        echo "<h1>BugTracker Pro</h1><p>Router hoạt động! ✅</p>";
        echo "<p>DB: ";
        try {
            $db = Database::getInstance();
            echo "Kết nối thành công ✅";
        } catch (Exception $e) {
            echo "Lỗi kết nối ❌ — " . $e->getMessage();
        }
        echo "</p>";
    }
}