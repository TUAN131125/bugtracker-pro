<?php
class Router {
    private array $routes = [];

    public function __construct() {
        $this->registerRoutes();
    }

    private function registerRoutes(): void {
        // Public routes — không cần đăng nhập
        $this->add('GET',  '/',                  'HomeController',    'index');
        $this->add('GET',  '/login',             'AuthController',    'loginForm');
        $this->add('POST', '/login',             'AuthController',    'login');
        $this->add('GET',  '/register',          'AuthController',    'registerForm');
        $this->add('POST', '/register',          'AuthController',    'register');
        $this->add('GET',  '/register/profile',  'AuthController',    'profileForm');
        $this->add('POST', '/register/profile',  'AuthController',    'saveProfile');
        $this->add('GET',  '/register/workspace','AuthController',    'workspaceForm');
        $this->add('POST', '/register/workspace','AuthController',    'saveWorkspace');
        $this->add('GET',  '/register/invite',   'AuthController',    'inviteForm');
        $this->add('POST', '/register/invite',   'AuthController',    'sendInvite');
        $this->add('GET',  '/logout',            'AuthController',    'logout');
        $this->add('GET',  '/forgot-password',   'AuthController',    'forgotForm');
        $this->add('POST', '/forgot-password',   'AuthController',    'sendReset');
        $this->add('GET',  '/reset-password',    'AuthController',    'resetForm');
        $this->add('POST', '/reset-password',    'AuthController',    'doReset');

        // Protected routes — cần đăng nhập
        $this->add('GET',  '/dashboard',         'DashboardController','index');
        $this->add('GET',  '/projects',          'ProjectController', 'index');
        $this->add('GET',  '/projects/new',      'ProjectController', 'createForm');
        $this->add('POST', '/projects/new',      'ProjectController', 'create');
        $this->add('GET',  '/projects/:key',     'ProjectController', 'show');
        $this->add('GET',  '/issues/:key',       'BugController',     'show');
        $this->add('GET',  '/profile',           'UserController',    'profile');
        $this->add('GET',  '/settings',          'UserController',    'settings');
        $this->add('GET',  '/admin',             'AdminController',   'index');

        // AJAX routes — check realtime
        $this->add('GET',  '/api/check-email',    'AuthController', 'checkEmail');
        $this->add('GET',  '/api/check-username', 'AuthController', 'checkUsername');
        $this->add('POST', '/api/slug-from-name', 'AuthController', 'slugFromName');

        // ── Dashboard ──
        $this->add('GET', '/dashboard', 'DashboardController', 'index');

        // ── Projects ──
        $this->add('GET',  '/projects',                   'ProjectController', 'index');
        $this->add('GET',  '/projects/new',               'ProjectController', 'createForm');
        $this->add('POST', '/projects/new',               'ProjectController', 'create');
        $this->add('GET',  '/projects/:key',              'ProjectController', 'show');
        $this->add('GET',  '/projects/:key/settings',     'ProjectController', 'settings');
        $this->add('POST', '/projects/:key/settings',     'ProjectController', 'saveSettings');
        $this->add('POST', '/projects/:key/delete',       'ProjectController', 'delete');

        // ── Issues ──
        $this->add('GET',  '/projects/:key/issues/new', 'BugController', 'createForm');
        $this->add('POST', '/projects/:key/issues/new', 'BugController', 'create');
        $this->add('GET',  '/issues/:key',              'BugController', 'show');
        $this->add('GET',  '/issues/:key/edit',         'BugController', 'editForm');
        $this->add('POST', '/issues/:key/edit',         'BugController', 'update');
        $this->add('POST', '/issues/:key/delete',       'BugController', 'delete');

        // ── Issue AJAX actions ──
        $this->add('POST', '/issues/:key/comment',      'BugController', 'addComment');
        $this->add('POST', '/issues/:key/attach',       'BugController', 'addAttachment');
        $this->add('POST', '/issues/:key/status',       'BugController', 'changeStatus');
        $this->add('POST', '/issues/:key/vote',         'BugController', 'vote');

        // ── Delete actions ──
        $this->add('POST', '/comments/:id/delete',      'BugController', 'deleteComment');
        $this->add('POST', '/attachments/:id/delete',   'BugController', 'deleteAttachment');

        // ── Kanban Board ──
        $this->add('GET',  '/projects/:key/board',               'KanbanController', 'board');
        $this->add('POST', '/projects/:key/board/move',          'KanbanController', 'moveCard');

        // ── Quick-view popup ──
        $this->add('GET',  '/issues/:key/quickview',             'KanbanController', 'quickView');

        // ── Sprint Board ──
        $this->add('GET',  '/projects/:key/sprint',              'KanbanController', 'sprintBoard');
        $this->add('POST', '/projects/:key/sprint/create',       'KanbanController', 'createSprint');
        $this->add('POST', '/projects/:key/sprint/:id/start',    'KanbanController', 'startSprint');
        $this->add('POST', '/projects/:key/sprint/:id/complete', 'KanbanController', 'completeSprint');
        $this->add('POST', '/projects/:key/sprint/assign-issue', 'KanbanController', 'assignIssue');

        // Notifications
        $this->add('GET',  '/notifications',            'NotificationController', 'index');
        $this->add('GET',  '/notifications/read-all',   'NotificationController', 'readAll');
        $this->add('GET',  '/notifications/read/:id',   'NotificationController', 'read');

        // Search
        $this->add('GET',  '/api/search',               'SearchController',       'search');

        //Report
        $this->add('GET', '/reports', 'ReportController', 'index');

        // Labels
        $this->add('GET',  '/projects/:key/labels',        'LabelController', 'index');
        $this->add('POST', '/projects/:key/labels',        'LabelController', 'create');
        $this->add('POST', '/projects/:key/labels/:id/delete', 'LabelController', 'delete');

        // ── Profile & Settings ──
        $this->add('GET',  '/profile',                           'ProfileController', 'index');
        $this->add('POST', '/profile/update',                    'ProfileController', 'update');
        $this->add('POST', '/profile/password',                  'ProfileController', 'changePassword');
        $this->add('POST', '/profile/theme',                     'ProfileController', 'toggleTheme');
        $this->add('GET',  '/settings',                          'ProfileController', 'settings');
        $this->add('POST', '/settings/save',                     'ProfileController', 'saveSettings');

        // ── Admin Panel ──
        $this->add('GET',  '/admin',                             'AdminController', 'index');
        $this->add('GET',  '/admin/users',                       'AdminController', 'users');
        $this->add('POST', '/admin/users/create',                'AdminController', 'createUser');
        $this->add('POST', '/admin/users/:id/toggle',            'AdminController', 'toggleUser');
        $this->add('POST', '/admin/users/:id/role',              'AdminController', 'changeRole');
        $this->add('GET',  '/admin/settings',                    'AdminController', 'siteSettings');
        $this->add('POST', '/admin/settings/smtp',               'AdminController', 'saveSmtp');
        $this->add('POST', '/admin/settings/test-smtp',          'AdminController', 'testSmtp');

        // Project Settings
        $this->add('GET',  '/projects/:key/settings',              'ProjectController', 'settings');
        $this->add('POST', '/projects/:key/settings',              'ProjectController', 'saveSettings');
        $this->add('POST', '/projects/:key/delete',                'ProjectController', 'delete');

        // Member management
        $this->add('POST', '/projects/:key/members/invite',        'ProjectController', 'inviteMember');
        $this->add('POST', '/projects/:key/members/:id/role',      'ProjectController', 'updateMemberRole');
        $this->add('POST', '/projects/:key/members/:id/remove',    'ProjectController', 'removeMember');

        // User Profile & Settings
        $this->add('GET',  '/profile',          'UserController', 'profile');
        $this->add('POST', '/profile/update',   'UserController', 'updateProfile');
        $this->add('GET',  '/settings',         'UserController', 'settings');
        $this->add('POST', '/settings/password','UserController', 'updatePassword');
    }

