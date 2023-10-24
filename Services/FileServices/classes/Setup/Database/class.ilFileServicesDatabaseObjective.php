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
class ilFileServicesDatabaseObjective implements ilDatabaseUpdateSteps
{
    protected ?ilDBInterface $database = null;

    public function prepare(ilDBInterface $db): void
    {
        $this->database = $db;
    }

    /**
     * adds a new table to store data of file upload policies
     */
    public function step_1(): void
    {
        $this->abortIfNotPrepared();

        if ($this->database->tableExists("il_upload_policy")) {
            return;
        }

        $this->database->createTable("il_upload_policy", [
            "policy_id" => [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
            ],
            "title" => [
                'type' => 'text',
                'length' => 256,
                'notnull' => true,
            ],
            "upload_limit_in_mb" => [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
            ],
            "audience" => [
                'type' => 'text',
                'length' => 512,
                'notnull' => true,
            ],
            "audience_type" => [
                'type' => 'integer',
                'length' => 2,
                'notnull' => true,
            ],
            "scope_definition" => [
                'type' => 'text',
                'length' => 512,
                'notnull' => true,
            ],
            "active" => [
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
            ],
            "valid_from" => [
                'type' => 'date',
                'notnull' => false,
            ],
            "valid_until" => [
                'type' => 'date',
                'notnull' => false,
            ],
            "owner" => [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
            ],
            "create_date" => [
                'type' => 'timestamp',
                'notnull' => true,
            ],
            "last_update" => [
                'type' => 'timestamp',
                'notnull' => true,
            ]
        ]);

        $this->database->createSequence("il_upload_policy");
    }

    /**
     * @throws LogicException if the database update steps were not
     *                        yet prepared.
     */
    protected function abortIfNotPrepared(): void
    {
        if (null === $this->database) {
            throw new LogicException(self::class . "::prepare() must be called before db-update-steps execution.");
        }
    }
}
