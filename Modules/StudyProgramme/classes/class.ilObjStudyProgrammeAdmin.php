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
 * Class ilObjStudyProgrammeAdmin
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @author  Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjStudyProgrammeAdmin extends ilObject2
{
    public const SETTING_VISIBLE_ON_PD = "prgs_visible_on_personal_desktop";
    public const SETTING_VISIBLE_ON_PD_ALLWAYS = "allways";
    public const SETTING_VISIBLE_ON_PD_READ = "read";

    public function __construct(int $id = 0, bool $call_by_reference = true)
    {
        parent::__construct($id, $call_by_reference);
    }

    protected function initType(): void
    {
        $this->type = "prgs";
    }
}
