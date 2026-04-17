<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);
if (file_exists($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

$dbPath = $_ENV['DB_PATH'] ?? 'database/app.sqlite';
$fullPath = $root . '/' . ltrim($dbPath, '/');

$pdo = new PDO('sqlite:' . $fullPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

$pdo->exec('CREATE TABLE IF NOT EXISTS app_meta (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  meta_key TEXT NOT NULL UNIQUE,
  meta_value TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

$adminUser = $_ENV['ADMIN_USER'] ?? 'admin';
$adminPass = $_ENV['ADMIN_PASS'] ?? 'admin123';
$adminHash = password_hash($adminPass, PASSWORD_DEFAULT);

$pdo->beginTransaction();

try {
  $pdo->exec('DELETE FROM submission_answers');
  $pdo->exec('DELETE FROM submissions');
  $pdo->exec('DELETE FROM survey_rules');
  $pdo->exec('DELETE FROM questions');
  $pdo->exec('DELETE FROM surveys');
  $pdo->exec('DELETE FROM projects');
  $pdo->exec('DELETE FROM admins');

  $adminStmt = $pdo->prepare('INSERT INTO admins (username, password_hash, is_active) VALUES (:username, :password_hash, 1)');
  $adminStmt->execute([
    'username' => $adminUser,
    'password_hash' => $adminHash,
  ]);

  $projectStmt = $pdo->prepare('INSERT INTO projects (name, slug, public_key, description, is_active) VALUES (:name, :slug, :public_key, :description, 1)');
  $projectStmt->execute([
    'name' => 'Acme Plataforma SaaS',
    'slug' => 'acme-plataforma-saas',
    'public_key' => 'nps_pk_acme_demo_001',
    'description' => 'Projeto de demonstracao para fluxo NPS com widget embed e gatilhos.',
  ]);
  $projectId = (int) $pdo->lastInsertId();

  $surveyStmt = $pdo->prepare('INSERT INTO surveys (project_id, name, slug, status, trigger_event, title, description, settings_json) VALUES (:project_id, :name, :slug, :status, :trigger_event, :title, :description, :settings_json)');

  $surveyStmt->execute([
    'project_id' => $projectId,
    'name' => 'NPS Pos-Video',
    'slug' => 'nps-pos-video',
    'status' => 'published',
    'trigger_event' => 'after_completed_video',
    'title' => 'Como voce avalia sua experiencia?',
    'description' => 'Pesquisa principal exibida apos concluir o video.',
    'settings_json' => json_encode(['theme' => 'blue', 'show_logo' => true], JSON_UNESCAPED_UNICODE),
  ]);
  $surveyVideoId = (int) $pdo->lastInsertId();

  $surveyStmt->execute([
    'project_id' => $projectId,
    'name' => 'NPS Onboarding',
    'slug' => 'nps-onboarding',
    'status' => 'published',
    'trigger_event' => 'on_load',
    'title' => 'Primeira impressao da plataforma',
    'description' => 'Pesquisa curta exibida no inicio da sessao.',
    'settings_json' => json_encode(['theme' => 'blue', 'show_logo' => false], JSON_UNESCAPED_UNICODE),
  ]);
  $surveyOnLoadId = (int) $pdo->lastInsertId();

  $questionStmt = $pdo->prepare('INSERT INTO questions (survey_id, label, field_name, question_type, position, is_required, placeholder, help_text, options_json, scale_min, scale_max) VALUES (:survey_id, :label, :field_name, :question_type, :position, :is_required, :placeholder, :help_text, :options_json, :scale_min, :scale_max)');

  $questionStmt->execute([
    'survey_id' => $surveyVideoId,
    'label' => 'De 0 a 10, o quanto voce recomendaria nosso produto?',
    'field_name' => 'nps_score',
    'question_type' => 'score_0_10',
    'position' => 1,
    'is_required' => 1,
    'placeholder' => null,
    'help_text' => '0 significa nada provavel e 10 extremamente provavel.',
    'options_json' => null,
    'scale_min' => 0,
    'scale_max' => 10,
  ]);
  $qVideoScore = (int) $pdo->lastInsertId();

  $questionStmt->execute([
    'survey_id' => $surveyVideoId,
    'label' => 'Qual foi o principal motivo da sua nota?',
    'field_name' => 'main_reason',
    'question_type' => 'select',
    'position' => 2,
    'is_required' => 0,
    'placeholder' => null,
    'help_text' => null,
    'options_json' => json_encode([
      'Preco',
      'Facilidade de uso',
      'Performance',
      'Atendimento',
      'Funcionalidades',
    ], JSON_UNESCAPED_UNICODE),
    'scale_min' => null,
    'scale_max' => null,
  ]);
  $qVideoReason = (int) $pdo->lastInsertId();

  $questionStmt->execute([
    'survey_id' => $surveyVideoId,
    'label' => 'O que podemos melhorar para sua experiencia ficar melhor?',
    'field_name' => 'improvement_feedback',
    'question_type' => 'textarea',
    'position' => 3,
    'is_required' => 0,
    'placeholder' => 'Conte para a gente em poucas palavras',
    'help_text' => 'Pergunta condicional para notas baixas.',
    'options_json' => null,
    'scale_min' => null,
    'scale_max' => null,
  ]);
  $qVideoImprove = (int) $pdo->lastInsertId();

  $questionStmt->execute([
    'survey_id' => $surveyOnLoadId,
    'label' => 'Qual sua expectativa inicial com a plataforma?',
    'field_name' => 'expectation_rating',
    'question_type' => 'stars_0_5',
    'position' => 1,
    'is_required' => 1,
    'placeholder' => null,
    'help_text' => null,
    'options_json' => null,
    'scale_min' => 0,
    'scale_max' => 5,
  ]);
  $qOnLoadStars = (int) $pdo->lastInsertId();

  $questionStmt->execute([
    'survey_id' => $surveyOnLoadId,
    'label' => 'Quais recursos voce pretende usar?',
    'field_name' => 'planned_features',
    'question_type' => 'checkbox',
    'position' => 2,
    'is_required' => 0,
    'placeholder' => null,
    'help_text' => null,
    'options_json' => json_encode([
      'Dashboard',
      'Automacoes',
      'Relatorios',
            'Integracoes',
    ], JSON_UNESCAPED_UNICODE),
    'scale_min' => null,
    'scale_max' => null,
  ]);
  $qOnLoadFeatures = (int) $pdo->lastInsertId();

  $ruleStmt = $pdo->prepare('INSERT INTO survey_rules (survey_id, source_question_id, operator, compare_value, target_question_id, action, position) VALUES (:survey_id, :source_question_id, :operator, :compare_value, :target_question_id, :action, :position)');
  $ruleStmt->execute([
    'survey_id' => $surveyVideoId,
    'source_question_id' => $qVideoScore,
    'operator' => 'lt',
    'compare_value' => '5',
    'target_question_id' => $qVideoImprove,
    'action' => 'show',
    'position' => 1,
  ]);

  $submissionStmt = $pdo->prepare('INSERT INTO submissions (survey_id, project_id, trigger_event, source_url, user_identifier, session_identifier, user_agent, ip_hash, score_nps, is_completed, created_at) VALUES (:survey_id, :project_id, :trigger_event, :source_url, :user_identifier, :session_identifier, :user_agent, :ip_hash, :score_nps, 1, :created_at)');

  $answerStmt = $pdo->prepare('INSERT INTO submission_answers (submission_id, question_id, answer_text, answer_number, answer_json, created_at) VALUES (:submission_id, :question_id, :answer_text, :answer_number, :answer_json, :created_at)');

  $videoSubmissions = [
    ['score' => 10, 'reason' => 'Funcionalidades', 'comment' => 'Fluxo muito simples, gostei bastante.', 'offset' => '-1 day'],
    ['score' => 9, 'reason' => 'Facilidade de uso', 'comment' => 'Onboarding bem direto ao ponto.', 'offset' => '-20 hours'],
    ['score' => 8, 'reason' => 'Performance', 'comment' => 'Rapido, mas pode melhorar no mobile.', 'offset' => '-16 hours'],
    ['score' => 6, 'reason' => 'Preco', 'comment' => 'Achei um pouco caro para meu caso.', 'offset' => '-10 hours'],
    ['score' => 4, 'reason' => 'Atendimento', 'comment' => 'Demora no retorno do suporte.', 'offset' => '-8 hours'],
    ['score' => 3, 'reason' => 'Performance', 'comment' => 'Tive lentidao em alguns momentos.', 'offset' => '-4 hours'],
  ];

  foreach ($videoSubmissions as $index => $row) {
    $createdAt = (new DateTimeImmutable('now'))->modify($row['offset'])->format('Y-m-d H:i:s');

    $submissionStmt->execute([
      'survey_id' => $surveyVideoId,
      'project_id' => $projectId,
      'trigger_event' => 'after_completed_video',
      'source_url' => 'https://acme.example.com/video-demo',
      'user_identifier' => 'user-video-' . ($index + 1),
      'session_identifier' => 'sess-video-' . ($index + 1),
      'user_agent' => 'Mozilla/5.0 (NPS Demo)',
      'ip_hash' => hash('sha256', 'video-ip-' . ($index + 1)),
      'score_nps' => $row['score'],
      'created_at' => $createdAt,
    ]);

    $submissionId = (int) $pdo->lastInsertId();

    $answerStmt->execute([
      'submission_id' => $submissionId,
      'question_id' => $qVideoScore,
      'answer_text' => null,
      'answer_number' => $row['score'],
      'answer_json' => null,
      'created_at' => $createdAt,
    ]);

    $answerStmt->execute([
      'submission_id' => $submissionId,
      'question_id' => $qVideoReason,
      'answer_text' => $row['reason'],
      'answer_number' => null,
      'answer_json' => null,
      'created_at' => $createdAt,
    ]);

    if ($row['score'] < 5) {
      $answerStmt->execute([
        'submission_id' => $submissionId,
        'question_id' => $qVideoImprove,
        'answer_text' => $row['comment'],
        'answer_number' => null,
        'answer_json' => null,
        'created_at' => $createdAt,
      ]);
    }
  }

  $onLoadSubmissions = [
    ['stars' => 5, 'features' => ['Dashboard', 'Relatorios'], 'offset' => '-3 hours'],
    ['stars' => 4, 'features' => ['Automacoes'], 'offset' => '-2 hours'],
    ['stars' => 3, 'features' => ['Integracoes', 'Dashboard'], 'offset' => '-1 hour'],
  ];

  foreach ($onLoadSubmissions as $index => $row) {
    $createdAt = (new DateTimeImmutable('now'))->modify($row['offset'])->format('Y-m-d H:i:s');

    $submissionStmt->execute([
      'survey_id' => $surveyOnLoadId,
      'project_id' => $projectId,
      'trigger_event' => 'on_load',
      'source_url' => 'https://acme.example.com/dashboard',
      'user_identifier' => 'user-onload-' . ($index + 1),
      'session_identifier' => 'sess-onload-' . ($index + 1),
      'user_agent' => 'Mozilla/5.0 (NPS Demo)',
      'ip_hash' => hash('sha256', 'onload-ip-' . ($index + 1)),
      'score_nps' => $row['stars'] * 2,
      'created_at' => $createdAt,
    ]);

    $submissionId = (int) $pdo->lastInsertId();

    $answerStmt->execute([
      'submission_id' => $submissionId,
      'question_id' => $qOnLoadStars,
      'answer_text' => null,
      'answer_number' => $row['stars'],
      'answer_json' => null,
      'created_at' => $createdAt,
    ]);

    $answerStmt->execute([
      'submission_id' => $submissionId,
      'question_id' => $qOnLoadFeatures,
      'answer_text' => null,
      'answer_number' => null,
      'answer_json' => json_encode($row['features'], JSON_UNESCAPED_UNICODE),
      'created_at' => $createdAt,
    ]);
  }

  $metaStmt = $pdo->prepare('INSERT INTO app_meta (meta_key, meta_value) VALUES (:key, :value) ON CONFLICT(meta_key) DO UPDATE SET meta_value = excluded.meta_value');
  $metaStmt->execute([
    'key' => 'seed_version',
    'value' => 'phase-1',
  ]);

  $pdo->commit();
} catch (Throwable $exception) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }

  throw $exception;
}

echo 'Seed fase 1 aplicado com dados de dominio NPS.' . PHP_EOL;
