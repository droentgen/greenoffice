-- Upgrade an existing database for:
--   • news: training document link + training clip link
--   • posters (โปสเตอร์ประชาสัมพันธ์)
-- Run once in the Supabase SQL editor (or psql). New installs already get this from schema.sql.

ALTER TABLE news
  ADD COLUMN IF NOT EXISTS doc_url   VARCHAR(1000) NULL,
  ADD COLUMN IF NOT EXISTS video_url VARCHAR(1000) NULL;

CREATE TABLE IF NOT EXISTS posters (
  id           SERIAL PRIMARY KEY,
  title        VARCHAR(255) NOT NULL,
  image_path   VARCHAR(255) NOT NULL,
  posted_at    DATE NULL,
  is_published SMALLINT NOT NULL DEFAULT 1,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
