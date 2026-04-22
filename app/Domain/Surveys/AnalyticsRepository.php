<?php

declare(strict_types=1);

namespace App\Domain\Surveys;

use App\Infrastructure\Database;
use PDO;

final class AnalyticsRepository
{
    public function listProjects(): array
    {
        $stmt = $this->connection()->query(
            'SELECT id, name
             FROM projects
             ORDER BY name ASC'
        );

        return $stmt->fetchAll() ?: [];
    }

    public function listTriggerEvents(): array
    {
        $stmt = $this->connection()->query(
            'SELECT DISTINCT trigger_event
             FROM submissions
             WHERE trigger_event IS NOT NULL
               AND trigger_event <> ""
             ORDER BY trigger_event ASC'
        );

        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        return array_values(array_filter(array_map(static fn ($item): string => (string) $item, $rows)));
    }

    public function homeStats(): array
    {
        $pdo = $this->connection();

        try {
            $projects = (int) $pdo->query('SELECT COUNT(1) FROM projects')->fetchColumn();
            $surveys = (int) $pdo->query('SELECT COUNT(1) FROM surveys')->fetchColumn();
            $submissions = (int) $pdo->query('SELECT COUNT(1) FROM submissions')->fetchColumn();
            $avgRaw = $pdo->query('SELECT AVG(score_nps) FROM submissions WHERE score_nps IS NOT NULL')->fetchColumn();

            return [
                'projects' => $projects,
                'surveys' => $surveys,
                'submissions' => $submissions,
                'avg_nps' => $avgRaw !== null ? round((float) $avgRaw, 2) : 0,
            ];
        } catch (\Throwable) {
            return ['projects' => 0, 'surveys' => 0, 'submissions' => 0, 'avg_nps' => 0];
        }
    }

    public function metrics(array $filters): array
    {
        $params = [];
        $whereSql = $this->buildWhereClause($filters, $params);

        $sql = 'SELECT
                    COUNT(1) AS total_submissions,
                    COALESCE(SUM(CASE WHEN sub.is_completed = 1 THEN 1 ELSE 0 END), 0) AS completed_submissions,
                    AVG(sub.score_nps) AS avg_nps,
                    COALESCE(SUM(CASE WHEN sub.score_nps >= 9 THEN 1 ELSE 0 END), 0) AS promoters,
                    COALESCE(SUM(CASE WHEN sub.score_nps <= 6 THEN 1 ELSE 0 END), 0) AS detractors,
                    COUNT(sub.score_nps) AS scored_submissions
                FROM submissions sub
                ' . $whereSql;

        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        $total = (int) ($row['total_submissions'] ?? 0);
        $completed = (int) ($row['completed_submissions'] ?? 0);
        $avgNps = $row['avg_nps'] !== null ? round((float) $row['avg_nps'], 2) : null;
        $completionRate = $total > 0 ? round(($completed / $total) * 100, 2) : 0.0;

        $scored = (int) ($row['scored_submissions'] ?? 0);
        $promoters = (int) ($row['promoters'] ?? 0);
        $detractors = (int) ($row['detractors'] ?? 0);
        $npsScore = $scored > 0
            ? round((($promoters / $scored) - ($detractors / $scored)) * 100, 1)
            : null;

        return [
            'total_submissions' => $total,
            'completed_submissions' => $completed,
            'avg_nps' => $avgNps,
            'completion_rate' => $completionRate,
            'nps_score' => $npsScore,
            'promoters' => $promoters,
            'detractors' => $detractors,
            'neutrals' => $scored - $promoters - $detractors,
        ];
    }

    public function recentSubmissions(array $filters, int $limit = 12): array
    {
        $params = [];
        $whereSql = $this->buildWhereClause($filters, $params);

        $sql = 'SELECT
                    sub.id,
                    sub.trigger_event,
                    sub.score_nps,
                    sub.is_completed,
                    sub.source_url,
                    sub.user_identifier,
                    sub.session_identifier,
                    sub.created_at,
                    p.name AS project_name,
                    s.name AS survey_name
                FROM submissions sub
                INNER JOIN projects p ON p.id = sub.project_id
                INNER JOIN surveys s ON s.id = sub.survey_id
                ' . $whereSql . '
                ORDER BY sub.created_at DESC, sub.id DESC
                LIMIT :limit';

        $stmt = $this->connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    private function buildWhereClause(array $filters, array &$params): string
    {
        $where = [];

        $projectId = (int) ($filters['project_id'] ?? 0);
        if ($projectId > 0) {
            $where[] = 'sub.project_id = :project_id';
            $params['project_id'] = $projectId;
        }

        $triggerEvent = trim((string) ($filters['trigger_event'] ?? ''));
        if ($triggerEvent !== '') {
            $where[] = 'sub.trigger_event = :trigger_event';
            $params['trigger_event'] = $triggerEvent;
        }

        $fromDate = $this->normalizeDate((string) ($filters['from_date'] ?? ''));
        if ($fromDate !== null) {
            $where[] = 'sub.created_at >= :from_date';
            $params['from_date'] = $fromDate . ' 00:00:00';
        }

        $toDate = $this->normalizeDate((string) ($filters['to_date'] ?? ''));
        if ($toDate !== null) {
            $where[] = 'sub.created_at <= :to_date';
            $params['to_date'] = $toDate . ' 23:59:59';
        }

        if ($where === []) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $where);
    }

    private function normalizeDate(string $dateValue): ?string
    {
        $candidate = trim($dateValue);
        if ($candidate === '') {
            return null;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $candidate)) {
            return null;
        }

        return $candidate;
    }

    private function connection(): PDO
    {
        return Database::connection();
    }
}
