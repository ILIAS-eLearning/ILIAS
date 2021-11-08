<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Profile prompt data gateway
 *
 * @author killing@leifos.de
 * @ingroup ServicesUser
 */
class ilUserProfilePromptDataGateway
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilSetting
     */
    protected $user_settings;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->user_settings = new ilSetting("user");
        $this->lng = $DIC->language();
        $this->db = $DIC->database();
    }

    /**
     * Save settings
     *
     * @param ilProfilePromptSettings $settings
     */
    public function saveSettings(ilProfilePromptSettings $settings)
    {
        $user_settings = $this->user_settings;

        foreach ($settings->getInfoTexts() as $l => $text) {
            $user_settings->set("user_profile_info_" . $l, $text);
        }
        foreach ($settings->getPromptTexts() as $l => $text) {
            $user_settings->set("user_profile_prompt_" . $l, $text);
        }

        $user_settings->set("user_profile_prompt_mode", (int) $settings->getMode());
        $user_settings->set("user_profile_prompt_days", (int) $settings->getDays());
    }

    /**
     * Get settings
     *
     * @return ilProfilePromptSettings
     */
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

    /**
     * Get user prompt data
     *
     * @param $user_id
     * @return ilProfileUserPrompt
     */
    public function getUserPrompt($user_id) : ilProfileUserPrompt
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

    /**
     * Save user prompt
     *
     * @param int $user_id
     * @param string $last_profile_prompt
     */
    public function saveLastUserPrompt(int $user_id, string $last_profile_prompt = "")
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
