CREATE TABLE tbl_chart_cache_minmax (
    user_id           INT NOT NULL,
    site_name         VARCHAR(50) NOT NULL,
    production_date   DATE NOT NULL,

    start_col         INT NOT NULL,
    end_col           INT NOT NULL,
    agg_mode          ENUM('min','max') NOT NULL,

    line_id           INT DEFAULT NULL,
    application_id    INT DEFAULT NULL,
    file_id           INT DEFAULT NULL,

    result_json       LONGTEXT NOT NULL,

    calculated_at     DATETIME NOT NULL,
    updated_at        DATETIME NOT NULL,

    PRIMARY KEY (user_id, site_name, production_date),
    INDEX idx_site_date (site_name, production_date),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;
