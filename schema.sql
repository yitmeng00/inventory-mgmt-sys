-- Compatible with MySQL 5.7+ / MariaDB 10.3+

CREATE DATABASE IF NOT EXISTS `inventory_db`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `inventory_db`;

CREATE TABLE IF NOT EXISTS `user` (
    `user_id`     INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `staff_id`    VARCHAR(20)      NOT NULL UNIQUE,
    `username`    VARCHAR(50)      NOT NULL UNIQUE,
    `password`    VARCHAR(255)     NOT NULL,
    `first_name`  VARCHAR(60)      NOT NULL,
    `last_name`   VARCHAR(60)      NOT NULL,
    `email`       VARCHAR(120)     DEFAULT NULL,
    `designation` VARCHAR(100)     DEFAULT NULL,
    `role`        ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_login`  DATETIME         DEFAULT NULL,
    PRIMARY KEY (`user_id`),
    INDEX `idx_user_username` (`username`),
    INDEX `idx_user_status`   (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin account
INSERT INTO `user` (`staff_id`, `username`, `password`, `first_name`, `last_name`, `role`, `status`)
VALUES (
    'ADM001',
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'System',
    'Administrator',
    'admin',
    'active'
) ON DUPLICATE KEY UPDATE `user_id` = `user_id`;

CREATE TABLE IF NOT EXISTS `category` (
    `category_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_name` VARCHAR(100) NOT NULL UNIQUE,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `supplier` (
    `supplier_id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `supplier_code`  VARCHAR(20)  NOT NULL UNIQUE,
    `supplier_name`  VARCHAR(150) NOT NULL,
    `contact_person` VARCHAR(100) DEFAULT NULL,
    `contact_no`     VARCHAR(30)  DEFAULT NULL,
    `email`          VARCHAR(120) DEFAULT NULL,
    `location`       VARCHAR(200) DEFAULT NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`supplier_id`),
    INDEX `idx_supplier_name` (`supplier_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product` (
    `product_id`   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `product_code` VARCHAR(20)     NOT NULL UNIQUE,
    `product_name` VARCHAR(200)    NOT NULL,
    `description`  TEXT            DEFAULT NULL,
    `category_id`  INT UNSIGNED    NOT NULL,
    `supplier_id`  INT UNSIGNED    NOT NULL,
    `cost_price`   DECIMAL(12, 2)  NOT NULL DEFAULT 0.00,
    `sale_price`   DECIMAL(12, 2)  NOT NULL DEFAULT 0.00,
    `quantity`     INT             NOT NULL DEFAULT 0,
    `img_path`     VARCHAR(255)    DEFAULT NULL,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`product_id`),
    INDEX `idx_product_category` (`category_id`),
    INDEX `idx_product_supplier` (`supplier_id`),
    CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`),
    CONSTRAINT `fk_product_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transaction_type` (
    `type_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type_name` VARCHAR(50)  NOT NULL UNIQUE,
    PRIMARY KEY (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `transaction_type` (`type_id`, `type_name`) VALUES
    (1, 'Sale'),
    (2, 'Purchase')
ON DUPLICATE KEY UPDATE `type_name` = VALUES(`type_name`);

CREATE TABLE IF NOT EXISTS `transaction` (
    `transaction_id`   INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `transaction_code` VARCHAR(20)    NOT NULL UNIQUE,
    `product_id`       INT UNSIGNED   NOT NULL,
    `type_id`          INT UNSIGNED   NOT NULL,
    `quantity`         INT            NOT NULL DEFAULT 1,
    `unit_price`       DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `created`          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`transaction_id`),
    INDEX `idx_txn_product`   (`product_id`),
    INDEX `idx_txn_type`      (`type_id`),
    INDEX `idx_txn_created`   (`created`),
    CONSTRAINT `fk_txn_product` FOREIGN KEY (`product_id`) REFERENCES `product`  (`product_id`),
    CONSTRAINT `fk_txn_type`    FOREIGN KEY (`type_id`)    REFERENCES `transaction_type` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
