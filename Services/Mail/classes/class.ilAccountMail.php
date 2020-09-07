<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilAccountMail
*
* Sends e-mail to newly created accounts.
*
* @author Stefan Schneider <stefan.schneider@hrz.uni-giessen.de>
* @author Alex Killing <alex.killing@hrz.uni-giessen.de>
*
*/
class ilAccountMail
{
    /**
    * user password
    * @var	string	user password (plain text)
    * @access	private
    */
    public $u_password = "";

    /**
    * user object (instance of ilObjUser)
    * @var	object
    * @access	private
    */
    public $user = "";

    /**
    * repository item target (e.g. "crs_123"
    * @var	string	target
    * @access	private
    */
    public $target = "";
    
    private $lang_variables_as_fallback = false;
    
    private $attachments = array();
    
    /** @var bool */
    private $attachConfiguredFiles = false;

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
     * @return bool
     */
    public function shouldAttachConfiguredFiles() : bool
    {
        return $this->attachConfiguredFiles;
    }

    /**
     * @param bool $attachConfiguredFiles
     */
    public function setAttachConfiguredFiles(bool $attachConfiguredFiles)
    {
        $this->attachConfiguredFiles = $attachConfiguredFiles;
    }

    /**
    * set user password
    *
    * @access	public
    * @param	string	$a_pwd		users password as plain text
    */
    public function setUserPassword($a_pwd)
    {
        $this->u_password = $a_pwd;
    }

    /**
    * get user password
    *
    * @access	public
    * @return	string		users password as plain text
    */
    public function getUserPassword()
    {
        return $this->u_password;
    }

    /**
    * Set user. The user object should provide email, language
    * login, gender, first and last name
    *
    * @access	public
    * @param	object	$a_user		user object
    */
    public function setUser(&$a_user)
    {
        if (
            $this->user instanceof ilObjUser &&
            $a_user instanceof ilObjUser &&
            $a_user->getId() != $this->user->getId()
        ) {
            $this->attachments = [];
        }

        $this->user = &$a_user;
    }

    /**
    * get user object
    *
    * @access	public
    * @return	object		user object
    */
    public function &getUser()
    {
        return $this->user;
    }

    /**
    * set repository item target
    *
    * @access	public
    * @param	string	$a_target		target as used in permanent links, e.g. crs_123
    */
    public function setTarget($a_target)
    {
        $this->u_target = $a_target;
    }

    /**
    * get target
    *
    * @access	public
    * @return	string		repository item target
    */
    public function getTarget()
    {
        return $this->target;
    }

    /**
    * reset all values
    */
    public function reset()
    {
        unset($this->u_password);
        unset($this->user);
        unset($this->target);
    }
    
    /**
    * get new account mail array (including subject and message body)
    */
    public function readAccountMail($a_lang)
    {
        if (!is_array($this->amail[$a_lang])) {
            include_once('./Services/User/classes/class.ilObjUserFolder.php');
            $this->amail[$a_lang] = ilObjUserFolder::_lookupNewAccountMail($a_lang);
            $amail["body"] = trim($amail["body"]);
            $amail["subject"] = trim($amail["subject"]);
        }

        return $this->amail[$a_lang];
    }

