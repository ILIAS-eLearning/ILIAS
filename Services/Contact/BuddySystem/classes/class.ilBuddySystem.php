<?php

declare(strict_types=1);

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
 * Class ilBuddySystem
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystem
{
    protected static ?self $instance = null;
    protected static ?bool $isEnabled = null;

    protected ilSetting $settings;
    protected ilObjUser $user;

    protected function __construct()
    {
        global $DIC;

        $this->settings = new ilSetting('buddysystem');
        $this->user = $DIC['ilUser'];
    }

    public static function getInstance(): self
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $keyword
     * @param string $value
     */
    public function setSetting(string $keyword, string $value): void
    {
        $this->settings->set($keyword, $value);
    }

    /**
     * @param string $keyword
     * @param string|null $default
     * @return string|null
     */
    public function getSetting(string $keyword, ?string $default = null): ?string
    {
        return $this->settings->get($keyword, $default);
    }

    public function isEnabled(): bool
    {
        if (self::$isEnabled !== null) {
            return self::$isEnabled;
        }

        if ($this->user->isAnonymous()) {
            self::$isEnabled = false;
            return false;
        }

        self::$isEnabled = (bool) $this->settings->get('enabled', '0');
        return self::$isEnabled;
    }
}
