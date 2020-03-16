<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/classes/class.ilAbstractMailMemberRoles.php';

/**
 * Class ilMailMemberSessionRoles
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilMailMemberSessionRoles extends ilAbstractMailMemberRoles
{
    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilRbacReview
     */
    protected $rbacreview;

    /**
     * ilMailMemberSessionRoles constructor.
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
        return $this->lng->txt('mail_sess_roles');
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
            // mailbox addresses are not supported in general since title of object might be empty
            $mailbox = $this->lng->txt('il_sess_participant') . ' <#' . $role_title . '>';

            switch (substr($role_title, 0, 12)) {
                case 'il_sess_part':
                    $sorted_role_ids[1]['default_checked'] = true;
                    $sorted_role_ids[1]['role_id'] = $role_id;
                    $sorted_role_ids[1]['mailbox'] = $mailbox;
                    $sorted_role_ids[1]['form_option_title'] = $this->lng->txt('send_mail_participants');
                    break;

            }
        }
        ksort($sorted_role_ids, SORT_NUMERIC);

        return $sorted_role_ids;
    }
}
