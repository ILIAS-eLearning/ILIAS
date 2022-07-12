<?php

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
 * XML writer class
 * Class to simplify manual writing of xml documents.
 * It only supports writing xml sequentially, because the xml document
 * is saved in a string with no additional structure information.
 * The author is responsible for well-formedness and validity
 * of the xml document.
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilUserXMLWriter extends ilXmlWriter
{
    public ILIAS $ilias;
    public array $users; // Missing array type.
    public int $user_id = 0;
    public bool $attachRoles = false;
    public bool $attachPreferences = false;

    /**
     * fields to be exported
     */
    private array $settings; // Missing array type.

    public function __construct()
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $ilUser = $DIC->user();

        parent::__construct();

        $this->ilias = $ilias;
        $this->user_id = $ilUser->getId();
        $this->attachRoles = false;
    }

    public function setAttachRoles(bool $value) : void
    {
        $this->attachRoles = $value;
    }

    public function setObjects(array $users) : void // Missing array type.
    {
        $this->users = $users;
    }


    public function start() : bool
    {
        if (!is_array($this->users)) {
            return false;
        }

        $this->__buildHeader();

        $udf_data = ilUserDefinedFields::_getInstance();
        $udf_data->addToXML($this);

        foreach ($this->users as $user) {
            $this->__handleUser($user);
        }

        $this->__buildFooter();

        return true;
    }

    public function getXML() : string
    {
        return $this->xmlDumpMem(false);
    }


    public function __buildHeader() : void
    {
        $this->xmlSetDtdDef("<!DOCTYPE Users PUBLIC \"-//ILIAS//DTD UserImport//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_user_5_1.dtd\">");
        $this->xmlSetGenCmt("User of ilias system");
        $this->xmlHeader();

        $this->xmlStartTag('Users');
    }

    public function __buildFooter() : void
    {
        $this->xmlEndTag('Users');
    }

    public function __handleUser(array $row) : void // Missing array type.
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        if (!is_array($this->settings)) {
            $this->setSettings(ilObjUserFolder::getExportSettings());
        }

        $prefs = ilObjUser::_getPreferences($row["usr_id"]);
        
        if (strlen($row["language"]) == 0) {
            $row["language"] = $lng->getDefaultLanguage();
        }

        $attrs = array(
            'Id' => "il_" . IL_INST_ID . "_usr_" . $row["usr_id"],
            'Language' => $row["language"],
            'Action' => "Update"
        );

        $this->xmlStartTag("User", $attrs);

        $this->xmlElement("Login", null, $row["login"]);

        if ($this->attachRoles == true) {
            $query = sprintf(
                "SELECT object_data.title, object_data.description,  rbac_fa.* " .
                            "FROM object_data, rbac_ua, rbac_fa WHERE rbac_ua.usr_id = %s " .
                            "AND rbac_ua.rol_id = rbac_fa.rol_id AND object_data.obj_id = rbac_fa.rol_id",
                $ilDB->quote($row["usr_id"], 'integer')
            );
            $rbacresult = $ilDB->query($query);

            while ($rbacrow = $rbacresult->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
                if ($rbacrow["assign"] != "y") {
                    continue;
                }

                $type = "";

                if ($rbacrow["parent"] == ROLE_FOLDER_ID) {
                    $type = "Global";
                } else {
                    $type = "Local";
                }
                if (strlen($type)) {
                    $this->xmlElement(
                        "Role",
                        array("Id" =>
                                "il_" . IL_INST_ID . "_role_" . $rbacrow["rol_id"], "Type" => $type),
                        $rbacrow["title"]
                    );
                }
            }
        }

        $this->__addElement("Firstname", $row["firstname"]);
        $this->__addElement("Lastname", $row["lastname"]);
        $this->__addElement("Title", $row["title"]);

        if ($this->canExport("PersonalPicture", "upload")) {
            $imageData = $this->getPictureValue($row["usr_id"]);
            if ($imageData) {
                $value = array_shift($imageData); //$imageData["value"];
                $this->__addElement("PersonalPicture", $value, $imageData, "upload");
            }
        }


        $this->__addElement("Gender", $row["gender"]);
        $this->__addElement("Email", $row["email"]);
        $this->__addElement("SecondEmail", $row["second_email"], null, "second_email");
        $this->__addElement("Birthday", $row["birthday"]);
        $this->__addElement("Institution", $row["institution"]);
        $this->__addElement("Street", $row["street"]);
        $this->__addElement("City", $row["city"]);
        $this->__addElement("PostalCode", $row["zipcode"], null, "zipcode");
        $this->__addElement("Country", $row["country"]);
        $this->__addElement("SelCountry", $row["sel_country"], null, "sel_country");
        $this->__addElement("PhoneOffice", $row["phone_office"], null, "phone_office");
        $this->__addElement("PhoneHome", $row["phone_home"], null, "phone_home");
        $this->__addElement("PhoneMobile", $row["phone_mobile"], null, "phone_mobile");
        $this->__addElement("Fax", $row["fax"]);
        $this->__addElement("Hobby", $row["hobby"]);
        
        $this->__addElementMulti("GeneralInterest", $row["interests_general"] ?? [], null, "interests_general");
        $this->__addElementMulti("OfferingHelp", $row["interests_help_offered"] ?? [], null, "interests_help_offered");
        $this->__addElementMulti("LookingForHelp", $row["interests_help_looking"] ?? [], null, "interests_help_looking");
        
        $this->__addElement("Department", $row["department"]);
        $this->__addElement("Comment", $row["referral_comment"], null, "referral_comment");
        $this->__addElement("Matriculation", $row["matriculation"]);
        $this->__addElement("Active", $row["active"] ? "true":"false");
        $this->__addElement("ClientIP", $row["client_ip"], null, "client_ip");
        $this->__addElement("TimeLimitOwner", $row["time_limit_owner"], null, "time_limit_owner");
        $this->__addElement("TimeLimitUnlimited", $row["time_limit_unlimited"], null, "time_limit_unlimited");
        $this->__addElement("TimeLimitFrom", $row["time_limit_from"], null, "time_limit_from");
        $this->__addElement("TimeLimitUntil", $row["time_limit_until"], null, "time_limit_until");
        $this->__addElement("TimeLimitMessage", $row["time_limit_message"], null, "time_limit_message");
        $this->__addElement("ApproveDate", $row["approve_date"], null, "approve_date");
        $this->__addElement("AgreeDate", $row["agree_date"], null, "agree_date");

        if (strlen($row["auth_mode"]) > 0) {
            $this->__addElement("AuthMode", null, array("type" => $row["auth_mode"]), "auth_mode", true);
        }

        if (strlen($row["ext_account"]) > 0) {
            $this->__addElement("ExternalAccount", $row["ext_account"], null, "ext_account", true);
        }

        if ($this->canExport("Look", "skin_style")) {
            $this->__addElement("Look", null, array(
                "Skin" => $prefs["skin"], "Style" => $prefs["style"]
            ), "skin_style", true);
        }


        $this->__addElement("LastUpdate", $row["last_update"], null, "last_update");
        $this->__addElement("LastLogin", $row["last_login"], null, "last_login");

        $udf_data = new ilUserDefinedData($row['usr_id']);
        $udf_data->addToXML($this);

        $this->__addElement("AccountInfo", $row["ext_account"], array("Type" => "external"));

        $this->__addElement("GMapsInfo", null, array(
            "longitude" => $row["longitude"],
            "latitude" => $row["latitude"],
            "zoom" => $row["loc_zoom"]));

        $this->__addElement("Feedhash", $row["feed_hash"]);

        if ($this->attachPreferences || $this->canExport("prefs", "preferences")) {
            $this->__handlePreferences($prefs, $row);
        }
        
        $this->xmlEndTag('User');
    }

    
    private function __handlePreferences(array $prefs, array $row) : void // Missing array type.
    {
        //todo nadia: test mail_address_option
        $mailOptions = new ilMailOptions($row["usr_id"]);
        $prefs["mail_incoming_type"] = $mailOptions->getIncomingType();
        $prefs["mail_address_option"] = $mailOptions->getEmailAddressMode();
        $prefs["mail_signature"] = $mailOptions->getSignature();
        $prefs["mail_linebreak"] = $mailOptions->getLinebreak();
        if (count($prefs)) {
            $this->xmlStartTag("Prefs");
            foreach ($prefs as $key => $value) {
                if (self::isPrefExportable($key)) {
                    $this->xmlElement("Pref", array("key" => $key), $value);
                }
            }
            $this->xmlEndTag("Prefs");
        }
    }
    
    public function __addElementMulti(
        string $tagname,
        array $value,
        ?array $attrs = null,
        ?string $settingsname = null,
        bool $requiredTag = false
    ) : void {
        if (is_array($value) && count($value)) {
            foreach ($value as $item) {
                $this->__addElement($tagname, $item, $attrs, $settingsname, $requiredTag);
            }
        }
    }
    
    public function __addElement(
        string $tagname,
        ?string $value,
        array $attrs = null,
        ?string $settingsname = null,
        bool $requiredTag = false
    ) : void {
        if ($this->canExport($tagname, $settingsname)) {
            if (strlen($value) > 0 || $requiredTag || (is_array($attrs) && count($attrs) > 0)) {
                $this->xmlElement($tagname, $attrs, (string) $value);
            }
        }
    }

    private function canExport(
        string $tagname,
        ?string $settingsname = null
    ) : bool {
        return !is_array($this->settings) ||
               in_array(strtolower($tagname), $this->settings) !== false ||
               in_array($settingsname, $this->settings) !== false;
    }

    public function setSettings(array $settings) : void // Missing array type.
    {
        $this->settings = $settings;
    }

    /**
     * return array with base-encoded picture data as key value,
     * encoding type as encoding, and image type as key type.
     */
    private function getPictureValue(int $usr_id) : ?array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        // personal picture
        $q = sprintf(
            "SELECT value FROM usr_pref WHERE usr_id = %s AND keyword = %s",
            $ilDB->quote($usr_id, "integer"),
            $ilDB->quote('profile_image', "text")
        );
        $r = $ilDB->query($q);
        if ($ilDB->numRows($r) == 1) {
            $personal_picture_data = $r->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
            $personal_picture = $personal_picture_data["value"];
            $webspace_dir = ilFileUtils::getWebspaceDir();
            $image_file = $webspace_dir . "/usr_images/" . $personal_picture;
            if (is_file($image_file)) {
                $fh = fopen($image_file, "rb");
                if ($fh) {
                    $image_data = fread($fh, filesize($image_file));
                    fclose($fh);
                    $base64 = base64_encode($image_data);
                    $imagetype = "image/jpeg";
                    if (preg_match("/.*\.(png|gif)$/", $personal_picture, $matches)) {
                        $imagetype = "image/" . $matches[1];
                    }
                    return array(
                        "value" => $base64,
                        "encoding" => "Base64",
                        "imagetype" => $imagetype
                    );
                }
            }
        }
        return null;
    }

    
    /**
     * if set to true, all preferences of a user will be set
     */
    public function setAttachPreferences(bool $attachPrefs) : void
    {
        $this->attachPreferences = $attachPrefs;
    }
    
    /**
     * return exportable preference keys as found in db
     */
    public static function getExportablePreferences() : array // Missing array type.
    {
        return array(
                'hits_per_page',
                'public_city',
                'public_country',
                'public_department',
                'public_email',
                'public_second_email',
                'public_fax',
                'public_hobby',
                'public_institution',
                'public_matriculation',
                'public_phone',
                'public_phone_home',
                'public_phone_mobile',
                'public_phone_office',
                'public_profile',
                'public_street',
                'public_upload',
                'public_zip',
                'send_info_mails',
                /*'show_users_online',*/
                'hide_own_online_status',
                'bs_allow_to_contact_me',
                'chat_osc_accept_msg',
                'chat_broadcast_typing',
                'user_tz',
                'weekstart',
                'mail_incoming_type',
                'mail_signature',
                'mail_linebreak',
                'public_interests_general',
                'public_interests_help_offered',
                'public_interests_help_looking'
        );
    }
    
    /**
     * returns wether a key from db is exportable or not
     */
    public static function isPrefExportable(string $key) : bool
    {
        return in_array($key, self::getExportablePreferences());
    }
}
