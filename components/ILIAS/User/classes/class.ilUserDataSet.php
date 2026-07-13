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

declare(strict_types=1);

use ILIAS\User\LocalDIC;
use ILIAS\User\Context;
use ILIAS\User\Profile\Profile;
use ILIAS\User\Profile\Fields\Field as ProfileField;
use ILIAS\User\Profile\Fields\Standard\Alias;
use ILIAS\User\Profile\Fields\Standard\Roles;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserDataSet extends ilDataSet
{
    protected ilUserExportConfig $export_config;
    private Profile $user_profile;
    private ilObjUser $current_user;
    protected array $temp_picture_dirs = []; // Missing array type.
    public array $multi = []; // Missing array type.
    protected array $users; // Missing array type.

    public function __construct()
    {
        global $DIC;
        $this->current_user = $DIC['ilUser'];

        parent::__construct();

        $this->user_profile = LocalDIC::dic()[Profile::class];
    }

    public function initByExporter(ilXmlExporter $xml_exporter): void
    {
        parent::initByExporter($xml_exporter);
        /** @var ilUserExportConfig $config */
        $config = $this->export->getExportConfigs()->getElementByClassName('ilUserExportConfig');
        $this->export_config = $config;
    }

    public function getSupportedVersions(): array // Missing array type.
    {
        return ["4.3.0", "4.5.0", "5.1.0", "5.2.0", "5.3.0"];
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Services/User/" . $a_entity;
    }

    protected function getTypes(string $a_entity, string $a_version): array // Missing array type.
    {
        // user profile type
        if ($a_entity == "usr_profile") {
            switch ($a_version) {
                case "4.3.0":
                case "4.5.0":
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    return [
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
                        ];
            }
        }

        if ($a_entity == "usr_setting") {
            switch ($a_version) {
                case "4.3.0":
                case "4.5.0":
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    return [
                        "UserId" => "integer",
                        "Keyword" => "text",
                        "Value" => "text"
                    ];
            }
        }

        if ($this->export_config->getExportType() == "personal_data") {
            switch ($a_version) {
                case "4.3.0":
                case "4.5.0":
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    return [
                        "Id" => "integer"
                    ];
            }
        }

        if ($a_entity == "usr_multi") {
            switch ($a_version) {
                case "4.5.0":
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    return [
                        "UserId" => "integer",
                        "FieldId" => "text",
                        "Value" => "text"
                    ];
            }
        }
        return [];
    }

    public function getXmlRecord(string $a_entity, string $a_version, array $a_set): array // Missing array type.
    {
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

    public function afterXmlRecordWriting(string $a_entity, string $a_version, array $a_set): void // Missing array type.
    {
        if ($a_entity == "usr_profile") {
            // cleanup temp dirs for pictures
            $tmp_dir = $this->temp_picture_dirs[$a_set["Id"]];
            if ($tmp_dir != "" && is_dir($tmp_dir)) {
                ilFileUtils::delDir($tmp_dir);
            }
        }
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void // Missing array type.
    {
        if (!is_array($a_ids)) {
            $a_ids = [$a_ids];
        }

        if ($this->export_config->getExportType() == "personal_data") {
            switch ($a_version) {
                case "4.3.0":
                case "4.5.0":
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    $this->data = [];
                    foreach ($a_ids as $id) {
                        $this->data[] = ["Id" => $id];
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
                        " title, birthday, gender, institution, department, street, city, zipcode, country, " .
                        " phone_office, phone_home, phone_mobile, fax, email, hobby, referral_comment, matriculation, " .
                        " delicious, latitude, longitude, loc_zoom" .
                        " FROM usr_data u " .
                        "WHERE " .
                        $this->db->in("u.usr_id", $a_ids, false, "integer"));
                    break;

                case "5.2.0":
                    $this->getDirectDataFromQuery("SELECT usr_id id, login username, firstname, lastname, " .
                        " title, birthday, gender, institution, department, street, city, zipcode, country, " .
                        " phone_office, phone_home, phone_mobile, fax, email, hobby, referral_comment, matriculation, " .
                        " latitude, longitude, loc_zoom" .
                        " FROM usr_data u " .
                        "WHERE " .
                        $this->db->in("u.usr_id", $a_ids, false, "integer"));
                    break;
                case "5.3.0":
                    $this->getDirectDataFromQuery("SELECT usr_id id, login username, firstname, lastname, " .
                        " title, birthday, gender, institution, department, street, city, zipcode, country, " .
                        " phone_office, phone_home, phone_mobile, fax, email, second_email, hobby, referral_comment, matriculation, " .
                        " latitude, longitude, loc_zoom" .
                        " FROM usr_data u " .
                        "WHERE " .
                        $this->db->in("u.usr_id", $a_ids, false, "integer"));
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
                    $prefs = ["date_format", "day_end", "day_start", "bs_allow_to_contact_me", "chat_osc_accept_msg", "hide_own_online_status", "language",
                        "public_birthday", "puplic_city", "public_country", "public_delicious", "public_department", "public_email", "public_second_email",
                        "public_fax", "public_gender", "public_hobby", "public_institution", "public_location",
                        "public_matriculation", "public_phone_home", "public_phone_mobile", "public_phone_office",
                        "public_profile", "public_country", "public_street", "public_title", "public_avatar", "public_zipcode",
                        "screen_reader_optimization", "show_users_online",
                        "store_last_visited", "time_format", "user_tz", "weekstart",
                        "session_reminder_lead_time", "usr_starting_point", "usr_starting_point_ref_id",
                        "chat_broadcast_typing"];

                    if (version_compare($a_version, '5.2.0', '>=')) {
                        unset(
                            $prefs['public_im_aim'], $prefs['public_im_icq'], $prefs['public_im_jabber'],
                            $prefs['public_im_msn'], $prefs['public_im_skype'], $prefs['public_im_voip'],
                            $prefs['public_im_yahoo'], $prefs['public_delicious']
                        );
                    }

                    $this->data = [];
                    $set = $this->db->query("SELECT * FROM usr_pref " .
                        " WHERE " . $this->db->in("keyword", $prefs, false, "text") .
                        " AND " . $this->db->in("usr_id", $a_ids, false, "integer"));
                    while ($rec = $this->db->fetchAssoc($set)) {
                        $this->data[] = ["UserId" => $rec["usr_id"], "Keyword" => $rec["keyword"], "Value" => $rec["value"]];
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
                    $this->data = [];
                    $set = $this->db->query("SELECT * FROM usr_profile_data" .
                        " WHERE " . $this->db->in("usr_id", $a_ids, false, "integer"));
                    while ($rec = $this->db->fetchAssoc($set)) {
                        $this->data[] = ["UserId" => $rec["usr_id"], "FieldId" => $rec["field_id"], "Value" => $rec["value"]];
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
    ): void {
        switch ($a_entity) {
            case "usr":
                // only users themselves import their profiles!
                // thus we can map the import id of the dataset to the current user
                $a_mapping->addMapping(
                    'components/ILIAS/User',
                    'usr',
                    $a_rec['Id'],
                    (string) $this->current_user->getId()
                );
                break;

            case "usr_profile":
                $usr_id = (int) $a_mapping->getMapping("components/ILIAS/User", "usr", $a_rec["Id"]);
                if ($usr_id > 0 && ilObject::_lookupType($usr_id) == "usr") {
                    if (!isset($this->users[$usr_id])) {
                        $this->users[$usr_id] = new ilObjUser($usr_id);
                    }
                    $user = array_reduce(
                        $this->user_profile->getFields([], [Alias::class, Roles::class]),
                        function (\ilObjUser $c, ProfileField $v) use ($a_rec): \ilObjUser {
                            $array_key = $this->buildProfileFieldArrayKeyFromIdentifier($v->getIdentifier());
                            if (!$this->user_profile->userFieldEditableByUser($v->getIdentifier())
                                || !isset($a_rec[$array_key])) {
                                return $c;
                            }
                            return $v->addValueToUserObject(
                                $c,
                                Context::UserAdministration,
                                $this->retrieveValueFromImportArray(
                                    $a_rec,
                                    $array_key
                                )
                            );
                        },
                        $this->users[$usr_id]
                    );

                    $user->update();
                }
                break;

            case "usr_setting":
                $usr_id = (int) $a_mapping->getMapping("components/ILIAS/User", "usr", $a_rec["UserId"]);
                if ($usr_id > 0 && ilObject::_lookupType($usr_id) === "usr") {
                    if (!isset($this->users[$usr_id])) {
                        $this->users[$usr_id] = new ilObjUser($usr_id);
                    }
                    $user = $this->users[$usr_id];
                    $user->writePref($a_rec["Keyword"], ilUtil::secureString($a_rec["Value"]));
                }
                break;

            case "usr_multi":
                $usr_id = (int) $a_mapping->getMapping("components/ILIAS/User", "usr", $a_rec["UserId"]);
                if ($usr_id > 0 && ilObject::_lookupType($usr_id) === "usr") {
                    $this->multi[$usr_id][$a_rec["FieldId"]][] = ilUtil::secureString($a_rec["Value"]);
                }
                break;
        }
    }

    private function buildProfileFieldArrayKeyFromIdentifier(
        string $identifier
    ): string {
        return $this->convertToLeadingUpper(
            match ($identifier) {
                'avatar' => 'picture',
                'location' => 'latitude',
                default => $identifier
            }
        );
    }

    private function retrieveValueFromImportArray(
        array $import_array,
        string $key
    ): mixed {
        return match($key) {
            'Latitude' => [
                'latitude' => $import_array['Latitude'],
                'longitude' => $import_array['Longitude'],
                'zoom' => (int) $import_array['LocZoom']
            ],
            'Picture' => [
                'tmp_name' => $this->buildFileUploadDir(
                    $import_array[$key],
                    $import_array['Id']
                ),
                'alias' => $import_array['Username']
            ],
            default => ilUtil::secureString($import_array[$key])
        };
    }

    private function buildFileUploadDir(
        string $rel_path,
        string $usr_id
    ): string {
        $pic_dir = "{$this->getImportDirectory()}/"
            . str_replace('..', '', $rel_path);
        if ($pic_dir === '' || !is_dir($pic_dir)) {
            return '';
        }

        $upload_file = "{$pic_dir}/usr_{$usr_id}.jpg";
        if (!is_file($upload_file)) {
            return '';
        }

        return $upload_file;
    }
}
