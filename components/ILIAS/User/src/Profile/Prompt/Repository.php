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

namespace ILIAS\User\Profile\Prompt;

class Repository
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private \ilDBInterface $db,
        private readonly \ilLanguage $lng,
        private readonly \ilSetting $user_settings
    ) {
    }

    public function getSettings(): Settings
    {
        $info_texts = $prompt_texts = [];
        foreach ($this->lng->getInstalledLanguages() as $lang) {
            $info_texts[$lang] = $this->user_settings->get('user_profile_info_' . $lang);
            $prompt_texts[$lang] = $this->user_settings->get('user_profile_prompt_' . $lang);
        }

        $prompt_days = $this->user_settings->get('user_profile_prompt_days');
        if ($prompt_days !== null) {
            $prompt_days = (int) $prompt_days;
        }

        return new Settings(
            (int) $this->user_settings->get('user_profile_prompt_mode'),
            $prompt_days,
            $info_texts,
            $prompt_texts
        );
    }

    public function saveSettings(Settings $settings): void
    {
        foreach ($this->lng->getInstalledLanguages() as $lang) {
            $this->updateText($lang, $settings->getInfoTexts(), 'user_profile_info');
            $this->updateText($lang, $settings->getPromptTexts(), 'user_profile_prompt');
        }

        $this->user_settings->set('user_profile_prompt_mode', (string) $settings->getMode());
        $this->updateDaysSetting($settings->getDays());
    }

    public function getUserPrompt(int $user_id): Prompt
    {
        $set = $this->db->queryF(
            'SELECT first_login, last_profile_prompt FROM usr_data ' .
            ' WHERE usr_id = %s ',
            ['integer'],
            [$user_id]
        );
        $rec = $this->db->fetchAssoc($set);
        return new Prompt(
            $user_id,
            $rec['last_profile_prompt'] === null
                ? null
                : new \DateTimeImmutable($rec['last_profile_prompt']),
            $rec['first_login'] === null
                ? null
                : new \DateTimeImmutable($rec['first_login'])
        );
    }

    public function updateLastUserPrompt(int $user_id): void
    {
        $this->db->update(
            'usr_data',
            [
                'last_profile_prompt' => ['timestamp', date(self::DATE_FORMAT)]
            ],
            [
                'usr_id' => ['integer', $user_id]
            ]
        );
    }

    private function updateText(
        string $lang,
        array $texts_array,
        string $variable_prefix
    ): void {
        if (!array_key_exists($lang, $texts_array)) {
            $this->user_settings->delete("{$variable_prefix}_{$lang}");
            return;
        }
        $this->user_settings->set("{$variable_prefix}_{$lang}", $texts_array[$lang]);
    }

    private function updateDaysSetting(
        ?int $days
    ): void {
        if ($days === null) {
            $this->user_settings->delete('user_profile_prompt_days');
            return;
        }

        $this->user_settings->set(
            'user_profile_prompt_days',
            (string) $days
        );
    }
}
