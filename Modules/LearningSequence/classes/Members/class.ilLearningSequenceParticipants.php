<?php

declare(strict_types=1);

/**
 * Manage participants.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de> (refactor to psr-12 as far as possible)
 */
class ilLearningSequenceParticipants extends ilParticipants
{
    const COMPONENT_NAME = 'Modules/LearningSequence';

    /**
     * @var ilLearningSequenceParticipants[]
     */
    protected static $instances;

    public function __construct(
        int $obj_id,
        ilLogger $logger,
        ilAppEventHandler $app_event_handler,
        ilSetting $settings
    ) {
        $refs = ilObject::_getAllReferences($obj_id);
        parent::__construct(self::COMPONENT_NAME, array_pop($refs));

        $this->logger = $logger;
        $this->app_event_handler = $app_event_handler;
        $this->settings = $settings;
    }

    public static function _getInstanceByObjId(int $obj_id) : ilLearningSequenceParticipants
    {
        global $DIC;

        $logger = $DIC["ilLoggerFactory"]->getRootLogger();
        $app_event_handler = $DIC['ilAppEventHandler'];
        $settings = $DIC["ilSetting"];

        if (isset(self::$instances[$obj_id]) and self::$instances[$obj_id]) {
            return self::$instances[$obj_id];
        }

        return self::$instances[$obj_id] = new ilLearningSequenceParticipants(
            $obj_id,
            $logger,
            $app_event_handler,
            $settings
        );
    }

    public static function getMemberRoles($ref_id) : array
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        $lrol = $rbacreview->getRolesOfRoleFolder($ref_id, false);

        $roles = array();
        foreach ($lrol as $role) {
            $title = ilObject::_lookupTitle($role);

            switch (substr($title, 0, 8)) {
                case 'il_lso_a':
                case 'il_lso_m':
                    $roles[$role] = $role;
                default:
                    break;
            }
        }

        return $roles;
    }

    public static function _isParticipant($ref_id, $usr_id) : bool
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        $local_roles = $rbacreview->getRolesOfRoleFolder($ref_id, false);

        return $rbacreview->isAssignedToAtLeastOneGivenRole($usr_id, $local_roles);
    }

    public function add($usr_id, $role) : bool
    {
        if (parent::add($usr_id, $role)) {
            $this->addDesktopItem($usr_id);
            return true;
        }

        return false;
    }

    public function addSubscriber($usr_id)
    {
        parent::addSubscriber($usr_id);

        $this->log->lso()->info('Raise new event: Modules/LearningSequence addSubscriber.');
        $this->app_event_handler->raise(
            "Modules/LearningSequence",
            'addSubscriber',
            array(
                'obj_id' => $this->getObjId(),
                'usr_id' => $usr_id
            )
        );
    }

    /**
     * Send notification mail.
     */
    public function sendNotification($type, $usr_id, $force_sending_mail = false) : bool
    {
        $mail = new ilLearningSequenceMembershipMailNotification($this->logger, $this->settings);
        $mail->forceSendingMail($force_sending_mail);

        switch ($type) {
            case ilLearningSequenceMembershipMailNotification::TYPE_ADMISSION_MEMBER:
                $mail->setType(ilLearningSequenceMembershipMailNotification::TYPE_ADMISSION_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($usr_id));
                $mail->send();
                break;
            case ilLearningSequenceMembershipMailNotification::TYPE_DISMISS_MEMBER:
                $mail->setType(ilLearningSequenceMembershipMailNotification::TYPE_DISMISS_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($usr_id));
                $mail->send();
                break;
            case ilLearningSequenceMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION:
                $mail->setType(ilLearningSequenceMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION);
                $mail->setAdditionalInformation(array('usr_id' => $usr_id));
                $mail->setRefId($this->ref_id);
                $mail->setRecipients($this->getNotificationRecipients());
                $mail->send();
                break;
            case ilLearningSequenceMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER:
                $mail->setType(ilLearningSequenceMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($usr_id));
                $mail->send();
                break;
            case ilLearningSequenceMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE:
                $mail->setType(ilLearningSequenceMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE);
                $mail->setAdditionalInformation(array('usr_id' => $usr_id));
                $mail->setRefId($this->ref_id);
                $mail->setRecipients($this->getNotificationRecipients());
                $mail->send();
                break;
            case ilLearningSequenceMembershipMailNotification::TYPE_SUBSCRIBE_MEMBER:
                $mail->setType(ilLearningSequenceMembershipMailNotification::TYPE_SUBSCRIBE_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($usr_id));
                $mail->send();
                break;
            case ilLearningSequenceMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION_REQUEST:
                $mail->setType(ilLearningSequenceMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION_REQUEST);
                $mail->setAdditionalInformation(array('usr_id' => $usr_id));
                $mail->setRefId($this->ref_id);
                $mail->setRecipients($this->getNotificationRecipients());
                $mail->send();
                break;
            case ilLearningSequenceMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER:
                $mail->setType(ilLearningSequenceMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($usr_id));
                $mail->send();
                break;
            case ilLearningSequenceMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER:
                $mail->setType(ilLearningSequenceMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($usr_id));
                $mail->send();
                break;
            case ilLearningSequenceMembershipMailNotification::TYPE_STATUS_CHANGED:
                $mail->setType(ilLearningSequenceMembershipMailNotification::TYPE_STATUS_CHANGED);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($usr_id));
                $mail->send();
                break;
        }
        return true;
    }
}
