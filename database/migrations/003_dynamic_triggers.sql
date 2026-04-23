CREATE TABLE IF NOT EXISTS survey_triggers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  project_id INTEGER NOT NULL,
  survey_id INTEGER NOT NULL,
  trigger_key TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
  FOREIGN KEY (survey_id) REFERENCES surveys (id) ON DELETE CASCADE,
  UNIQUE (project_id, trigger_key)
);

CREATE INDEX IF NOT EXISTS idx_survey_triggers_project_trigger
  ON survey_triggers (project_id, trigger_key);

CREATE INDEX IF NOT EXISTS idx_survey_triggers_survey
  ON survey_triggers (survey_id);

INSERT OR IGNORE INTO survey_triggers (project_id, survey_id, trigger_key)
SELECT s.project_id, s.id, TRIM(s.trigger_event)
FROM surveys s
WHERE TRIM(COALESCE(s.trigger_event, '')) <> '';

CREATE TABLE IF NOT EXISTS trigger_event_logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  project_id INTEGER,
  trigger_key TEXT NOT NULL,
  public_key TEXT NOT NULL,
  source_url TEXT,
  user_identifier TEXT,
  matched_survey_id INTEGER,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE SET NULL,
  FOREIGN KEY (matched_survey_id) REFERENCES surveys (id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_trigger_event_logs_project_trigger
  ON trigger_event_logs (project_id, trigger_key, created_at);

CREATE INDEX IF NOT EXISTS idx_trigger_event_logs_public_key
  ON trigger_event_logs (public_key, created_at);
