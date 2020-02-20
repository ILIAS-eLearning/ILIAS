<?php

declare(strict_types=1);

/**
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilMailMemberLearningSequenceRoles extends ilAbstractMailMemberRoles
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->rbacreview = $DIC->rbac()->review();
    }

    /**
     * @return string
     */
    public function getRadioOptionTitle()
    {
        return $this->lng->txt('mail_lso_roles');
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
                case 'il_lso_a':
                    $sorted_role_ids[1]['role_id'] = $role_id;
                    $sorted_role_ids[1]['mailbox'] = $mailbox;
                    $sorted_role_ids[1]['form_option_title'] = $this->lng->txt('send_mail_admins');
                    break;

                case 'il_lso_m':
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
