<?php declare(strict_types=1);

/**
 * Class ilLPStatusContributionToDiscussion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilLPStatusContributionToDiscussion extends ilLPStatus
{
    public static function _getCompleted($a_obj_id)
    {
        $userIds = [];

        $frm_properties = ilForumProperties::getInstance($a_obj_id);
        $num_required_postings = $frm_properties->getLpReqNumPostings();

        if (null === $num_required_postings) {
            return $userIds;
        }

        /**
         * TODO:
         * 1. Read threshold setting (number of required postings) by $a_obj_id
         * 2. Determine all users, where the amount of created postings (do NOT count root postings) is >= this threshold
         * Maybe ask mkunkel: only active postings (WHERE status = 1)?
         */

        return $userIds;
    }

    public static function _getInProgress($a_obj_id)
    {
        $userIds = [];

        $frm_properties = ilForumProperties::getInstance($a_obj_id);
        $num_required_postings = $frm_properties->getLpReqNumPostings();

        if (null === $num_required_postings) {
            return $userIds;
        }

        /**
         * TODO:
         * 1. Read threshold setting (number of required postings) by $a_obj_id
         * 2. Determine all users, where the amount of created postings (do NOT count root postings) is < this threshold but > 1
         * Maybe ask mkunkel: only active postings (WHERE status = 1)?
         */

        return $userIds;
    }

    public function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
    {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;

        $frm_properties = ilForumProperties::getInstance($a_obj_id);
        $num_required_postings = $frm_properties->getLpReqNumPostings();

        if (null === $num_required_postings) {
            return $status;
        }

        /**
         * TODO:
         * 1. Read threshold setting (number of required postings) by $a_obj_id
         * 2. Determine the number of postings (do NOT count root postings) for $a_user_id in $a_obj_id
         * Maybe ask mkunkel: only active postings (WHERE status = 1)?
         * 3. If $number > 1, then $status = self::LP_STATUS_IN_PROGRESS_NUM, if $number >= $threshold
         * $status = self::LP_STATUS_COMPLETED_NUM
         */

        return $status;
    }
}
