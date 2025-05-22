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

namespace ILIAS\components\Authentication\Pages;

enum AuthPageEditorContext: string
{
    case LOGIN = 'login_editor';
    case LOGOUT = 'logout_editor';

    public function pageLanguageIdentifier(bool $plural = false): string
    {
        if ($this === self::LOGIN) {
            return $plural ? 'login_pages' : 'login_page';
        }

        return $plural ? 'logout_pages' : 'logout_page';
    }

    public function tabIdentifier(): string
    {
        return match ($this) {
            self::LOGIN => 'auth_login_editor',
            self::LOGOUT => 'logout_editor',
        };
    }

    public function pageType(): string
    {
        return match ($this) {
            self::LOGIN => \ilLoginPage::class::PAGE_TYPE,
            self::LOGOUT => \ilLogoutPage::class::PAGE_TYPE,
        };
    }

    /**
     * @return class-string<\ilPageObject>
     */
    public function pageClass(): string
    {
        return match ($this) {
            self::LOGIN => \ilLoginPage::class,
            self::LOGOUT => \ilLogoutPage::class,
        };
    }

    /**
     * @return class-string<\ilPageObjectGUI>
     */
    public function pageUiClass(): string
    {
        return match ($this) {
            self::LOGIN => \ilLoginPageGUI::class,
            self::LOGOUT => \ilLogoutPageGUI::class,
        };
    }
}
