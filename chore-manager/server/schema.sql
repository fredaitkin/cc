CREATE TABLE IF NOT EXISTS team_members (
    id         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(255) NOT NULL UNIQUE,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS chores (
    id                 INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title              VARCHAR(255) NOT NULL,
    category           VARCHAR(255) NOT NULL DEFAULT '',
    recurrence         ENUM('daily','monthly') NOT NULL,
    recur_day_of_month TINYINT UNSIGNED,
    rotation_index     INT          NOT NULL DEFAULT 0,
    active             TINYINT(1)   NOT NULL DEFAULT 1,
    created_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS chore_instances (
    id                 INT      NOT NULL AUTO_INCREMENT PRIMARY KEY,
    chore_id           INT      NOT NULL,
    assigned_member_id INT,
    due_date           DATE     NOT NULL,
    completed_at       DATETIME,
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_chore_date (chore_id, due_date),
    CONSTRAINT fk_instance_chore  FOREIGN KEY (chore_id)           REFERENCES chores(id)       ON DELETE CASCADE,
    CONSTRAINT fk_instance_member FOREIGN KEY (assigned_member_id) REFERENCES team_members(id) ON DELETE SET NULL
);

CREATE INDEX idx_instances_due_date ON chore_instances(due_date);
CREATE INDEX idx_instances_chore_id ON chore_instances(chore_id);
