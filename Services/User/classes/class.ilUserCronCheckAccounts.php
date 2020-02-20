<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * This cron send notifications about expiring user accounts
 *
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @package ServicesUser
 */
class ilUserCronCheckAccounts extends ilCronJob
{
    protected $counter = 0; // [int]
    
    public function getId()
    {
        return "user_check_accounts";
    }
    
    public function getTitle()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        return $lng->txt("check_user_accounts");
    }
    
    public function getDescription()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        return $lng->txt("check_user_accounts_desc");
    }
    
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }
    
    public function getDefaultScheduleValue()
    {
        return;
    }
    
    public function hasAutoActivation()
    {
        return false;
    }
    
    public function hasFlexibleSchedule()
    {
        return false;
    }
    
    public function run()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        $lng = $DIC['lng'];
        
        $status = ilCronJobResult::STATUS_NO_ACTION;
        
        $now = time();
        $two_weeks_in_seconds = $now + (60 * 60 * 24 * 14); // #14630
            
        // all users who are currently active and expire in the next 2 weeks
        $query = "SELECT * FROM usr_data,usr_pref " .
            "WHERE time_limit_message = '0' " .
            "AND time_limit_unlimited = '0' " .
            "AND time_limit_from < " . $ilDB->quote($now, "integer") . " " .
            "AND time_limit_until > " . $ilDB->quote($now, "integer") . " " .
            "AND time_limit_until < " . $ilDB->quote($two_weeks_in_seconds, "integer") . " " .
            "AND usr_data.usr_id = usr_pref.usr_id " .
            "AND keyword = " . $ilDB->quote("language", "text");

        $res = $ilDB->query($query);

        /** @var ilMailMimeSenderFactory $senderFactory */
        $senderFactory = $GLOBALS['DIC']["mail.mime.sender.factory"];
        $sender        = $senderFactory->system();

        while ($row = $ilDB->fetchObject($res)) {
            include_once 'Services/Mail/classes/class.ilMimeMail.php';

            $data['expires'] = $row->time_limit_until;
            $data['email'] = $row->email;
            $data['login'] = $row->login;
            $data['usr_id'] = $row->usr_id;
            $data['language'] = $row->value;
            $data['owner'] = $row->time_limit_owner;

            // Send mail
            $mail = new ilMimeMail();
            
            $mail->From($sender);
            $mail->To($data['email']);
            $mail->Subject($this->txt($data['language'], 'account_expires_subject'), true);
            $mail->Body($this->txt($data['language'], 'account_expires_body') . " " . strftime('%Y-%m-%d %R', $data['expires']));
            $mail->send();

            // set status 'mail sent'
            $query = "UPDATE usr_data SET time_limit_message = '1' WHERE usr_id = '" . $data['usr_id'] . "'";
            $ilDB->query($query);
            
            // Send log message
            $ilLog->write('Cron: (checkUserAccounts()) sent message to ' . $data['login'] . '.');
            
            $this->counter++;
        }

        $this->checkNotConfirmedUserAccounts();
        
        if ($this->counter) {
            $status = ilCronJobResult::STATUS_OK;
        }
        $result = new ilCronJobResult();
        $result->setStatus($status);
        return $result;
    }
    
    // #13288 / #12345
    protected function txt($language, $key, $module = 'common')
    {
        include_once 'Services/Language/classes/class.ilLanguage.php';
        return ilLanguage::_lookupEntry($language, $module, $key);
    }
    
    protected function checkNotConfirmedUserAccounts()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        
        require_once 'Services/Registration/classes/class.ilRegistrationSettings.php';
        $oRegSettigs = new ilRegistrationSettings();
        
        $query = 'SELECT usr_id FROM usr_data '
               . 'WHERE reg_hash IS NOT NULL '
               . 'AND active = %s '
               . 'AND create_date < %s';
        $res = $ilDB->queryF(
            $query,
            array('integer', 'timestamp'),
            array(0, date('Y-m-d H:i:s', time() - (int) $oRegSettigs->getRegistrationHashLifetime()))
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            $oUser = ilObjectFactory::getInstanceByObjId((int) $row['usr_id']);
            $oUser->delete();
            $ilLog->write('Cron: Deleted ' . $oUser->getLogin() . ' [' . $oUser->getId() . '] ' . __METHOD__);
            
            $this->counter++;
        }
    }
}
