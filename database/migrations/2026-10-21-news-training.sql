-- Upgrade an existing database (created before 2026-10-21) for:
--   • news category "อบรม/สัมมนา"
--   • training links: register / pre-test / post-test / evaluation
-- Run once in the Supabase SQL editor (or psql). New installs already get this from schema.sql.

ALTER TABLE news DROP CONSTRAINT IF EXISTS news_category_check;
ALTER TABLE news ALTER COLUMN category TYPE VARCHAR(20);
ALTER TABLE news ADD CONSTRAINT news_category_check CHECK (category IN ('กิจกรรม','ข่าวสาร','ประกาศ','อบรม/สัมมนา'));

ALTER TABLE news
  ADD COLUMN IF NOT EXISTS register_url VARCHAR(1000) NULL,
  ADD COLUMN IF NOT EXISTS pretest_url  VARCHAR(1000) NULL,
  ADD COLUMN IF NOT EXISTS posttest_url VARCHAR(1000) NULL,
  ADD COLUMN IF NOT EXISTS eval_url     VARCHAR(1000) NULL;
