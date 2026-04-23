-- Eventos: blog posts (catering & team-building)
CREATE TABLE IF NOT EXISTS eventos_posts (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    category       VARCHAR(50)  NOT NULL DEFAULT 'catering',
    title          VARCHAR(255) NOT NULL DEFAULT '',
    body           TEXT,
    image_filename VARCHAR(255) DEFAULT NULL,
    sort_order     INT          DEFAULT 0,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- Contact form submissions from eventos pages
CREATE TABLE IF NOT EXISTS contact_submissions (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(255) NOT NULL DEFAULT '',
    email        VARCHAR(255) NOT NULL DEFAULT '',
    phone        VARCHAR(100) DEFAULT '',
    message      TEXT,
    source_page  VARCHAR(100) DEFAULT '',
    submitted_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);
