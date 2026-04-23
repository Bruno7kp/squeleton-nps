<?php

declare(strict_types=1);

namespace App\Domain\Surveys;

use App\Infrastructure\Database;
use PDO;

final class TriggerEventLogRepository
{
    public function log(
        string $publicKey,
        string $triggerKey,
        ?string $sourceUrl,
        ?string $userIdentifier,
        ?int $matchedSurveyId,
        ?int $projectId = null
    ): void {
        $resolvedProjectId = $projectId ?? $this->findProjectIdByPublicKey($publicKey);

        $stmt = $this->connection()->prepare(
            'INSERT INTO trigger_event_logs (
                project_id, trigger_key, public_key, source_url, user_identifier, matched_survey_id
             ) VALUES (
                :project_id, :trigger_key, :public_key, :source_url, :user_identifier, :matched_survey_id
             )'
        );

        $stmt->execute([
            'project_id' => $resolvedProjectId,
            'trigger_key' => $triggerKey,
            'public_key' => $publicKey,
            'source_url' => $sourceUrl !== null && trim($sourceUrl) !== '' ? trim($sourceUrl) : null,
            'user_identifier' => $userIdentifier !== null && trim($userIdentifier) !== '' ? trim($userIdentifier) : null,
            'matched_survey_id' => $matchedSurveyId,
        ]);
    }

    private function findProjectIdByPublicKey(string $publicKey): ?int
    {
        $stmt = $this->connection()->prepare(
            'SELECT id
             FROM projects
             WHERE public_key = :public_key
             LIMIT 1'
        );

        $stmt->execute(['public_key' => $publicKey]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    private function connection(): PDO
    {
        return Database::connection();
    }
}
