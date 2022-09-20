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
 * News cache
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsCache extends ilCache
{
    protected ilSetting $settings;
    public static bool $disabled = false;

    public function __construct()
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $news_set = new ilSetting("news");

        parent::__construct("ServicesNews", "News", true);
        $this->setExpiresAfter($news_set->get("acc_cache_mins") * 60);
        if ((int) $news_set->get("acc_cache_mins") === 0) {
            self::$disabled = true;
        }
    }

    public function isDisabled(): bool
    {
        return self::$disabled || parent::isDisabled();
    }

    protected function readEntry(string $a_id): bool
    {
        if (!$this->isDisabled()) {
            return parent::readEntry($a_id);
        }
        return false;
    }

    /**
     * Id is user_id:ref_id, we store ref_if additionally
     */
    public function storeEntry(
        string $a_id,
        string $a_value,
        ?int $a_int_key1 = null,
        ?int $a_int_key2 = null,
        ?string $a_text_key1 = null,
        ?string $a_text_key2 = null
    ): void {
        if (!$this->isDisabled()) {
            parent::storeEntry($a_id, $a_value);
        }
    }
}
