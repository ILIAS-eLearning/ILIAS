<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

declare(strict_types=1);

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class UploadPolicyDBRepository
{
    protected const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
    protected const MYSQL_DATE_FORMAT = 'Y-m-d';

    public function __construct(protected readonly ilDBInterface $db)
    {
    }

    public function store(UploadPolicy $policy): void
    {
        $data_for_storage = [
            "title" => ['text', $policy->getTitle()],
            "upload_limit_in_mb" => ['integer', $policy->getUploadLimitInMB()],
            "audience" => ['text', json_encode($policy->getAudience(), JSON_THROW_ON_ERROR)],
            "audience_type" => ['integer', $policy->getAudienceType()],
            "scope_definition" => ['text', $policy->getScopeDefinition()],
            "active" => ['integer', $policy->isActive()],
            "valid_from" => ['date', (null !== ($from = $policy->getValidFrom())) ? $this->getDateString($from) : null],
            "valid_until" => [
                'date',
                (null !== ($until = $policy->getValidUntil())) ? $this->getDateString($until) : null
            ],
            "owner" => ['integer', $policy->getOwnerId()],
            "last_update" => ['timestamp', $this->getDateTimeString($policy->getLastUpdate())]
        ];

        if (null !== ($policy_id = $policy->getPolicyId()) && null !== $this->get($policy_id)) {
            // UPDATE
            $this->db->update(
                "il_upload_policy",
                $data_for_storage,
                ["policy_id" => ['integer', $policy_id]]
            );
        } else {
            // CREATE
            $data_for_storage["policy_id"] = ['integer', $this->db->nextId("il_upload_policy")];
            $data_for_storage["create_date"] = ['timestamp', $this->getDateTimeString($policy->getCreateDate())];

            $this->db->insert("il_upload_policy", $data_for_storage);
        }
    }

    public function get(int $policy_id): ?UploadPolicy
    {
        $query = "SELECT * FROM il_upload_policy WHERE policy_id = %s";
        $result = $this->db->queryF($query, ['integer'], [$policy_id]);
        if (null !== ($dataset = $this->db->fetchObject($result))) {
            return $this->transformToDtoOrAbort($dataset);
        }

        return null;
    }

    /**
     * @return UploadPolicy[]
     */
    public function getAll(): array
    {
        $query = "SELECT * FROM il_upload_policy";
        $result = $this->db->query($query);

        $upload_policies = [];
        while (null !== ($dataset = $this->db->fetchObject($result))) {
            $upload_policies[] = $this->transformToDtoOrAbort($dataset);
        }

        return $upload_policies;
    }

    public function delete(UploadPolicy $policy): void
    {
        if (null === ($policy_id = $policy->getPolicyId())) {
            return;
        }

        $query = "DELETE FROM il_upload_policy WHERE policy_id = %s";
        $this->db->manipulateF($query, ['integer'], [$policy_id]);
    }

    /**
     * @throws LogicException if required data is missing
     */
    protected function transformToDtoOrAbort(stdClass $dataset): UploadPolicy
    {
        $audience_json = $dataset?->audience ?? $this->missingRequiredField('audience');

        $valid_from = (null !== ($from = $dataset?->valid_from)) ? $this->getDateObject((string) $from) : null;
        $valid_until = (null !== ($until = $dataset?->valid_until)) ? $this->getDateObject((string) $until) : null;

        return new UploadPolicy(
            $dataset?->policy_id ?? $this->missingRequiredField('policy_id'),
            $dataset?->title ?? $this->missingRequiredField('title'),
            $dataset->upload_limit_in_mb ?? $this->missingRequiredField('upload_limit_in_mb'),
            json_decode((string) $audience_json, true, 512, JSON_THROW_ON_ERROR),
            $dataset?->audience_type ?? $this->missingRequiredField('audience_type'),
            $dataset?->scope_definition ?? $this->missingRequiredField('scope_definition'),
            (bool) ($dataset?->active ?? $this->missingRequiredField('active')),
            $valid_from,
            $valid_until,
            $dataset?->owner ?? $this->missingRequiredField('owner'),
            $this->getDateTimeObject(
                (string) ($dataset?->create_date ?? $this->missingRequiredField('create_date'))
            ),
            $this->getDateTimeObject(
                (string) ($dataset?->last_update ?? $this->missingRequiredField('last_update'))
            )
        );
    }

    protected function getDateString(DateTimeImmutable $date): string
    {
        return $date->format(self::MYSQL_DATE_FORMAT);
    }

    /**
     * Returns a datetime object with '00:00:00' as H:i:s to avoid comparison
     * errors because PHP will use the current time automatically otherwise.
     */
    protected function getDateObject(string $date_string): DateTimeImmutable
    {
        return
            (DateTimeImmutable::createFromFormat(self::MYSQL_DATETIME_FORMAT, $date_string . ' 00:00:00')) ?:
                throw new LogicException("Could not create DateTimeImmutable from '$date_string'.");
    }

    protected function getDateTimeString(DateTimeImmutable $date_time): string
    {
        return $date_time->format(self::MYSQL_DATETIME_FORMAT);
    }

    protected function getDateTimeObject(string $date_time_string): DateTimeImmutable
    {
        return
            (DateTimeImmutable::createFromFormat(self::MYSQL_DATETIME_FORMAT, $date_time_string)) ?:
                throw new LogicException("Could not create DateTimeImmutable from '$date_time_string'.");
    }

    /**
     * @throws LogicException
     */
    protected function missingRequiredField(string $field_name): void
    {
        throw new LogicException("Could not retrieve data for required field '$field_name'.");
    }
}
