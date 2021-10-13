<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMemberSessionRoles
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilMailMemberSessionRoles extends ilAbstractMailMemberRoles
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
        return $this->lng->txt('mail_sess_roles');
    }

    public function getMailRoles(int $ref_id) : array
    {
        $role_ids = $this->rbacreview->getLocalRoles($ref_id);

        $sorted_role_ids = [];
        $counter = 2;

        foreach ($role_ids as $role_id) {
            $role_title = ilObject::_lookupTitle($role_id);
            // mailbox addresses are not supported in general since title of object might be empty
            $mailbox = $this->lng->txt('il_sess_participant') . ' <#' . $role_title . '>';

            $role_prefix = substr($role_title, 0, 12);
            if ($role_prefix === 'il_sess_part') {
                $sorted_role_ids[1]['default_checked'] = true;
                $sorted_role_ids[1]['role_id'] = $role_id;
                $sorted_role_ids[1]['mailbox'] = $mailbox;
                $sorted_role_ids[1]['form_option_title'] = $this->lng->txt('send_mail_participants');
            }
        }
        ksort($sorted_role_ids, SORT_NUMERIC);

        return $sorted_role_ids;
    }
}
