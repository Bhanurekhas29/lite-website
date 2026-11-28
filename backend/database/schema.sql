-- E for Events: database schema (MySQL 8)
-- Single source of truth. Change tables here, never by hand in phpMyAdmin.
-- All times are stored in UTC and shown in the time zone from Settings.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ---------------------------------------------------------------------
-- People and access
-- ---------------------------------------------------------------------
CREATE TABLE admins (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(60)  NOT NULL,
  email         VARCHAR(190) NULL,
  password_hash VARCHAR(255) NOT NULL,
  display_name  VARCHAR(120) NOT NULL,
  photo_media_id INT UNSIGNED NULL,
  is_super      TINYINT(1)   NOT NULL DEFAULT 0,
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  last_login_at TIMESTAMP    NULL,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admins_username (username),
  UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- "Forgot password": one-hour reset links (only a hash of the link is stored)
-- ---------------------------------------------------------------------
CREATE TABLE password_resets (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id   INT UNSIGNED NOT NULL,
  token_hash CHAR(64)     NOT NULL,
  expires_at TIMESTAMP    NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_reset_token (token_hash),
  KEY idx_reset_admin (admin_id, created_at),
  CONSTRAINT fk_reset_admin FOREIGN KEY (admin_id) REFERENCES admins (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Media library (files live in backend/media, database stores the path)
-- ---------------------------------------------------------------------
CREATE TABLE media (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  file_name   VARCHAR(255) NOT NULL,
  file_path   VARCHAR(255) NOT NULL,
  alt_text    VARCHAR(255) NOT NULL DEFAULT '',
  mime_type   VARCHAR(100) NOT NULL,
  size_bytes  INT UNSIGNED NOT NULL DEFAULT 0,
  width       INT UNSIGNED NULL,
  height      INT UNSIGNED NULL,
  uploaded_by INT UNSIGNED NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_media_path (file_path),
  CONSTRAINT fk_media_admin FOREIGN KEY (uploaded_by) REFERENCES admins (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE admins
  ADD CONSTRAINT fk_admins_photo FOREIGN KEY (photo_media_id) REFERENCES media (id) ON DELETE SET NULL;

CREATE TABLE admin_permissions (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id      INT UNSIGNED NOT NULL,
  resource_type ENUM('page','projects','enquiries','media','settings') NOT NULL,
  resource_id   INT UNSIGNED NOT NULL DEFAULT 0, -- page id when resource_type = 'page', else 0
  can_view      TINYINT(1) NOT NULL DEFAULT 0,
  can_create    TINYINT(1) NOT NULL DEFAULT 0,
  can_edit      TINYINT(1) NOT NULL DEFAULT 0,
  can_delete    TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permission (admin_id, resource_type, resource_id),
  CONSTRAINT fk_perm_admin FOREIGN KEY (admin_id) REFERENCES admins (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Website content: page > section > item, with every editable field
-- ---------------------------------------------------------------------
CREATE TABLE pages (
  id                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title                  VARCHAR(150) NOT NULL,
  menu_label             VARCHAR(100) NOT NULL,
  footer_label           VARCHAR(100) NULL,
  slug                   VARCHAR(120) NOT NULL,
  is_visible             TINYINT(1) NOT NULL DEFAULT 1,
  is_locked              TINYINT(1) NOT NULL DEFAULT 0,
  show_in_header         TINYINT(1) NOT NULL DEFAULT 1,
  show_in_footer         TINYINT(1) NOT NULL DEFAULT 1,
  sort_order             INT NOT NULL DEFAULT 0,
  seo_title              VARCHAR(255) NULL,
  seo_description        VARCHAR(500) NULL,
  seo_keywords_primary   VARCHAR(500) NULL,
  seo_keywords_secondary VARCHAR(500) NULL,
  created_at             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pages_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sections (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  page_id    INT UNSIGNED NOT NULL,
  type       VARCHAR(50)  NOT NULL, -- hero, stats, image_text, card_grid, steps, icon_list, image_cards, video, project_cards, cta, contact_info, enquiry_form
  label      VARCHAR(150) NOT NULL,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_sections_page (page_id, sort_order),
  CONSTRAINT fk_sections_page FOREIGN KEY (page_id) REFERENCES pages (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE section_items (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  section_id INT UNSIGNED NOT NULL,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_items_section (section_id, sort_order),
  CONSTRAINT fk_items_section FOREIGN KEY (section_id) REFERENCES sections (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE section_fields (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  section_id      INT UNSIGNED NOT NULL,
  item_id         INT UNSIGNED NULL,           -- NULL = field of the section itself
  field_key       VARCHAR(60)  NOT NULL,       -- e.g. eyebrow, heading, heading_accent, button_label
  value_text      TEXT NULL,
  media_id        INT UNSIGNED NULL,
  icon            VARCHAR(60)  NULL,           -- Font Awesome icon name
  link_type       ENUM('none','page','url') NOT NULL DEFAULT 'none',
  link_page_id    INT UNSIGNED NULL,
  link_section_id INT UNSIGNED NULL,
  link_url        VARCHAR(500) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_field (section_id, item_id, field_key),
  CONSTRAINT fk_fields_section FOREIGN KEY (section_id) REFERENCES sections (id) ON DELETE CASCADE,
  CONSTRAINT fk_fields_item    FOREIGN KEY (item_id) REFERENCES section_items (id) ON DELETE CASCADE,
  CONSTRAINT fk_fields_media   FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE RESTRICT,
  CONSTRAINT fk_fields_page    FOREIGN KEY (link_page_id) REFERENCES pages (id) ON DELETE RESTRICT,
  CONSTRAINT fk_fields_lsec    FOREIGN KEY (link_section_id) REFERENCES sections (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Projects (Our Work page, featured on Home)
-- ---------------------------------------------------------------------
CREATE TABLE project_categories (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(100) NOT NULL,
  icon       VARCHAR(60)  NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_category_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE projects (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id    INT UNSIGNED NOT NULL,
  title          VARCHAR(200) NOT NULL,
  image_media_id INT UNSIGNED NULL,
  hover_media_id INT UNSIGNED NULL,
  event_year     VARCHAR(20)  NULL,
  location       VARCHAR(150) NULL,
  our_role       VARCHAR(255) NULL,
  summary        VARCHAR(500) NULL,
  image_alt      VARCHAR(255) NULL,
  show_on_home   TINYINT(1) NOT NULL DEFAULT 0,
  is_visible     TINYINT(1) NOT NULL DEFAULT 1,
  sort_order     INT NOT NULL DEFAULT 0,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_projects_home (show_on_home, is_visible, sort_order),
  CONSTRAINT fk_projects_category FOREIGN KEY (category_id) REFERENCES project_categories (id) ON DELETE RESTRICT,
  CONSTRAINT fk_projects_media    FOREIGN KEY (image_media_id) REFERENCES media (id) ON DELETE RESTRICT,
  CONSTRAINT fk_projects_hover    FOREIGN KEY (hover_media_id) REFERENCES media (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_services (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id INT UNSIGNED NOT NULL,
  text       VARCHAR(200) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_pservices_project (project_id, sort_order),
  CONSTRAINT fk_pservices_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Enquiry form: editable choices, and received enquiries
-- ---------------------------------------------------------------------
CREATE TABLE form_options (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  field_key  ENUM('enquiry_type','attendance','services') NOT NULL,
  label      VARCHAR(150) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_form_option (field_key, label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE enquiries (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name           VARCHAR(150) NOT NULL,
  company        VARCHAR(150) NULL,
  email          VARCHAR(190) NOT NULL,
  phone          VARCHAR(40)  NULL,
  enquiry_type   VARCHAR(150) NOT NULL,
  event_timeframe VARCHAR(150) NULL,
  event_location VARCHAR(190) NOT NULL,
  attendance     VARCHAR(150) NULL,
  services       JSON NULL,
  message        TEXT NULL,
  consent_given  TINYINT(1) NOT NULL DEFAULT 0,
  consent_at     TIMESTAMP NULL,
  status         ENUM('new','read') NOT NULL DEFAULT 'new',
  ip_address     VARCHAR(45) NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_enquiries_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Settings: brand, contact, social, email (SMTP), general, SEO defaults
-- ---------------------------------------------------------------------
CREATE TABLE settings (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  group_name   VARCHAR(40)  NOT NULL,
  setting_key  VARCHAR(80)  NOT NULL,
  value_text   TEXT NULL,
  media_id     INT UNSIGNED NULL,
  is_encrypted TINYINT(1) NOT NULL DEFAULT 0,
  updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_setting (group_name, setting_key),
  CONSTRAINT fk_settings_media FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