    private function add(string $method, string $path, string $controller, string $action): void {
        $this->routes[] = compact('method', 'path', 'controller', 'action');
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $url    = '/' . trim($_GET['url'] ?? '', '/');

        foreach ($this->routes as $route) {
            $pattern = $this->pathToRegex($route['path']);
            if ($route['method'] === $method && preg_match($pattern, $url, $matches)) {
                // Lấy các tham số động (:key, :id...)
                array_shift($matches);
                $params = array_values($matches);

                // Load controller
                $controllerFile = APP_PATH . '/Controllers/' . $route['controller'] . '.php';
                if (!file_exists($controllerFile)) {
                    $this->error404("Controller {$route['controller']} not found");
                    return;
                }
                require_once $controllerFile;

                $ctrl = new $route['controller']();
                $action = $route['action'];

                if (!method_exists($ctrl, $action)) {
                    $this->error404("Action {$action} not found");
                    return;
                }

                call_user_func_array([$ctrl, $action], $params);
                return;
            }
        }

        $this->error404("Page not found: $url");
    }

    private function pathToRegex(string $path): string {
        // Chuyển :key, :id... thành regex group
        $pattern = preg_replace('/:([a-zA-Z_]+)/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function error404(string $message = 'Not Found'): void {
        http_response_code(404);
        echo "<h1>404 - Không tìm thấy trang</h1><p>$message</p>";
    }
}