<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/classes/class.ilAbstractMailMemberRoles.php';

/**
 * Class ilMailMemberCourseRoles
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailMemberGroupRoles extends ilAbstractMailMemberRoles
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
     * ilMailMemberGroupRoles constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC['lng'];
        $this->rbacreview = $DIC['rbacreview'];
    }

    /**
     * @return string
     */
    public function getRadioOptionTitle()
    {
        return $this->lng->txt('mail_grp_roles');
    }

    /**
     * @param $ref_id
     * @return array sorted_roles
     */
    public function getMailRoles($ref_id)
    {
        $role_ids = $this->rbacreview->getLocalRoles($ref_id);

        $sorted_role_ids = array();
        $counter = 2;

        foreach ($role_ids as $role_id) {
            $role_title = ilObject::_lookupTitle($role_id);
            $mailbox = $this->getMailboxRoleAddress($role_id);

            switch (substr($role_title, 0, 8)) {
                case 'il_grp_a':
                    $sorted_role_ids[1]['role_id'] = $role_id;
                    $sorted_role_ids[1]['mailbox'] = $mailbox;
                    $sorted_role_ids[1]['form_option_title'] = $this->lng->txt('send_mail_admins');
                    break;

                case 'il_grp_m':
                    $sorted_role_ids[0]['role_id'] = $role_id;
                    $sorted_role_ids[0]['mailbox'] = $mailbox;
                    $sorted_role_ids[0]['form_option_title'] = $this->lng->txt('send_mail_members');
                    break;

                default:
                    $sorted_role_ids[$counter]['role_id'] = $role_id;
                    $sorted_role_ids[$counter]['mailbox'] = $mailbox;
                    $sorted_role_ids[$counter]['form_option_title'] = $role_title;

                    $counter++;
                    break;
            }
        }
        ksort($sorted_role_ids, SORT_NUMERIC);

        return $sorted_role_ids;
    }
}
