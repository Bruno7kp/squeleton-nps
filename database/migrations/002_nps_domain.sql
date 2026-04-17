CREATE TABLE IF NOT EXISTS admins (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  is_active INTEGER NOT NULL DEFAULT 1,
  last_login_at TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS projects (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE,
  public_key TEXT NOT NULL UNIQUE,
  description TEXT,
  is_active INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS surveys (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  project_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  slug TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'draft',
  trigger_event TEXT NOT NULL,
  title TEXT,
  description TEXT,
  settings_json TEXT,
  starts_at TEXT,
  ends_at TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
  UNIQUE (project_id, slug)
);

CREATE TABLE IF NOT EXISTS questions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  survey_id INTEGER NOT NULL,
  label TEXT NOT NULL,
  field_name TEXT NOT NULL,
  question_type TEXT NOT NULL,
  position INTEGER NOT NULL,
  is_required INTEGER NOT NULL DEFAULT 0,
  placeholder TEXT,
  help_text TEXT,
  options_json TEXT,
  scale_min INTEGER,
  scale_max INTEGER,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (survey_id) REFERENCES surveys (id) ON DELETE CASCADE,
  UNIQUE (survey_id, field_name)
);

CREATE TABLE IF NOT EXISTS survey_rules (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  survey_id INTEGER NOT NULL,
  source_question_id INTEGER NOT NULL,
  operator TEXT NOT NULL,
  compare_value TEXT NOT NULL,
  target_question_id INTEGER NOT NULL,
  action TEXT NOT NULL DEFAULT 'show',
  position INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (survey_id) REFERENCES surveys (id) ON DELETE CASCADE,
  FOREIGN KEY (source_question_id) REFERENCES questions (id) ON DELETE CASCADE,
  FOREIGN KEY (target_question_id) REFERENCES questions (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS submissions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  survey_id INTEGER NOT NULL,
  project_id INTEGER NOT NULL,
  trigger_event TEXT NOT NULL,
  source_url TEXT,
  user_identifier TEXT,
  session_identifier TEXT,
  user_agent TEXT,
  ip_hash TEXT,
  score_nps INTEGER,
  is_completed INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (survey_id) REFERENCES surveys (id) ON DELETE CASCADE,
  FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS submission_answers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  submission_id INTEGER NOT NULL,
  question_id INTEGER NOT NULL,
  answer_text TEXT,
  answer_number REAL,
  answer_json TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (submission_id) REFERENCES submissions (id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_surveys_project_status_trigger
  ON surveys (project_id, status, trigger_event);

CREATE INDEX IF NOT EXISTS idx_questions_survey_position
  ON questions (survey_id, position);

CREATE INDEX IF NOT EXISTS idx_rules_survey_position
  ON survey_rules (survey_id, position);

CREATE INDEX IF NOT EXISTS idx_submissions_survey_created
  ON submissions (survey_id, created_at);

CREATE INDEX IF NOT EXISTS idx_submissions_project_created
  ON submissions (project_id, created_at);

CREATE INDEX IF NOT EXISTS idx_submissions_score
  ON submissions (score_nps);

CREATE INDEX IF NOT EXISTS idx_answers_submission
  ON submission_answers (submission_id);

CREATE INDEX IF NOT EXISTS idx_answers_question
  ON submission_answers (question_id);
