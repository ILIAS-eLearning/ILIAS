<?php declare(strict_types=1);

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
 ********************************************************************
 */

/**
 * Session participation handling.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesSession
 */
class ilSessionParticipants extends ilParticipants
{
    public const COMPONENT_NAME = 'Modules/Session';
    
    protected static array $instances = [];

    protected ilEventParticipants $event_part;

    public function __construct(int $a_ref_id)
    {
        $this->event_part = new ilEventParticipants(ilObject::_lookupObjId($a_ref_id));
        parent::__construct(self::COMPONENT_NAME, $a_ref_id);
    }

    public static function _getInstanceByObjId(int $a_obj_id) : ilSessionParticipants
    {
        $refs = ilObject::_getAllReferences($a_obj_id);
        return self::getInstance(array_pop($refs));
    }

    public static function getInstance(int $a_ref_id) : ilSessionParticipants
    {
        if (isset(self::$instances[$a_ref_id]) && self::$instances[$a_ref_id] instanceof self) {
            return self::$instances[$a_ref_id];
        }
        return self::$instances[$a_ref_id] = new self($a_ref_id);
    }

    public function getEventParticipants() : ilEventParticipants
    {
        return $this->event_part;
    }
    
    /**
     * no last admin restrictions for sessions
     * @param int[] $a_usr_ids
     */
    public function checkLastAdmin(array $a_usr_ids) : bool
    {
        return false;
    }

    public static function _isParticipant(int $a_ref_id, int $a_usr_id) : bool
    {
        $obj_id = ilObject::_lookupObjId($a_ref_id);
        return ilEventParticipants::_isRegistered($a_usr_id, $obj_id);
    }

    protected function readParticipantsStatus() : void
    {
        $this->participants_status = [];
        foreach ($this->getMembers() as $mem_uid) {
            $this->participants_status[$mem_uid]['blocked'] = false;
            $this->participants_status[$mem_uid]['notification'] = false;
            $this->participants_status[$mem_uid]['passed'] = false;
            $this->participants_status[$mem_uid]['contact'] = $this->getEventParticipants()->isContact($mem_uid);
        }
    }
    
    /**
     * Add user to session member role. Additionally the status registered or participated must be set manually
     */
    public function add(int $a_usr_id, int $a_role = 0) : bool
    {
        if (parent::add($a_usr_id, $a_role)) {
            return true;
        }
        return false;
    }

    public function register(int $a_usr_id) : bool
    {
        $this->logger->debug('Registering user: ' . $a_usr_id . ' for session: ' . $this->getObjId());
        $this->add($a_usr_id, ilParticipants::IL_SESS_MEMBER);
        // in any (already participant since status attended) case register user.
        $this->getEventParticipants()->register($a_usr_id);
        return true;
    }

    public function unregister(int $a_usr_id) : bool
    {
        // participated users are not dropped from role
        if (!$this->getEventParticipants()->hasParticipated($a_usr_id)) {
            $this->delete($a_usr_id);
        }
        $this->getEventParticipants()->unregister($a_usr_id);
        return true;
    }

    public function sendNotification(int $a_type, int $a_usr_id, bool $a_force_email = false) : void
    {
        $mail = new ilSessionMembershipMailNotification();

        switch ($a_type) {
            case ilSessionMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER:
                $mail->setType(ilSessionMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients([$a_usr_id]);
                $mail->send();
                break;

            default:
                $this->logger->warning('Invalid notfication type given: ' . $a_type);
                $this->logger->logStack(ilLogLevel::WARNING);
                break;
        }
    }
}
