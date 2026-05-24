-- ============================================
-- 金貔貅 EA 后台数据库 Schema
-- 数据库名默认 jinpixiu，可通过 .env 修改 DB_NAME
-- 执行: mysql -u root -p < schema.sql
-- ============================================

CREATE DATABASE IF NOT EXISTS `jinpixiu`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `jinpixiu`;

-- ---------- 管理员表 ----------
CREATE TABLE IF NOT EXISTS `admins` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(64)  NOT NULL UNIQUE COMMENT '登录账号',
  `password_hash` VARCHAR(255) NOT NULL        COMMENT 'werkzeug pbkdf2 哈希',
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_at` DATETIME     NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台管理员';

-- ---------- 授权码记录表 ----------
CREATE TABLE IF NOT EXISTS `licenses` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `account`      VARCHAR(32)  NOT NULL          COMMENT '交易账号(纯数字)',
  `expiry_date`  DATE         NOT NULL          COMMENT '授权有效期',
  `license_code` TEXT         NOT NULL          COMMENT 'Base64授权码',
  `xor_key`      VARCHAR(64)  NOT NULL          COMMENT '本条记录使用的XOR密钥',
  `product`      VARCHAR(32)  NOT NULL DEFAULT 'XAUUSD' COMMENT '产品标识(XAUUSD/EURUSD)',
  `remark`       VARCHAR(255) NULL              COMMENT '备注：客户名/Telegram等',
  `created_by`   VARCHAR(64)  NOT NULL          COMMENT '生成人',
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_account_expiry_product` (`account`, `expiry_date`, `product`),
  KEY `idx_account`     (`account`),
  KEY `idx_expiry`      (`expiry_date`),
  KEY `idx_created_at`  (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='授权码生成记录';
