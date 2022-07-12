<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    public function getMembers(bool $a_search = true) : array
    {
        if (!$a_search) {
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
    public function getDefaultMode() : int
    {
        return \ilLPObjSettings::LP_MODE_DEACTIVATED;
    }

    /**
     * @param bool $a_lp_active
     * @return array
     */
    public static function getDefaultModes(bool $a_lp_active) : array
    {
        return [
            \ilLPObjSettings::LP_MODE_DEACTIVATED,
            \ilLPObjSettings::LP_MODE_COURSE_REFERENCE
        ];
    }

    /**
     * @inheritdoc
     */
    public function getValidModes() : array
    {
        return self::getDefaultModes(true);
    }
}
