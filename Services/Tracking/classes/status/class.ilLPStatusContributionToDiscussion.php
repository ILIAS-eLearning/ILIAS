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

        $frm = new ilForum();
        $frm->setForumId($frm_properties->getObjId());
        $statistics = $frm->getUserStatistics(false, true);

        return array_map(static function (array $statisic) : int {
            return (int) $statisic['pos_author_id'];
        }, array_filter($statistics, static function (array $statistic) use ($num_required_postings) : bool {
            return (int) $statistic['num_postings'] >= $num_required_postings;
        }));
    }

    public static function _getInProgress($a_obj_id)
    {
        $userIds = [];

        $frm_properties = ilForumProperties::getInstance($a_obj_id);
        $num_required_postings = $frm_properties->getLpReqNumPostings();

        if (null === $num_required_postings) {
            return $userIds;
        }

        $frm = new ilForum();
        $frm->setForumId($frm_properties->getObjId());
        $statistics = $frm->getUserStatistics(false, true);

        return array_map(static function (array $statisic) : int {
            return (int) $statisic['pos_author_id'];
        }, array_filter($statistics, static function (array $statistic) use ($num_required_postings) : bool {
            $num_user_postings = (int) $statistic['num_postings'];
            return $num_user_postings > 0 && $num_user_postings < $num_required_postings;
        }));
    }

    public function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
    {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;

        $frm_properties = ilForumProperties::getInstance($a_obj_id);
        $num_required_postings = $frm_properties->getLpReqNumPostings();

        if (null === $num_required_postings) {
            return $status;
        }

        $frm = new ilForum();
        $frm->setForumId($frm_properties->getObjId());

        $num_postings = $frm->getNumberOfPublishedUserPostings((int) $a_user_id);
        if ($num_postings >= $num_required_postings) {
            $status = self::LP_STATUS_COMPLETED_NUM;
        } elseif ($num_postings > 0) {
            $status = self::LP_STATUS_IN_PROGRESS_NUM;
        }

        return $status;
    }
}