    /***
     * @param $mailData
     */
    private function addAttachments($mailData)
    {
        if ($this->shouldAttachConfiguredFiles() && isset($mailData['att_file'])) {
            $fs = new ilFSStorageUserFolder(USER_FOLDER_ID);
            $fs->create();

            $pathToFile = '/' . implode('/', array_map(function ($pathPart) {
                return trim($pathPart, '/');
            }, [
                    $fs->getAbsolutePath(),
                    $mailData['lang'],
                ]));

            $this->addAttachment($pathToFile, $mailData['att_file']);
        }
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
        global $ilSetting;
        
        $user = &$this->getUser();
        
        if (!$user->getEmail()) {
            return false;
        }
        
        // determine language and get account mail data
        // fall back to default language if acccount mail data is not given for user language.
        $amail = $this->readAccountMail($user->getLanguage());
        if ($amail['body'] == '' || $amail['subject'] == '') {
            $amail = $this->readAccountMail($ilSetting->get('language'));
            $lang = $ilSetting->get('language');
        } else {
            $lang = $user->getLanguage();
        }
        
        // fallback if mail data is still not given
        if ($this->areLangVariablesUsedAsFallback() &&
           ($amail['body'] == '' || $amail['subject'] == '')) {
            $lang = $user->getLanguage();
            $tmp_lang = new ilLanguage($lang);
                        
            // mail subject
            $mail_subject = $tmp_lang->txt('reg_mail_subject');
            
            $timelimit = "";
            if (!$user->checkTimeLimit()) {
                $tmp_lang->loadLanguageModule("registration");
                
                // #6098
                $timelimit_from = new ilDateTime($user->getTimeLimitFrom(), IL_CAL_UNIX);
                $timelimit_until = new ilDateTime($user->getTimeLimitUntil(), IL_CAL_UNIX);
                $timelimit = ilDatePresentation::formatPeriod($timelimit_from, $timelimit_until);
                $timelimit = "\n" . sprintf($tmp_lang->txt('reg_mail_body_timelimit'), $timelimit) . "\n\n";
            }

            // mail body
            $mail_body = $tmp_lang->txt('reg_mail_body_salutation') . ' ' . $user->getFullname() . ",\n\n" .
                $tmp_lang->txt('reg_mail_body_text1') . "\n\n" .
                $tmp_lang->txt('reg_mail_body_text2') . "\n" .
                ILIAS_HTTP_PATH . '/login.php?client_id=' . CLIENT_ID . "\n";
            $mail_body .= $tmp_lang->txt('login') . ': ' . $user->getLogin() . "\n";
            $mail_body .= $tmp_lang->txt('passwd') . ': ' . $this->u_password . "\n";
            $mail_body .= "\n" . $timelimit;
            $mail_body .= $tmp_lang->txt('reg_mail_body_text3') . "\n\r";
            $mail_body .= $user->getProfileAsString($tmp_lang);
        } else {
            $this->addAttachments($amail);

            // replace placeholders
            $mail_subject = $this->replacePlaceholders($amail['subject'], $user, $amail, $lang);
            $mail_body = $this->replacePlaceholders($amail['body'], $user, $amail, $lang);
        }

        /** @var ilMailMimeSenderFactory $senderFactory */
        $senderFactory = $GLOBALS["DIC"]["mail.mime.sender.factory"];

        // send the mail
        include_once 'Services/Mail/classes/class.ilMimeMail.php';
        $mmail = new ilMimeMail();
        $mmail->From($senderFactory->system());
        $mmail->Subject($mail_subject);
        $mmail->To($user->getEmail());
        $mmail->Body($mail_body);
        
        foreach ($this->attachments as $filename => $display_name) {
            $mmail->Attach($filename, "", "attachment", $display_name);
        }
        /*
        echo "<br><br><b>From</b>:".$ilSetting->get("admin_email");
        echo "<br><br><b>To</b>:".$user->getEmail();
        echo "<br><br><b>Subject</b>:".$mail_subject;
        echo "<br><br><b>Body</b>:".$mail_body;
        return true;*/
        $mmail->Send();
        
        return true;
    }
    
