-- ============================================
-- BugTracker Pro - Database Schema v1.0
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- Bảng users
CREATE TABLE IF NOT EXISTS users (
    id             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    username       VARCHAR(50)     NOT NULL,
    email          VARCHAR(255)    NOT NULL,
    password_hash  VARCHAR(255)    NOT NULL,
    full_name      VARCHAR(100)    NOT NULL,
    avatar         VARCHAR(255)    DEFAULT NULL,
    role           ENUM('admin','manager','developer','reporter','viewer') NOT NULL DEFAULT 'developer',
    is_active      TINYINT(1)      NOT NULL DEFAULT 1,
    email_verified TINYINT(1)      NOT NULL DEFAULT 0,
    timezone       VARCHAR(50)     NOT NULL DEFAULT 'UTC',
    language       VARCHAR(10)     NOT NULL DEFAULT 'vi',
    bio            TEXT            DEFAULT NULL,
    created_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login     TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_username (username),
    UNIQUE KEY uq_email    (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng workspaces
CREATE TABLE IF NOT EXISTS workspaces (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name        VARCHAR(200)  NOT NULL,
    slug        VARCHAR(100)  NOT NULL,
    type        ENUM('personal','team','enterprise') NOT NULL DEFAULT 'team',
    owner_id    INT UNSIGNED  NOT NULL,
    logo        VARCHAR(255)  DEFAULT NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_slug (slug),
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng workspace_members
CREATE TABLE IF NOT EXISTS workspace_members (
    workspace_id  INT UNSIGNED NOT NULL,
    user_id       INT UNSIGNED NOT NULL,
    role          ENUM('admin','manager','developer','reporter','viewer') NOT NULL DEFAULT 'developer',
    joined_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (workspace_id, user_id),
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)      REFERENCES users(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng workspace_invitations
CREATE TABLE IF NOT EXISTS workspace_invitations (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    workspace_id  INT UNSIGNED  NOT NULL,
    email         VARCHAR(255)  NOT NULL,
    role          ENUM('admin','manager','developer','reporter','viewer') NOT NULL DEFAULT 'developer',
    token         VARCHAR(64)   NOT NULL,
    invited_by    INT UNSIGNED  NOT NULL,
    expires_at    TIMESTAMP     NOT NULL,
    accepted_at   TIMESTAMP     NULL DEFAULT NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_token (token),
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by)   REFERENCES users(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng projects
CREATE TABLE IF NOT EXISTS projects (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    workspace_id INT UNSIGNED  NOT NULL,
    name         VARCHAR(200)  NOT NULL,
    `key`        VARCHAR(10)   NOT NULL,
    description  TEXT          DEFAULT NULL,
    status       ENUM('active','archived','closed') NOT NULL DEFAULT 'active',
    owner_id     INT UNSIGNED  NOT NULL,
    visibility   ENUM('public','private','team_only') NOT NULL DEFAULT 'private',
    cover_image  VARCHAR(255)  DEFAULT NULL,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_key (workspace_id, `key`),
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id)     REFERENCES users(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng project_members
CREATE TABLE IF NOT EXISTS project_members (
    project_id  INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    role        ENUM('admin','manager','developer','reporter','viewer') NOT NULL DEFAULT 'developer',
    joined_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng bugs (issue chính)
CREATE TABLE IF NOT EXISTS bugs (
    id                  INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    issue_key           VARCHAR(20)    NOT NULL,
    project_id          INT UNSIGNED   NOT NULL,
    title               VARCHAR(500)   NOT NULL,
    description         LONGTEXT       DEFAULT NULL,
    type                ENUM('bug','feature','task','improvement','question','epic') NOT NULL DEFAULT 'bug',
    status              ENUM('open','in_progress','review','resolved','closed') NOT NULL DEFAULT 'open',
    priority            ENUM('critical','high','medium','low','trivial') NOT NULL DEFAULT 'medium',
    severity            ENUM('blocker','major','minor','cosmetic') DEFAULT NULL,
    reporter_id         INT UNSIGNED   NOT NULL,
    assignee_id         INT UNSIGNED   DEFAULT NULL,
    sprint_id           INT UNSIGNED   DEFAULT NULL,
    milestone_id        INT UNSIGNED   DEFAULT NULL,
    due_date            DATE           DEFAULT NULL,
    estimated_hours     DECIMAL(5,1)   DEFAULT NULL,
    actual_hours        DECIMAL(5,1)   DEFAULT NULL,
    steps_to_reproduce  TEXT           DEFAULT NULL,
    environment         VARCHAR(255)   DEFAULT NULL,
    browser_info        VARCHAR(255)   DEFAULT NULL,
    resolution          TEXT           DEFAULT NULL,
    votes               INT            NOT NULL DEFAULT 0,
    created_at          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at         TIMESTAMP      NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_issue_key (project_id, issue_key),
    FOREIGN KEY (project_id)  REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (assignee_id) REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng comments
CREATE TABLE IF NOT EXISTS comments (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    bug_id      INT UNSIGNED  NOT NULL,
    user_id     INT UNSIGNED  NOT NULL,
    content     TEXT          NOT NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (bug_id)  REFERENCES bugs(id)  ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng attachments
CREATE TABLE IF NOT EXISTS attachments (
    id             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    bug_id         INT UNSIGNED   NOT NULL,
    user_id        INT UNSIGNED   NOT NULL,
    filename       VARCHAR(255)   NOT NULL,
    original_name  VARCHAR(255)   NOT NULL,
    file_size      INT UNSIGNED   NOT NULL,
    mime_type      VARCHAR(100)   NOT NULL,
    created_at     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (bug_id)  REFERENCES bugs(id)  ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng labels
CREATE TABLE IF NOT EXISTS labels (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    project_id  INT UNSIGNED  NOT NULL,
    name        VARCHAR(50)   NOT NULL,
    color       VARCHAR(7)    NOT NULL DEFAULT '#0078D4',
    description VARCHAR(255)  DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng bug_labels (nhiều-nhiều)
CREATE TABLE IF NOT EXISTS bug_labels (
    bug_id    INT UNSIGNED NOT NULL,
    label_id  INT UNSIGNED NOT NULL,
    PRIMARY KEY (bug_id, label_id),
    FOREIGN KEY (bug_id)   REFERENCES bugs(id)   ON DELETE CASCADE,
    FOREIGN KEY (label_id) REFERENCES labels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng sprints
CREATE TABLE IF NOT EXISTS sprints (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    project_id  INT UNSIGNED  NOT NULL,
    name        VARCHAR(200)  NOT NULL,
    goal        TEXT          DEFAULT NULL,
    start_date  DATE          DEFAULT NULL,
    end_date    DATE          DEFAULT NULL,
    status      ENUM('planning','active','completed') NOT NULL DEFAULT 'planning',
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng milestones
CREATE TABLE IF NOT EXISTS milestones (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    project_id  INT UNSIGNED  NOT NULL,
    title       VARCHAR(200)  NOT NULL,
    description TEXT          DEFAULT NULL,
    due_date    DATE          DEFAULT NULL,
    status      ENUM('open','closed') NOT NULL DEFAULT 'open',
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng activity_log
CREATE TABLE IF NOT EXISTS activity_log (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED   NOT NULL,
    bug_id      INT UNSIGNED   DEFAULT NULL,
    project_id  INT UNSIGNED   DEFAULT NULL,
    action      VARCHAR(100)   NOT NULL,
    old_value   TEXT           DEFAULT NULL,
    new_value   TEXT           DEFAULT NULL,
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng notifications
CREATE TABLE IF NOT EXISTS notifications (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED   NOT NULL,
    type        VARCHAR(50)    NOT NULL,
    title       VARCHAR(255)   NOT NULL,
    message     TEXT           DEFAULT NULL,
    link        VARCHAR(255)   DEFAULT NULL,
    is_read     TINYINT(1)     NOT NULL DEFAULT 0,
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng user_settings
CREATE TABLE IF NOT EXISTS user_settings (
    user_id        INT UNSIGNED  NOT NULL,
    setting_key    VARCHAR(100)  NOT NULL,
    setting_value  TEXT          DEFAULT NULL,
    PRIMARY KEY (user_id, setting_key),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng password_resets
CREATE TABLE IF NOT EXISTS password_resets (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    email       VARCHAR(255)  NOT NULL,
    token       VARCHAR(64)   NOT NULL,
    expires_at  TIMESTAMP     NOT NULL,
    used_at     TIMESTAMP     NULL DEFAULT NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_token (token),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Seed: tài khoản admin mặc định
-- Password: Admin@123456
-- ============================================
INSERT INTO users (username, email, password_hash, full_name, role, is_active, email_verified)
VALUES ('admin', 'admin@bugtracker.local',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'System Administrator', 'admin', 1, 1);