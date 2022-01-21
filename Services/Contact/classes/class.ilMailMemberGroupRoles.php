<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMemberCourseRoles
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailMemberGroupRoles extends ilAbstractMailMemberRoles
{
    protected ilLanguage $lng;
    protected ilRbacReview $rbacreview;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC['lng'];
        $this->rbacreview = $DIC['rbacreview'];
    }

    
    public function getRadioOptionTitle() : string
    {
        return $this->lng->txt('mail_grp_roles');
    }

    public function getMailRoles(int $ref_id) : array
    {
        $role_ids = $this->rbacreview->getLocalRoles($ref_id);

        $sorted_role_ids = [];
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
