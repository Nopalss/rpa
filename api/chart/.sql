CREATE TABLE tbl_chart_cache (
    user_id INT NOT NULL,
    site_name VARCHAR(50) NOT NULL,
    result_json LONGTEXT NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (user_id, site_name)
);


CREATE TABLE tbl_cpk_fastlane (
    user_id INT NOT NULL,
    site_name VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (user_id, site_name)
);


CREATE TABLE tbl_spc_model_settings (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT NOT NULL,
    site_name VARCHAR(50) NOT NULL,

    line_id INT NOT NULL,
    application_id INT NOT NULL,
    file_id INT NOT NULL,

    agg_mode ENUM('min','max') NOT NULL,
    start_col INT NOT NULL,
    end_col INT NOT NULL,

    -- Histogram settings
    standard_lower DECIMAL(18,6) NOT NULL,
    standard_upper DECIMAL(18,6) NOT NULL,
    lower_boundary DECIMAL(18,6) NOT NULL,
    interval_width DECIMAL(18,6) NOT NULL,

    -- SPC limits
    cp_limit DECIMAL(10,4) DEFAULT NULL,
    cpk_limit DECIMAL(10,4) DEFAULT NULL,

    is_active TINYINT(1) DEFAULT 1,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_user_site (user_id, site_name),
    KEY idx_file (file_id),
    KEY idx_line (line_id)
);