    public function replacePlaceholders($a_string, &$a_user, $a_amail, $a_lang)
    {
        global $ilSetting, $tree;
        
        // determine salutation
        switch ($a_user->getGender()) {
            case "f":	$gender_salut = $a_amail["sal_f"];
                        break;
            case "m":	$gender_salut = $a_amail["sal_m"];
                        break;
            default:	$gender_salut = $a_amail["sal_g"];
        }
        $gender_salut = trim($gender_salut);

        $a_string = str_replace("[MAIL_SALUTATION]", $gender_salut, $a_string);
        $a_string = str_replace("[LOGIN]", $a_user->getLogin(), $a_string);
        $a_string = str_replace("[FIRST_NAME]", $a_user->getFirstname(), $a_string);
        $a_string = str_replace("[LAST_NAME]", $a_user->getLastname(), $a_string);
        // BEGIN Mail Include E-Mail Address in account mail
        $a_string = str_replace("[EMAIL]", $a_user->getEmail(), $a_string);
        // END Mail Include E-Mail Address in account mail
        $a_string = str_replace("[PASSWORD]", $this->getUserPassword(), $a_string);
        $a_string = str_replace(
            "[ILIAS_URL]",
            ILIAS_HTTP_PATH . "/login.php?client_id=" . CLIENT_ID,
            $a_string
        );
        $a_string = str_replace("[CLIENT_NAME]", CLIENT_NAME, $a_string);
        $a_string = str_replace(
            "[ADMIN_MAIL]",
            $ilSetting->get("admin_email"),
            $a_string
        );
            
        // (no) password sections
        if ($this->getUserPassword() == "") {
            // #12232
            $a_string = preg_replace("/\[IF_PASSWORD\].*\[\/IF_PASSWORD\]/imsU", "", $a_string);
            $a_string = preg_replace("/\[IF_NO_PASSWORD\](.*)\[\/IF_NO_PASSWORD\]/imsU", "$1", $a_string);
        } else {
            $a_string = preg_replace("/\[IF_NO_PASSWORD\].*\[\/IF_NO_PASSWORD\]/imsU", "", $a_string);
            $a_string = preg_replace("/\[IF_PASSWORD\](.*)\[\/IF_PASSWORD\]/imsU", "$1", $a_string);
        }
                
        // #13346
        if (!$a_user->getTimeLimitUnlimited()) {
            // #6098
            $a_string = preg_replace("/\[IF_TIMELIMIT\](.*)\[\/IF_TIMELIMIT\]/imsU", "$1", $a_string);
            $timelimit_from = new ilDateTime($a_user->getTimeLimitFrom(), IL_CAL_UNIX);
            $timelimit_until = new ilDateTime($a_user->getTimeLimitUntil(), IL_CAL_UNIX);
            $timelimit = ilDatePresentation::formatPeriod($timelimit_from, $timelimit_until);
            $a_string = str_replace("[TIMELIMIT]", $timelimit, $a_string);
        } else {
            $a_string = preg_replace("/\[IF_TIMELIMIT\](.*)\[\/IF_TIMELIMIT\]/imsU", "", $a_string);
        }
        
        // target
        $tar = false;
        if ($_GET["target"] != "") {
            $tarr = explode("_", $_GET["target"]);
            if ($tree->isInTree($tarr[1])) {
                $obj_id = ilObject::_lookupObjId($tarr[1]);
                $type = ilObject::_lookupType($obj_id);
                if ($type == $tarr[0]) {
                    $a_string = str_replace(
                        "[TARGET_TITLE]",
                        ilObject::_lookupTitle($obj_id),
                        $a_string
                    );
                    $a_string = str_replace(
                        "[TARGET]",
                        ILIAS_HTTP_PATH . "/goto.php?client_id=" . CLIENT_ID . "&target=" . $_GET["target"],
                        $a_string
                    );
                        
                    // this looks complicated, but we may have no initilised $lng object here
                    // if mail is send during user creation in authentication
                    include_once("./Services/Language/classes/class.ilLanguage.php");
                    $a_string = str_replace(
                        "[TARGET_TYPE]",
                        ilLanguage::_lookupEntry($a_lang, "common", "obj_" . $tarr[0]),
                        $a_string
                    );
                        
                    $tar = true;
                }
            }
        }

        // (no) target section
        if (!$tar) {
            $a_string = preg_replace("/\[IF_TARGET\].*\[\/IF_TARGET\]/imsU", "", $a_string);
        } else {
            $a_string = preg_replace("/\[IF_TARGET\](.*)\[\/IF_TARGET\]/imsU", "$1", $a_string);
        }

        return $a_string;
    }
    
    public function addAttachment($a_filename, $a_display_name)
    {
        $this->attachments[$a_filename] = $a_display_name;
    }
}
