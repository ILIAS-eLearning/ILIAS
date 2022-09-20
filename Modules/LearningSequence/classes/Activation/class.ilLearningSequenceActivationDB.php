<?php

declare(strict_types=1);

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
 *
 *********************************************************************/

/**
 * Persistence for online/activation period
 */
class ilLearningSequenceActivationDB
{
    public const TABLE_NAME = 'lso_activation';

    protected ilDBInterface $database;

    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    public function getActivationForRefId(int $ref_id): ilLearningSequenceActivation
    {
        $data = $this->select($ref_id);
        if (count($data) == 0) {
            $settings = $this->buildActivationSettings($ref_id);
            $this->insert($settings);
        } else {
            if ($data['activation_start_ts']) {
                $start = new \DateTime();
                $start->setTimestamp((int) $data['activation_start_ts']);
            } else {
                $start = null;
            }

            if ($data['activation_end_ts']) {
                $end = new \DateTime();
                $end->setTimestamp((int) $data['activation_end_ts']);
            } else {
                $end = null;
            }

            $settings = $this->buildActivationSettings(
                (int) $data['ref_id'],
                (bool) $data['online'],
                (bool) $data['effective_online'],
                $start,
                $end
            );
        }

        return $settings;
    }

    public function deleteForRefId(int $ref_id): void
    {
        $query = "DELETE FROM " . static::TABLE_NAME . PHP_EOL
            . "WHERE ref_id = " . $this->database->quote($ref_id, "integer") . PHP_EOL
        ;
        $this->database->manipulate($query);
    }

    public function store(ilLearningSequenceActivation $settings): void
    {
        $where = [
            "ref_id" => ["integer", $settings->getRefId()]
        ];

        $start = $settings->getActivationStart();
        $end = $settings->getActivationEnd();

        if ($start) {
            $start = $start->getTimestamp();
        }

        if ($end) {
            $end = $end->getTimestamp();
        }

        $values = [
            "online" => ["integer", $settings->getIsOnline()],
            "activation_start_ts" => ["integer", $start],
            "activation_end_ts" => ["integer", $end]
        ];

        $this->database->update(static::TABLE_NAME, $values, $where);
    }

    protected function insert(ilLearningSequenceActivation $settings): void
    {
        $start = $settings->getActivationStart();
        $end = $settings->getActivationEnd();

        if ($start) {
            $start = $start->getTimestamp();
        }

        if ($end) {
            $end = $end->getTimestamp();
        }

        $values = [
            "ref_id" => ["integer", $settings->getRefId()],
            "online" => ["integer", $settings->getIsOnline()],
            "effective_online" => ["integer", $settings->getEffectiveOnlineStatus()],
            "activation_start_ts" => ["integer", $start],
            "activation_end_ts" => ["integer", $end]
        ];

        $this->database->insert(static::TABLE_NAME, $values);
    }

    /**
     * @return string[]
     */
    protected function select(int $ref_id): array
    {
        $ret = [];
        $query =
             "SELECT ref_id, online, effective_online, activation_start_ts, activation_end_ts" . PHP_EOL
            . "FROM " . static::TABLE_NAME . PHP_EOL
            . "WHERE ref_id = " . $this->database->quote($ref_id, "integer") . PHP_EOL
        ;

        $result = $this->database->query($query);

        if ($this->database->numRows($result) !== 0) {
            $ret = $this->database->fetchAssoc($result);
        }

        return $ret;
    }

    protected function buildActivationSettings(
        int $ref_id,
        bool $online = false,
        bool $effective_online = false,
        \DateTime $activation_start = null,
        \DateTime $activation_end = null
    ): ilLearningSequenceActivation {
        return new ilLearningSequenceActivation(
            $ref_id,
            $online,
            $effective_online,
            $activation_start,
            $activation_end
        );
    }

    public function setEffectiveOnlineStatus(int $ref_id, bool $status): void
    {
        $where = ["ref_id" => ["integer", $ref_id]];
        $values = ["effective_online" => ["integer", $status]];

        $this->database->update(static::TABLE_NAME, $values, $where);
    }
}
