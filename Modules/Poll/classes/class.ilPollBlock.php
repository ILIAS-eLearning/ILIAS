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
 ********************************************************************
 */

/**
 * Custom block for polls
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPollBlock extends ilCustomBlock
{
    protected ilLanguage $lng;
    protected ilObjPoll $poll;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        parent::__construct($a_id);
        $this->lng = $DIC->language();
    }

    /**
     * Set ref id (needed for poll access)
     */
    public function setRefId(int $a_id): void
    {
        $this->poll = new ilObjPoll($a_id, true);
    }

    public function getPoll(): ilObjPoll
    {
        return $this->poll;
    }
}
