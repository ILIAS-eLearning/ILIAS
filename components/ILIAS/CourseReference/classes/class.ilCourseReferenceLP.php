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

/**
 * Class ilCourseReferenceLP
 */
class ilCourseReferenceLP extends ilObjectLP
{
    /**
     * @var \ilLogger | null
     */
    private $logger = null;

    protected function __construct($a_obj_id)
    {
        global $DIC;

        parent::__construct($a_obj_id);

        $this->logger = $DIC->logger()->crsr();
    }

    /**
     * @param bool $a_search
     * @return array
     */
    public function getMembers(bool $search = true): array
    {
        if (!$search) {
            return [];
        }
        $target_ref_id = \ilObjCourseReference::_lookupTargetRefId($this->obj_id);
        if (!$target_ref_id) {
            return [];
        }
        $participants = \ilParticipants::getInstance($target_ref_id);
        return $participants->getMembers();
    }


    /**
     * @inheritdoc
     */
    public function getDefaultMode(): int
    {
        return \ilLPObjSettings::LP_MODE_DEACTIVATED;
    }

    public static function getDefaultModes(bool $lp_active): array
    {
        return [\ilLPObjSettings::LP_MODE_DEACTIVATED];
    }

    /**
     * @inheritdoc
     */
    public function getValidModes(): array
    {
        return [
            \ilLPObjSettings::LP_MODE_DEACTIVATED,
            \ilLPObjSettings::LP_MODE_COURSE_REFERENCE
        ];
    }
}
