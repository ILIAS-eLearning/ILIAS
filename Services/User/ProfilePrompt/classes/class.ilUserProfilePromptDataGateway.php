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
 * Profile prompt data gateway
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserProfilePromptDataGateway
{
    protected ilLanguage $lng;
    protected ilSetting $user_settings;
    protected ilDBInterface $db;

    public function __construct()
    {
        global $DIC;

        $this->user_settings = new ilSetting("user");
        $this->lng = $DIC->language();
        $this->db = $DIC->database();
    }

    public function saveSettings(ilProfilePromptSettings $settings) : void
    {
        $user_settings = $this->user_settings;

        foreach ($settings->getInfoTexts() as $l => $text) {
            $user_settings->set("user_profile_info_" . $l, $text);
        }
        foreach ($settings->getPromptTexts() as $l => $text) {
            $user_settings->set("user_profile_prompt_" . $l, $text);
        }

        $user_settings->set("user_profile_prompt_mode", $settings->getMode());
        $user_settings->set("user_profile_prompt_days", $settings->getDays());
    }

    public function getSettings() : ilProfilePromptSettings
    {
        $user_settings = $this->user_settings;
        $lng = $this->lng;

        $info_texts = $prompt_texts = [];
        foreach ($lng->getInstalledLanguages() as $l) {
            $info_texts[$l] = $user_settings->get("user_profile_info_" . $l);
            $prompt_texts[$l] = $user_settings->get("user_profile_prompt_" . $l);
        }

        return new ilProfilePromptSettings(
            (int) $user_settings->get("user_profile_prompt_mode"),
            (int) $user_settings->get("user_profile_prompt_days"),
            $info_texts,
            $prompt_texts
        );
    }

    public function getUserPrompt(int $user_id) : ilProfileUserPrompt
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT first_login, last_profile_prompt FROM usr_data " .
            " WHERE usr_id = %s ",
            array("integer"),
            array($user_id)
        );
        if ($rec = $db->fetchAssoc($set)) {
            return new ilProfileUserPrompt($user_id, $rec["last_profile_prompt"], $rec["first_login"]);
        }
        return new ilProfileUserPrompt($user_id, "", "");
    }

    public function saveLastUserPrompt(int $user_id, string $last_profile_prompt = "") : void
    {
        $db = $this->db;

        if ($last_profile_prompt == "") {
            $last_profile_prompt = ilUtil::now();
        }

        $db->update("usr_data", array(
                "last_profile_prompt" => array("timestamp", $last_profile_prompt)
            ), array(	// where
                "usr_id" => array("integer", $user_id)
            ));
    }
}
