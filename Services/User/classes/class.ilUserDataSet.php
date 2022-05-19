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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserDataSet extends ilDataSet
{
    protected array $temp_picture_dirs = array(); // Missing array type.
    public array $multi = array(); // Missing array type.
    protected array $users; // Missing array type.
    
    public function getSupportedVersions() : array // Missing array type.
    {
        return array("4.3.0", "4.5.0", "5.1.0", "5.2.0", "5.3.0");
    }
    
    protected function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "https://www.ilias.de/xml/Services/User/" . $a_entity;
    }
    
    protected function getTypes(string $a_entity, string $a_version) : array // Missing array type.
    {
        // user profile type
        if ($a_entity == "usr_profile") {
            switch ($a_version) {
                case "4.3.0":
                case "4.5.0":
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    return array(
                        "Id" => "integer",
                        "Username" => "text",
                        "Firstname" => "text",
                        "Lastname" => "text",
                        "Title" => "text",
                        "Birthday" => "text",
                        "Gender" => "text",
                        "Institution" => "text",
                        "Department" => "text",
                        "Street" => "text",
                        "Zipcode" => "text",
                        "City" => "text",
                        "Country" => "text",
                        "SelCountry" => "text",
                        "PhoneOffice" => "text",
                        "PhoneHome" => "text",
                        "PhoneMobile" => "text",
                        "Fax" => "text",
                        "Email" => "text",
                        "SecondEmail" => "text",
                        "Hobby" => "text",
                        "ReferralComment" => "text",
                        "Matriculation" => "text",
                        "Latitude" => "text",
                        "Longitude" => "text",
                        "LocZoom" => "text",
                        "Picture" => "directory"
                        );
            }
        }

        if ($a_entity == "usr_setting") {
            switch ($a_version) {
                case "4.3.0":
                case "4.5.0":
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    return array(
                        "UserId" => "integer",
                        "Keyword" => "text",
                        "Value" => "text"
                    );
            }
        }

        if ($a_entity == "personal_data") {
            switch ($a_version) {
                case "4.3.0":
                case "4.5.0":
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    return array(
                        "Id" => "integer"
                    );
            }
        }

        if ($a_entity == "usr_multi") {
            switch ($a_version) {
                case "4.5.0":
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    return array(
                        "UserId" => "integer",
                        "FieldId" => "text",
                        "Value" => "text"
                    );
            }
        }
        return [];
    }

    public function getXmlRecord(string $a_entity, string $a_version, array $a_set) : array // Missing array type.
    {
        global $DIC;

        if ($a_entity == "usr_profile") {
            $tmp_dir = ilFileUtils::ilTempnam();
            ilFileUtils::makeDir($tmp_dir);

            $im = ilObjUser::_getPersonalPicturePath(
                $a_set["Id"],
                "small",
                true,
                true
            );

            if ($im != "") {
                ilObjUser::copyProfilePicturesToDirectory($a_set["Id"], $tmp_dir);
            }
            
            $this->temp_picture_dirs[$a_set["Id"]] = $tmp_dir;
            
            $a_set["Picture"] = $tmp_dir;
        }

        return $a_set;
    }

    public function afterXmlRecordWriting(string $a_entity, string $a_version, array $a_set) : void // Missing array type.
    {
        if ($a_entity == "usr_profile") {
            // cleanup temp dirs for pictures
            $tmp_dir = $this->temp_picture_dirs[$a_set["Id"]];
            if ($tmp_dir != "" && is_dir($tmp_dir)) {
                ilFileUtils::delDir($tmp_dir);
            }
        }
    }

    public function readData(string $a_entity, string $a_version, array $a_ids) : void // Missing array type.
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
                
        if ($a_entity == "personal_data") {
            switch ($a_version) {
                case "4.3.0":
                case "4.5.0":
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    $this->data = array();
                    foreach ($a_ids as $id) {
                        $this->data[] = array("Id" => $id);
                    }
                    break;
            }
        }
        
        if ($a_entity == "usr_profile") {
            switch ($a_version) {
                case "4.3.0":
                case "4.5.0":
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT usr_id id, login username, firstname, lastname, " .
                        " title, birthday, gender, institution, department, street, city, zipcode, country, sel_country, " .
                        " phone_office, phone_home, phone_mobile, fax, email, hobby, referral_comment, matriculation, " .
                        " delicious, latitude, longitude, loc_zoom" .
                        " FROM usr_data u " .
                        "WHERE " .
                        $ilDB->in("u.usr_id", $a_ids, false, "integer"));
                    break;

                case "5.2.0":
                    $this->getDirectDataFromQuery("SELECT usr_id id, login username, firstname, lastname, " .
                        " title, birthday, gender, institution, department, street, city, zipcode, country, sel_country, " .
                        " phone_office, phone_home, phone_mobile, fax, email, hobby, referral_comment, matriculation, " .
                        " latitude, longitude, loc_zoom" .
                        " FROM usr_data u " .
                        "WHERE " .
                        $ilDB->in("u.usr_id", $a_ids, false, "integer"));
                    break;
                case "5.3.0":
                    $this->getDirectDataFromQuery("SELECT usr_id id, login username, firstname, lastname, " .
                        " title, birthday, gender, institution, department, street, city, zipcode, country, sel_country, " .
                        " phone_office, phone_home, phone_mobile, fax, email, second_email, hobby, referral_comment, matriculation, " .
                        " latitude, longitude, loc_zoom" .
                        " FROM usr_data u " .
                        "WHERE " .
                        $ilDB->in("u.usr_id", $a_ids, false, "integer"));
                    break;
            }
        }
        
        if ($a_entity == "usr_setting") {
            switch ($a_version) {
                case "4.3.0":
                case "4.5.0":
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    // for all user ids get data from usr_pref and mail options, create records user_id/name/value
                    $prefs = array("date_format", "day_end", "day_start", "bs_allow_to_contact_me", "chat_osc_accept_msg", "hide_own_online_status", "hits_per_page", "language",
                        "public_birthday", "puplic_city", "public_country", "public_delicious", "public_department", "public_email", "public_second_email",
                        "public_fax", "public_gender", "public_hobby", "public_im_aim", "public_im_icq", "public_im_jabber",
                        "public_im_msn", "public_im_skype", "public_im_voip", "public_im_yahoo", "public_institution", "public_location",
                        "public_matriculation", "public_phone_home", "public_phone_mobile", "public_phone_office",
                        "public_profile", "public_sel_country", "public_street", "public_title", "public_upload", "public_zipcode",
                        "screen_reader_optimization", "show_users_online",
                        "store_last_visited", "time_format", "user_tz", "weekstart",
                        "session_reminder_enabled", "session_reminder_lead_time", "usr_starting_point",
                        "char_selector_availability", "char_selector_definition", "chat_broadcast_typing");

                    if (version_compare($a_version, '5.2.0', '>=')) {
                        unset(
                            $prefs['public_im_aim'], $prefs['public_im_icq'], $prefs['public_im_jabber'],
                            $prefs['public_im_msn'], $prefs['public_im_skype'], $prefs['public_im_voip'],
                            $prefs['public_im_yahoo'], $prefs['public_delicious']
                        );
                    }

                    $this->data = array();
                    $set = $ilDB->query("SELECT * FROM usr_pref " .
                        " WHERE " . $ilDB->in("keyword", $prefs, false, "text") .
                        " AND " . $ilDB->in("usr_id", $a_ids, false, "integer"));
                    while ($rec = $ilDB->fetchAssoc($set)) {
                        $this->data[] = array("UserId" => $rec["usr_id"], "Keyword" => $rec["keyword"], "Value" => $rec["value"]);
                    }
                    break;
            }
        }

        if ($a_entity == "usr_multi") {
            switch ($a_version) {
                case "4.5.0":
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    $this->data = array();
                    $set = $ilDB->query("SELECT * FROM usr_data_multi" .
                        " WHERE " . $ilDB->in("usr_id", $a_ids, false, "integer"));
                    while ($rec = $ilDB->fetchAssoc($set)) {
                        $this->data[] = array("UserId" => $rec["usr_id"], "FieldId" => $rec["field_id"], "Value" => $rec["value"]);
                    }
                    break;
            }
        }
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ) : void {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $ilUser = $DIC['ilUser'];

        switch ($a_entity) {
            case "personal_data":
                // only users themselves import their profiles!
                // thus we can map the import id of the dataset to the current user
                $a_mapping->addMapping("Services/User", "usr", $a_rec["Id"], $ilUser->getId());
                break;
                
            case "usr_profile":
                $usr_id = $a_mapping->getMapping("Services/User", "usr", $a_rec["Id"]);
                if ($usr_id > 0 && ilObject::_lookupType($usr_id) == "usr") {
                    if (!isset($this->users[$usr_id])) {
                        $this->users[$usr_id] = new ilObjUser($usr_id);
                    }
                    $user = $this->users[$usr_id];
                    $prof = new ilUserProfile();
                    $prof->skipField("username");
                    $prof->skipField("password");
                    $prof->skipField("roles");
                    $prof->skipGroup("settings");
                    $fields = $prof->getStandardFields();
                    foreach ($fields as $k => $f) {
                        $up_k = $this->convertToLeadingUpper($k);
                        // only change fields, when it is possible in profile
                        if (ilUserProfile::userSettingVisible($k) &&
                            !$ilSetting->get("usr_settings_disable_" . $k) &&
                            $f["method"] != "" && isset($a_rec[$up_k])) {
                            $set_method = "set" . substr($f["method"], 3);
                            $user->{$set_method}(ilUtil::secureString($a_rec[$up_k]));
                        }
                    }

                    $user->setLatitude($a_rec["Latitude"]);
                    $user->setLongitude($a_rec["Longitude"]);
                    $user->setLocationZoom($a_rec["LocZoom"]);

                    $user->update();
                    
                    // personal picture
                    $pic_dir = $this->getImportDirectory() . "/" . str_replace("..", "", $a_rec["Picture"]);
                    if ($pic_dir != "" && is_dir($pic_dir)) {
                        $upload_file = $pic_dir . "/usr_" . $a_rec["Id"] . ".jpg";
                        if (!is_file($upload_file)) {
                            $upload_file = $pic_dir . "/upload_" . $a_rec["Id"] . "pic";
                        }
                        if (is_file($upload_file)) {
                            ilObjUser::_uploadPersonalPicture($upload_file, $user->getId());
                        }
                    }
                }
                break;

            case "usr_setting":
                $usr_id = $a_mapping->getMapping("Services/User", "usr", $a_rec["UserId"]);
                if ($usr_id > 0 && ilObject::_lookupType($usr_id) == "usr") {
                    if (!isset($this->users[$usr_id])) {
                        $this->users[$usr_id] = new ilObjUser($usr_id);
                    }
                    $user = $this->users[$usr_id];
                    $user->writePref($a_rec["Keyword"], ilUtil::secureString($a_rec["Value"]));
                }
                break;
                
            case "usr_multi":
                $usr_id = $a_mapping->getMapping("Services/User", "usr", $a_rec["UserId"]);
                if ($usr_id > 0 && ilObject::_lookupType($usr_id) == "usr") {
                    $this->multi[$usr_id][$a_rec["FieldId"]][] = ilUtil::secureString($a_rec["Value"]);
                }
                break;
        }
    }
}
