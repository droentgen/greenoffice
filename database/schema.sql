-- Green Office Digital Platform — database structure (PostgreSQL / Supabase)
-- Import this first, then seed.sql.

DROP TABLE IF EXISTS criteria_evidence, criteria_reports, criteria_subitems, criteria_indicators, criteria_issues, categories,
  resource_monthly, news, missions, activities, audit_findings, documents, complaints, complaint_plan_steps,
  game_responses, settings, users, app_sessions CASCADE;

-- Shared trigger: keep updated_at current on every UPDATE (replaces MySQL's "ON UPDATE CURRENT_TIMESTAMP").
CREATE OR REPLACE FUNCTION set_updated_at() RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ---------- Users & settings ----------
CREATE TABLE users (
  id            SERIAL PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  display_name  VARCHAR(120) NOT NULL DEFAULT '',
  role          VARCHAR(10)  NOT NULL DEFAULT 'staff' CHECK (role IN ('admin','editor','staff','auditor')),
  unit          VARCHAR(150) NOT NULL DEFAULT '',
  is_active     SMALLINT NOT NULL DEFAULT 1,
  last_login    TIMESTAMP NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
  k VARCHAR(60) PRIMARY KEY,
  v TEXT NOT NULL
);

-- PHP session storage (see app/session_db.php) — needed on hosts where each
-- request can land on a different machine with no shared local disk.
CREATE TABLE app_sessions (
  id          VARCHAR(128) PRIMARY KEY,
  data        TEXT NOT NULL DEFAULT '',
  last_access TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_app_sessions_last_access ON app_sessions (last_access);

-- ---------- Resource statistics (one row per month) ----------
-- GHG is not stored: it is calculated from fuel and electricity with the factors in settings.
CREATE TABLE resource_monthly (
  id         SERIAL PRIMARY KEY,
  year_be    SMALLINT NOT NULL,                  -- Buddhist-era year, e.g. 2569
  month      SMALLINT NOT NULL,                  -- 1-12
  fuel_l     DECIMAL(12,2) NOT NULL DEFAULT 0,    -- น้ำมันเชื้อเพลิง (ลิตร)
  elec_kwh   DECIMAL(12,2) NOT NULL DEFAULT 0,    -- ไฟฟ้า (kWh)
  water_m3   DECIMAL(12,2) NOT NULL DEFAULT 0,    -- น้ำ (ลบ.ม.)
  paper_ream DECIMAL(10,2) NOT NULL DEFAULT 0,    -- กระดาษ (รีม)
  note       VARCHAR(255) NOT NULL DEFAULT '',
  updated_by INT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (year_be, month)
);
CREATE TRIGGER trg_resource_monthly_updated BEFORE UPDATE ON resource_monthly
  FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ---------- Green Office criteria: 7 categories → issues → indicators → sub-items ----------
CREATE TABLE categories (
  id          SMALLINT PRIMARY KEY,               -- 1-7
  name        VARCHAR(255) NOT NULL,
  description VARCHAR(255) NOT NULL DEFAULT '',
  color_bg    VARCHAR(9) NOT NULL DEFAULT '#E6F3F1',
  color_fg    VARCHAR(9) NOT NULL DEFAULT '#006664'
);

CREATE TABLE criteria_issues (
  id          SERIAL PRIMARY KEY,
  category_id SMALLINT NOT NULL REFERENCES categories(id) ON DELETE CASCADE,
  code        VARCHAR(10) NOT NULL,              -- e.g. 1.1
  title       TEXT NOT NULL,
  sort        INT NOT NULL DEFAULT 0
);

CREATE TABLE criteria_indicators (
  id       SERIAL PRIMARY KEY,
  issue_id INT NOT NULL REFERENCES criteria_issues(id) ON DELETE CASCADE,
  code     VARCHAR(12) NOT NULL UNIQUE,          -- e.g. 1.1.1
  title    TEXT NOT NULL,
  sort     INT NOT NULL DEFAULT 0
);

CREATE TABLE criteria_subitems (
  id           SERIAL PRIMARY KEY,
  indicator_id INT NOT NULL REFERENCES criteria_indicators(id) ON DELETE CASCADE,
  idx          SMALLINT NOT NULL,                -- 0-based position
  text         TEXT NOT NULL
);

-- One report per indicator per year (รายงานผล)
CREATE TABLE criteria_reports (
  id           SERIAL PRIMARY KEY,
  indicator_id INT NOT NULL REFERENCES criteria_indicators(id) ON DELETE CASCADE,
  year_be      SMALLINT NOT NULL,
  report_text  TEXT NOT NULL,
  checks_json  VARCHAR(255) NOT NULL DEFAULT '[]', -- indexes of finished sub-items, e.g. [0,2]
  updated_by   INT NULL,
  updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (indicator_id, year_be)
);
CREATE TRIGGER trg_criteria_reports_updated BEFORE UPDATE ON criteria_reports
  FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- Evidence links (หลักฐาน) per indicator per year
CREATE TABLE criteria_evidence (
  id           SERIAL PRIMARY KEY,
  indicator_id INT NOT NULL REFERENCES criteria_indicators(id) ON DELETE CASCADE,
  year_be      SMALLINT NOT NULL,
  label        VARCHAR(255) NOT NULL,
  url          VARCHAR(1000) NOT NULL,
  created_by   INT NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_criteria_evidence_ind_year ON criteria_evidence (indicator_id, year_be);

-- ---------- Content ----------
CREATE TABLE news (
  id           SERIAL PRIMARY KEY,
  category     VARCHAR(20) NOT NULL DEFAULT 'ข่าวสาร' CHECK (category IN ('กิจกรรม','ข่าวสาร','ประกาศ','อบรม/สัมมนา')),
  title        VARCHAR(255) NOT NULL,
  summary      VARCHAR(500) NOT NULL DEFAULT '',
  body         TEXT NOT NULL,                     -- plain text + [ข้อความ](url) links and ![](url) images, see render_body()
  image_path   VARCHAR(255) NULL,
  register_url VARCHAR(1000) NULL,                -- ลิงก์ลงทะเบียน
  pretest_url  VARCHAR(1000) NULL,                -- ลิงก์สอบก่อนอบรม
  posttest_url VARCHAR(1000) NULL,                -- ลิงก์สอบหลังอบรม
  eval_url     VARCHAR(1000) NULL,                -- ลิงก์ประเมินการฝึกอบรม
  status       VARCHAR(10) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','review','published')),
  published_at DATE NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Documents shown on the About page tabs and the complaint plan page (image or PDF)
CREATE TABLE documents (
  id           SERIAL PRIMARY KEY,
  section      VARCHAR(20) NOT NULL CHECK (section IN ('policy','goals','team','measures','complaint_plan','complaint_notice')),
  title        VARCHAR(255) NOT NULL,
  file_path    VARCHAR(255) NULL,                -- uploads/docs/....pdf
  external_url VARCHAR(1000) NULL,               -- or a link (e.g. Google Drive)
  file_type    VARCHAR(10) NOT NULL DEFAULT 'pdf' CHECK (file_type IN ('pdf','image','link')),
  sort         INT NOT NULL DEFAULT 0,
  is_published SMALLINT NOT NULL DEFAULT 1,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Monthly missions on the home page
CREATE TABLE missions (
  id          SERIAL PRIMARY KEY,
  name        VARCHAR(200) NOT NULL,
  target_text VARCHAR(255) NOT NULL DEFAULT '',
  progress    SMALLINT NOT NULL DEFAULT 0,        -- 0-100
  color       VARCHAR(9) NOT NULL DEFAULT '#9CCB3B',
  sort        INT NOT NULL DEFAULT 0,
  is_active   SMALLINT NOT NULL DEFAULT 1
);

-- Plans & activities board
CREATE TABLE activities (
  id          SERIAL PRIMARY KEY,
  title       VARCHAR(255) NOT NULL,
  category_no SMALLINT NOT NULL DEFAULT 1,
  owner       VARCHAR(150) NOT NULL DEFAULT '',
  due_date    DATE NULL,
  status      VARCHAR(10) NOT NULL DEFAULT 'plan' CHECK (status IN ('plan','doing','done')),
  progress    SMALLINT NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Internal audit findings & corrective actions
CREATE TABLE audit_findings (
  id          SERIAL PRIMARY KEY,
  category_no SMALLINT NOT NULL DEFAULT 1,
  finding     TEXT NOT NULL,
  action      TEXT NOT NULL,
  owner       VARCHAR(150) NOT NULL DEFAULT '',
  due_date    DATE NULL,
  status      VARCHAR(10) NOT NULL DEFAULT 'open' CHECK (status IN ('open','fixing','closed')),
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ---------- Environmental feedback & complaints ----------
CREATE TABLE complaints (
  id            SERIAL PRIMARY KEY,
  ticket_no     VARCHAR(20) NOT NULL UNIQUE,     -- GO-2569-001
  type          VARCHAR(15) NOT NULL DEFAULT 'ข้อคิดเห็น' CHECK (type IN ('ข้อคิดเห็น','ข้อร้องเรียน','ข้อเสนอแนะ')),
  topic         VARCHAR(80) NOT NULL DEFAULT '',
  place         VARCHAR(255) NOT NULL DEFAULT '',
  detail        TEXT NOT NULL,
  rating        SMALLINT NULL,                   -- 1-5 satisfaction
  name          VARCHAR(150) NOT NULL DEFAULT '',
  contact       VARCHAR(150) NOT NULL DEFAULT '',
  status        VARCHAR(10) NOT NULL DEFAULT 'received' CHECK (status IN ('received','checking','fixing','done')),
  result_text   TEXT NULL,
  is_public     SMALLINT NOT NULL DEFAULT 0,      -- show in ประกาศข้อร้องเรียน
  public_title  VARCHAR(255) NOT NULL DEFAULT '',
  public_detail TEXT NULL,                        -- text shown publicly (never name/contact)
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TRIGGER trg_complaints_updated BEFORE UPDATE ON complaints
  FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE complaint_plan_steps (
  id        SERIAL PRIMARY KEY,
  step_no   SMALLINT NOT NULL,
  title     VARCHAR(150) NOT NULL,
  detail    VARCHAR(500) NOT NULL DEFAULT '',
  timeframe VARCHAR(80) NOT NULL DEFAULT '',
  owner     VARCHAR(150) NOT NULL DEFAULT ''
);

-- ---------- Intro game (Green Check-in) ----------
CREATE TABLE game_responses (
  id         SERIAL PRIMARY KEY,
  a_elec     SMALLINT NOT NULL,                  -- 0 = not yet, 1 = sometimes, 2 = always
  a_plastic  SMALLINT NOT NULL,
  a_paper    SMALLINT NOT NULL,
  a_commute  SMALLINT NOT NULL,
  score      SMALLINT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
