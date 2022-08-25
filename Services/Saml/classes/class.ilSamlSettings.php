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
 * Class ilSamlSettings
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilSamlSettings
{
    private static ?self $instance = null;
    private ilSetting $settings;

    private function __construct()
    {
        $this->settings = new ilSetting('auth_saml');
    }

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function isDisplayedOnLoginPage(): bool
    {
        return (bool) $this->settings->get('login_form', '0');
    }

    public function setLoginFormStatus(bool $displayed_on_login_page): void
    {
        $this->settings->set('login_form', (string) ((int) $displayed_on_login_page));
    }
}
