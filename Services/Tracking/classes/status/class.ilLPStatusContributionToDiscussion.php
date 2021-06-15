<?php declare(strict_types = 1);

/**
 * Class ilLPStatusContributionToDiscussion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilLPStatusContributionToDiscussion extends ilLPStatus
{
    public static function _getCompleted($a_obj_id)
    {
        $userIds = [];

        return $userIds;
    }

    public static function _getInProgress($a_obj_id)
    {
        $userIds = [];

        return $userIds;
    }

    public function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
    {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;

        return $status;
    }
}
