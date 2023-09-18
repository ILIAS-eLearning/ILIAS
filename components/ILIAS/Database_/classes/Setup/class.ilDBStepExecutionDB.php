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
 * This logs the execution of database update steps.
 *
 * @author: Richard Klees
 */
class ilDBStepExecutionDB implements ilDatabaseUpdateStepExecutionLog
{
    public const TABLE_NAME = "il_db_steps";

    public const FIELD_CLASS = "class";
    public const FIELD_STEP = "step";
    public const FIELD_STARTED = "started";
    public const FIELD_FINISHED = "finished";

    protected ilDBInterface $db;
    protected $get_now;

    /**
     * @param   callable $get_now must return a DateTime object indicating the
     *                            very moment the callable was called.
     */
    public function __construct(ilDBInterface $db, callable $get_now)
    {
        $this->db = $db;
        $this->get_now = $get_now;
    }

    /**
     * @throws \LogicException	if the previously started step has not finished
     */
    public function started(string $class, int $step): void
    {
        $this->throwIfClassNameTooLong($class);

        $last_started_step = $this->getLastStartedStep($class);
        if ($last_started_step >= $step) {
            throw new \RuntimeException(
                "The last started step for $class was $last_started_step, which" .
                " is higher then the step $step started now."
            );
        }

        $last_finished_step = $this->getLastFinishedStep($class);
        if ($last_started_step !== $last_finished_step) {
            throw new \RuntimeException(
                "Step $step should be started for $class, but last step $last_started_step " .
                "has not finished by now."
            );
        }

        $this->db->insert(
            self::TABLE_NAME,
            [
                self::FIELD_CLASS => ["text", $class],
                self::FIELD_STEP => ["integer", $step],
                self::FIELD_STARTED => ["text", $this->getFormattedNow()]
            ]
        );
    }

    /**
     * @throws \LogicException	if the finished step does not match the previously started step
     */
    public function finished(string $class, int $step): void
    {
        $this->throwIfClassNameTooLong($class);

        $last_started_step = $this->getLastStartedStep($class);
        if ($last_started_step != $step) {
            throw new \RuntimeException(
                "The step $step for $class is supposed to be finished, but" .
                " $last_started_step was $step started last."
            );
        }

        $this->db->update(
            self::TABLE_NAME,
            [
                self::FIELD_FINISHED => ["text", $this->getFormattedNow()]
            ],
            [
                self::FIELD_CLASS => ["text", $class],
                self::FIELD_STEP => ["integer", $step]
            ]
        );
    }

    public function getLastStartedStep(string $class): int
    {
        $this->throwIfClassNameTooLong($class);

        $res = $this->db->query(
            "SELECT MAX(" . self::FIELD_STEP . ") AS " . self::FIELD_STEP .
            " FROM " . self::TABLE_NAME .
            " WHERE " . self::FIELD_CLASS . " = " . $this->db->quote($class, "text")
        );

        $row = $this->db->fetchAssoc($res);
        return (int) ($row[self::FIELD_STEP] ?? 0);
    }

    public function getLastFinishedStep(string $class): int
    {
        $this->throwIfClassNameTooLong($class);

        $res = $this->db->query(
            "SELECT MAX(" . self::FIELD_STEP . ") AS " . self::FIELD_STEP .
            " FROM " . self::TABLE_NAME .
            " WHERE " . self::FIELD_CLASS . " = " . $this->db->quote($class, "text") .
            " AND " . self::FIELD_FINISHED . " IS NOT NULL"
        );

        $row = $this->db->fetchAssoc($res);
        return (int) ($row[self::FIELD_STEP] ?? 0);
    }

    protected function throwIfClassNameTooLong(string $class): void
    {
        if (strlen($class) > 200) {
            throw new \InvalidArgumentException(
                "This ilDatabaseUpdateStepExecutionLog only supports class names up to 200 chars."
            );
        }
    }

    protected function getFormattedNow(): string
    {
        $now = ($this->get_now)();
        if (!($now instanceof \DateTime)) {
            throw new \LogicException(
                "Expected \$get_now to return a DateTime."
            );
        }
        return $now->format("Y-m-d H:i:s.u");
    }
}
