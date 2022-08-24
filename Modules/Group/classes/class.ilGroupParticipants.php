<?php

declare(strict_types=1);

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
*
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @ingroup ModulesGroup
*/


class ilGroupParticipants extends ilParticipants
{
    protected const COMPONENT_NAME = 'Modules/Group';

    protected static array $instances = [];

    /**
     * Constructor
     *
     * @access protected
     * @param int obj_id of container
     */
    public function __construct(int $a_obj_id)
    {
        global $DIC;

        $this->logger = $DIC->logger()->grp();

        // ref based constructor
        $refs = ilObject::_getAllReferences($a_obj_id);
        parent::__construct(self::COMPONENT_NAME, array_pop($refs));
    }

    /**
     * Get singleton instance
     */
    public static function _getInstanceByObjId(int $a_obj_id): ilGroupParticipants
    {
        if (isset(self::$instances[$a_obj_id]) && self::$instances[$a_obj_id]) {
            return self::$instances[$a_obj_id];
        }
        return self::$instances[$a_obj_id] = new ilGroupParticipants($a_obj_id);
    }

    /**
     * Get member roles (not auto generated)
     */
    public static function getMemberRoles(int $a_ref_id): array
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        $lrol = $rbacreview->getRolesOfRoleFolder($a_ref_id, false);

        $roles = array();
        foreach ($lrol as $role) {
            $title = ilObject::_lookupTitle($role);
            switch (substr($title, 0, 8)) {
                case 'il_grp_a':
                case 'il_grp_m':
                    continue 2;

                default:
                    $roles[$role] = $role;
            }
        }
        return $roles;
    }

    public function add(int $a_usr_id, int $a_role): bool
    {
        if (parent::add($a_usr_id, $a_role)) {
            $this->addRecommendation($a_usr_id);
            return true;
        }
        return false;
    }

    public function addSubscriber(int $a_usr_id): void
    {
        parent::addSubscriber($a_usr_id);

        $this->logger->info('Raise new event: Modules/Group addSubscriber.');
        $this->eventHandler->raise(
            "Modules/Group",
            'addSubscriber',
            array(
                    'obj_id' => $this->getObjId(),
                    'usr_id' => $a_usr_id
                )
        );
    }



    /**
     * Static function to check if a user is a participant of the container object
     */
    public static function _isParticipant(int $a_ref_id, int $a_usr_id): bool
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        $local_roles = $rbacreview->getRolesOfRoleFolder($a_ref_id, false);
        return $rbacreview->isAssignedToAtLeastOneGivenRole($a_usr_id, $local_roles);
    }

    public function sendNotification(int $a_type, int $a_usr_id, bool $a_force_sending_mail = false): void
    {
        $mail = new ilGroupMembershipMailNotification();
        $mail->forceSendingMail($a_force_sending_mail);

        switch ($a_type) {
            case ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER:

                $mail->setType(ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilGroupMembershipMailNotification::TYPE_DISMISS_MEMBER:

                $mail->setType(ilGroupMembershipMailNotification::TYPE_DISMISS_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilGroupMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION:

                $mail->setType(ilGroupMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION);
                $mail->setAdditionalInformation(array('usr_id' => $a_usr_id));
                $mail->setRefId($this->ref_id);
                $mail->setRecipients($this->getNotificationRecipients());
                $mail->send();
                break;

            case ilGroupMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER:

                $mail->setType(ilGroupMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilGroupMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE:

                $mail->setType(ilGroupMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE);
                $mail->setAdditionalInformation(array('usr_id' => $a_usr_id));
                $mail->setRefId($this->ref_id);
                $mail->setRecipients($this->getNotificationRecipients());
                $mail->send();
                break;

            case ilGroupMembershipMailNotification::TYPE_SUBSCRIBE_MEMBER:

                $mail->setType(ilGroupMembershipMailNotification::TYPE_SUBSCRIBE_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilGroupMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION_REQUEST:

                $mail->setType(ilGroupMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION_REQUEST);
                $mail->setAdditionalInformation(array('usr_id' => $a_usr_id));
                $mail->setRefId($this->ref_id);
                $mail->setRecipients($this->getNotificationRecipients());
                $mail->send();
                break;

            case ilGroupMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER:

                $mail->setType(ilGroupMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER:

                $mail->setType(ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilGroupMembershipMailNotification::TYPE_WAITING_LIST_MEMBER:

                $wl = new ilGroupWaitingList($this->obj_id);
                $pos = $wl->getPosition($a_usr_id);

                $mail->setType(ilGroupMembershipMailNotification::TYPE_WAITING_LIST_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->setAdditionalInformation(array('position' => $pos));
                $mail->send();
                break;

            case ilGroupMembershipMailNotification::TYPE_STATUS_CHANGED:

                $mail->setType(ilGroupMembershipMailNotification::TYPE_STATUS_CHANGED);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;


        }
    }
}
