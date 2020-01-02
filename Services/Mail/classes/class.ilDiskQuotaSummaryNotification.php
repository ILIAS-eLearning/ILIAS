<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Mail/classes/class.ilMailNotification.php';
include_once 'Services/WebDAV/classes/class.ilDiskQuotaChecker.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 *
 * @ingroup ServicesMembership
 */
class ilDiskQuotaSummaryNotification extends ilMailNotification
{
    /**
     * {@inheritdoc}
     */
    public function __construct($a_is_personal_workspace = false)
    {
        $dqs = new ilSetting('disk_quota');
        $rcpt = $dqs->get('summary_rcpt');
        $rcpt = explode(',', $rcpt);
        $loginnames = array();
        foreach ($rcpt as $loginname) {
            $loginname = trim($loginname);
            if (ilObjUser::_lookupId($loginname)) {
                $loginnames[] = $loginname;
            }
        }
        $this->setRecipients($loginnames);
        
        parent::__construct($a_is_personal_workspace);
    }
    
    /**
     *
     * Send notifications
     *
     * @access	public
     *
     */
    public function send()
    {
        global $ilDB;
        
        // parent::send();
        
        if (count($this->getRecipients())) {
            $res = $ilDB->queryf(
                "SELECT u.usr_id,u.gender,u.firstname,u.lastname,u.login,u.email,u.last_login,u.active," .
                    "u.time_limit_unlimited, " . $ilDB->fromUnixtime("u.time_limit_from") . ", " . $ilDB->fromUnixtime("u.time_limit_until") . "," .
    
                    // Inactive users get the date 0001-01-01 so that they appear
                    // first when the list is sorted by this field. Users with
                    // unlimited access get the date 9999-12-31 so that they appear
                    // last.
                    "CASE WHEN u.active = 0 THEN '0001-01-01' ELSE CASE WHEN u.time_limit_unlimited=1 THEN '9999-12-31' ELSE " . $ilDB->fromUnixtime("u.time_limit_until") . " END END access_until," .
    
                    " CASE WHEN " . $ilDB->unixTimestamp() . " BETWEEN u.time_limit_from AND u.time_limit_until THEN 0 ELSE 1 END expired," .
                    "rq.role_disk_quota, system_role.rol_id role_id, " .
                    "p1.value+0 user_disk_quota," .
                    "p2.value+0 disk_usage, " .
                    "p3.value last_update, " .
                    "p5.value language, " .
    
                    // We add 0 to some of the values to convert them into a number.
                    // This is needed for correct sorting.
                    "CASE WHEN rq.role_disk_quota>p1.value+0 OR p1.value IS NULL THEN rq.role_disk_quota ELSE p1.value+0 END disk_quota	" .
                "FROM usr_data u  " .
    
                // Fetch the role with the highest disk quota value.
                "JOIN (SELECT u.usr_id usr_id,MAX(rd.disk_quota) role_disk_quota " .
                    "FROM usr_data u " .
                    "JOIN rbac_ua ua ON ua.usr_id=u.usr_id " .
                    "JOIN rbac_fa fa ON fa.rol_id=ua.rol_id AND fa.parent=%s  " .
                    "JOIN role_data rd ON rd.role_id=ua.rol_id WHERE u.usr_id=ua.usr_id GROUP BY u.usr_id) rq ON rq.usr_id=u.usr_id " .
    
                // Fetch the system role in order to determine whether the user has unlimited disk quota
                "LEFT JOIN rbac_ua system_role ON system_role.usr_id=u.usr_id AND system_role.rol_id = %s " .
    
                // Fetch the user disk quota from table usr_pref
                "LEFT JOIN usr_pref p1 ON p1.usr_id=u.usr_id AND p1.keyword = 'disk_quota'  " .
    
                // Fetch the disk usage from table usr_pref
                "LEFT JOIN usr_pref p2 ON p2.usr_id=u.usr_id AND p2.keyword = 'disk_usage'  " .
    
                // Fetch the last update from table usr_pref
                "LEFT JOIN usr_pref p3 ON p3.usr_id=u.usr_id AND p3.keyword = 'disk_usage.last_update'  " .
    
                // Fetch the language of the user
                "LEFT JOIN usr_pref p5 ON p5.usr_id=u.usr_id AND p5.keyword = 'language'  " .
    
                // Fetch only users who have exceeded their quota, and who have
                // access, and who have not received a reminder in the past seven days
                // #8554 / #10301
                'WHERE (((p1.value+0 > rq.role_disk_quota OR rq.role_disk_quota IS NULL) AND p2.value+0 > p1.value+0) OR 
					((rq.role_disk_quota > p1.value+0 OR p1.value IS NULL) AND p2.value+0 > rq.role_disk_quota)) ' .
                'AND (u.active=1 AND (u.time_limit_unlimited = 1 OR ' . $ilDB->unixTimestamp() . ' BETWEEN u.time_limit_from AND u.time_limit_until)) ',
                array('integer','integer'),
                array(ROLE_FOLDER_ID, SYSTEM_ROLE_ID)
            );
            
            $users = array();
            
            $counter = 0;
            while ($row = $ilDB->fetchAssoc($res)) {
                $details = ilDiskQuotaChecker::_lookupDiskUsage($row['usr_id']);
                
                $users[$counter]['disk_quota'] = $row['disk_quota'];
                $users[$counter]['disk_usage'] = $details['disk_usage'];
                $users[$counter]['email'] = $row['email'];
                $users[$counter]['firstname'] = $row['firstname'];
                $users[$counter]['lastname'] = $row['lastname'];
                
                ++$counter;
            }

            if (count($users)) {
                foreach ($this->getRecipients() as $rcp) {
                    $usrId = ilObjUser::_lookupId($rcp);
                
                    $this->initLanguage($usrId);
                    $this->initMail();
                
                    $this->setSubject($this->getLanguage()->txt('disk_quota_summary_subject'));
                
                    $this->setBody(ilMail::getSalutation($usrId, $this->getLanguage()));
                
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguage()->txt('disk_quota_exceeded_headline'));
                    $this->appendBody("\n\n");
                
                    $first = true;
                    $counter = 0;
                    $numUsers = count($users);
                    foreach ($users as $user) {
                        if (!$first) {
                            $this->appendBody("\n---------------------------------------------------\n\n");
                        }
                    
                        $this->appendBody(
                        $this->getLanguage()->txt('fullname') . ': ' .
                        $user['lastname'] . ', ' . $user['firstname']
                    . "\n"
                    );
                        $this->appendBody(
                        $this->getLanguage()->txt('email') . ': ' .
                        $user['email']
                    . "\n"
                    );
                        $this->appendBody(
                        $this->getLanguage()->txt('disk_quota') . ': ' .
                        ilUtil::formatSize($user['disk_quota'], 'short', $this->getLanguage())
                    . "\n"
                    );
                        $this->appendBody(
                        $this->getLanguage()->txt('currently_used_disk_space') . ': ' .
                        ilUtil::formatSize($user['disk_usage'], 'short', $this->getLanguage())
                    . "\n"
                    );
                    
                        $this->appendBody(
                        $this->getLanguage()->txt('usrf_profile_link') . ': ' .
                        ilUtil::_getHttpPath() . '/goto.php?target=usrf&client_id=' . CLIENT_ID
                    );
                    
                        if ($counter < $numUsers - 1) {
                            $this->appendBody("\n");
                        }
                    
                        ++$counter;
                        $first = false;
                    }
                                
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp), array('system'), false);
                }
            }
        }
    }
}
