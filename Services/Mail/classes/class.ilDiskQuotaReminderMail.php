<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilDiskQuotaReminderMail
*
* Sends e-mail to users who have exceeded their disk quota.
*
* @author Werner Randelshofer <werner.randelshofer@hslu.ch>
* @version $Id$
*
*/
class ilDiskQuotaReminderMail
{
    private $lang_variables_as_fallback = true;

    /** Data used to fill in the placeholders in the mail.
     * Contains key value pairs with the name of the placeholder as keys.
     */
    private $data;

    private $tmp_lng;
    
    /**
    * constructor
    * @access	public
    */
    public function __construct()
    {
    }
    
    public function useLangVariablesAsFallback($a_status)
    {
        $this->lang_variables_as_fallback = $a_status;
    }
    
    public function areLangVariablesUsedAsFallback()
    {
        return $this->lang_variables_as_fallback;
    }
    
    /**
    * Sets used to fill in the placeholders in the mail.
    *
    * The following key value pairs must be supplied:
    *
    * 'language'	string	language of user
    * 'gender'		string	gender of user 'm' or 'f'
    * 'firstname'	string	firstname of the user
    * 'lastname'	string	lastname of the user
    * 'email'		string	email address of the user
    * 'login'		string	login of the user
    * 'disk_quota'	integer	disk quota in bytes of the user
    * 'disk_usage'	integer	disk usage in bytes of the user
    * 'disk_usage_details'	associative array with the values returned by
     *						ilDiskQuotaChecker::_lookupDiskUsage($a_usr_id)
    *
    * @param $a_data array Key value pairs with the name of the placeholder
    *  as keys.
    */
    public function setData($a_data)
    {
        $this->data = $a_data;
    }

    /**
    * reset all values
    */
    public function reset()
    {
        unset($this->data);
    }
    
    /**
    * get new mail template array (including subject and message body)
    */
    public function readMailTemplate($a_lang)
    {
        if (!is_array($this->amail[$a_lang])) {
            require_once('./Services/WebDAV/classes/class.ilObjDiskQuotaSettings.php');
            $this->amail[$a_lang] = ilObjDiskQuotaSettings::_lookupReminderMailTemplate($a_lang);
            $this->amail["body"] = trim($this->amail["body"]);
            $this->amail["subject"] = trim($this->amail["subject"]);
        }

        return $this->amail[$a_lang];
    }
    
    /**
    * Sends the mail with its object properties as MimeMail
    * It first tries to read the mail body, subject and sender address from posted named formular fields.
    * If no field values found the defaults are used.
    * Placehoders will be replaced by the appropriate data.
    * @access	public
    * @param object ilUser
    */
    public function send()
    {
        global $DIC;
        
        // determine language and get account mail data
        // fall back to default language if acccount mail data is not given for user language.
        $amail = $this->readMailTemplate($this->data['language']);
        if ($amail['body'] == '' || $amail['subject'] == '') {
            $amail = $this->readMailTemplate($DIC->settings()->get('language'));
            $lang = $DIC->settings()->get('language');
        } else {
            $lang = $this->data['language'];
        }
        
        // fallback if mail data is still not given
        if ($this->areLangVariablesUsedAsFallback() &&
           ($amail['body'] == '' || $amail['subject'] == '')) {
            $lang = $this->data['language'];
            $tmp_lang = $this->getLng($lang);
                        
            // mail subject
            $mail_subject = $tmp_lang->txt('disk_quota_mail_subject');

            // mail body
            $mail_body = $tmp_lang->txt('disk_quota_mail_body_salutation') . ' ' . $data['firstname'] . ' ' . $data['lastname'] . ",\n\n" .
                $tmp_lang->txt('disk_quota_body_text1') . "\n\n" .
                $tmp_lang->txt('disk_quota_body_text2') . "\n" .
                ILIAS_HTTP_PATH . '/login.php?client_id=' . CLIENT_ID . "\n";
            $mail_body .= $tmp_lang->txt('login') . ': ' . $data['firstname'] . "\n";
            $mail_body .= "\n";
            $mail_body .= $tmp_lang->txt('disk_quota_mail_body_text3') . "\n\r";
        //$mail_body .= $user->getProfileAsString($tmp_lang);
        } else {
            // replace placeholders
            $mail_subject = $this->replacePlaceholders($amail['subject'], $amail, $lang);
            $mail_body = $this->replacePlaceholders($amail['body'], $amail, $lang);
        }

        /** @var ilMailMimeSenderFactory $senderFactory */
        $senderFactory = $GLOBALS["DIC"]["mail.mime.sender.factory"];

        // send the mail
        include_once 'Services/Mail/classes/class.ilMimeMail.php';
        $mmail = new ilMimeMail();
        $mmail->From($senderFactory->system());
        $mmail->Subject($mail_subject);
        $mmail->To($this->data['email']);
        $mmail->Body($mail_body);
        $mmail->Send();
        
        include_once 'Services/Mail/classes/class.ilMail.php';
        $mail = new ilMail($GLOBALS['DIC']['ilUser']->getId());
        $mail->sendMail($this->data['login'], "", "", $mail_subject, $mail_body, array(), array("normal"));



        return true;
    }

    private function getLng($a_lang)
    {
        if ($this->tmp_lng == null || $this->tmp_lng->lang_key != $a_lang) {
            $this->tmp_lng = new ilLanguage($lang);
        }
        return $this->tmp_lng;
    }
    
    public function replacePlaceholders($a_string, $a_amail, $a_lang)
    {
        global $DIC;

        $tmp_lang = $this->getLng($a_lang);
        
        // determine salutation
        switch ($this->data['gender']) {
            case "f":	$gender_salut = $a_amail["sal_f"];
                        break;
            case "m":	$gender_salut = $a_amail["sal_m"];
                        break;
            default:	$gender_salut = $a_amail["sal_g"];
        }
        $gender_salut = trim($gender_salut);

        $a_string = str_replace("[MAIL_SALUTATION]", $gender_salut, $a_string);
        $a_string = str_replace("[LOGIN]", $this->data['login'], $a_string);
        $a_string = str_replace("[FIRST_NAME]", $this->data['firstname'], $a_string);
        $a_string = str_replace("[LAST_NAME]", $this->data['lastname'], $a_string);
        // BEGIN Mail Include E-Mail Address in account mail
        $a_string = str_replace("[EMAIL]", $this->data['email'], $a_string);
        $a_string = str_replace(
            "[ILIAS_URL]",
            ILIAS_HTTP_PATH . "/login.php?client_id=" . CLIENT_ID,
            $a_string
        );
        $a_string = str_replace("[CLIENT_NAME]", CLIENT_NAME, $a_string);
        $a_string = str_replace(
            "[ADMIN_MAIL]",
            $DIC->settings()->get("admin_email"),
            $a_string
        );

        $a_string = str_replace("[DISK_QUOTA]", ilUtil::formatSize($this->data['disk_quota'], 'short', $tmp_lang), $a_string);
        $a_string = str_replace("[DISK_USAGE]", ilUtil::formatSize($this->data['disk_usage'], 'short', $tmp_lang), $a_string);

        $disk_usage_details = '';
        foreach ($this->data['disk_usage_details'] as $details) {
            $disk_usage_details .= number_format($details['count'], 0) . ' ' .
                $tmp_lang->txt($details['type']) . ' ' .
                ilUtil::formatSize($details['size'], 'short', $tmp_lang) . "\n";
        }
        $a_string = str_replace("[DISK_USAGE_DETAILS]", $disk_usage_details, $a_string);
            
        return $a_string;
    }
}
