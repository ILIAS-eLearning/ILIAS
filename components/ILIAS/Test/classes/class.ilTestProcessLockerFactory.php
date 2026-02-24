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
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Test\Logging\TestLogger;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestProcessLockerFactory
{
    protected ?int $context_id;

    public function __construct(
        private readonly ilSetting $settings,
        private readonly ilDBInterface $db,
        private readonly ilLogger|TestLogger $logger
    ) {
    }

    public function getContextId(): ?int
    {
        return $this->context_id;
    }

    public function withContextId(int $contextId): self
    {
        $clone = clone $this;
        $clone->context_id = $contextId;

        return $clone;
    }

    private function getLockModeSettingValue(): ?string
    {
        return $this->settings->get('ass_process_lock_mode', ilObjTestFolder::ASS_PROC_LOCK_MODE_NONE);
    }

    public function getLocker(): ilTestProcessLocker
    {
        switch ($this->getLockModeSettingValue()) {
            case ilObjTestFolder::ASS_PROC_LOCK_MODE_NONE:
                return new ilTestProcessLockerNone();

            case ilObjTestFolder::ASS_PROC_LOCK_MODE_FILE:
                $storage = new ilTestProcessLockFileStorage((int) $this->getContextId());
                $storage->create();
                return new ilTestProcessLockerFile($storage, $this->logger);

            case ilObjTestFolder::ASS_PROC_LOCK_MODE_DB:
                return new ilTestProcessLockerDb($this->db);
        }
    }

    public function retrieveLockerForNamedOperation(): ilTestProcessLocker
    {
        if ($this->getLocker() instanceof ilTestProcessLockerFile) {
            return $this->getLocker();
        }

        return new ilTestProcessLockerNone();
    }
}
