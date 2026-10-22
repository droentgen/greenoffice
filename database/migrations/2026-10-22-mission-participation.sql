-- Upgrade an existing database for:
--   • clickable missions: people join with name + หน่วยงาน/สาขาวิชา, progress computed from joins
-- Run once via the Supabase SQL editor (or psql). New installs already get this from schema.sql.

CREATE TABLE IF NOT EXISTS units (
  id        SERIAL PRIMARY KEY,
  name      VARCHAR(150) NOT NULL,
  sort      INT NOT NULL DEFAULT 0,
  is_active SMALLINT NOT NULL DEFAULT 1
);

ALTER TABLE missions ADD COLUMN IF NOT EXISTS target_count INT NOT NULL DEFAULT 0;
ALTER TABLE missions DROP COLUMN IF EXISTS progress;

CREATE TABLE IF NOT EXISTS mission_participants (
  id         SERIAL PRIMARY KEY,
  mission_id INT NOT NULL REFERENCES missions(id) ON DELETE CASCADE,
  name       VARCHAR(150) NOT NULL,
  unit_id    INT NOT NULL REFERENCES units(id),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_mission_participants_mission ON mission_participants (mission_id);
CREATE INDEX IF NOT EXISTS idx_mission_participants_unit ON mission_participants (unit_id, created_at);
