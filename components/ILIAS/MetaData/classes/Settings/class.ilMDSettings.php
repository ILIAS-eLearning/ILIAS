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

use ILIAS\MetaData\Settings\SettingsInterface;

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 */
class ilMDSettings implements SettingsInterface
{
    protected static ?self $instance = null;

    protected ilSetting $settings;
    private bool $copyright_selection_active = false;
    private bool $oai_pmh_active = false;
    private string $oai_repository_name = '';
    private string $oai_identifier_prefix = '';
    private string $oai_contact_mail = '';

    private function __construct()
    {
        $this->read();
    }

    public static function _getInstance(): ilMDSettings
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilMDSettings();
    }

    public function isCopyrightSelectionActive(): bool
    {
        return $this->copyright_selection_active;
    }

    public function activateCopyrightSelection(bool $status): void
    {
        $this->copyright_selection_active = $status;
        $this->settings->set('copyright_selection_active', (string) $status);
    }

    public function isOAIPMHActive(): bool
    {
        return $this->oai_pmh_active;
    }

    public function activateOAIPMH(bool $status): void
    {
        $this->oai_pmh_active = $status;
        $this->settings->set('oai_pmh_active', (string) $status);
    }

    public function getOAIRepositoryName(): string
    {
        return $this->oai_repository_name;
    }

    public function saveOAIRepositoryName(string $oai_repository_name): void
    {
        $this->oai_repository_name = $oai_repository_name;
        $this->settings->set('oai_repository_name', $oai_repository_name);
    }

    public function getOAIIdentifierPrefix(): string
    {
        return $this->oai_identifier_prefix;
    }

    public function saveOAIIdentifierPrefix(string $oai_identifier_prefix): void
    {
        $this->oai_identifier_prefix = $oai_identifier_prefix;
        $this->settings->set('oai_identifier_prefix', $oai_identifier_prefix);
    }

    public function getOAIContactMail(): string
    {
        return $this->oai_contact_mail;
    }

    public function saveOAIContactMail(string $oai_contact_mail): void
    {
        $this->oai_contact_mail = $oai_contact_mail;
        $this->settings->set('oai_contact_mail', $oai_contact_mail);
    }

    private function read(): void
    {
        $this->settings = new ilSetting('md_settings');

        $this->copyright_selection_active = (bool) $this->settings->get('copyright_selection_active', '0');
        $this->oai_pmh_active = (bool) $this->settings->get('oai_pmh_active', '0');
        $this->oai_repository_name = (string) $this->settings->get('oai_repository_name', '');
        $this->oai_identifier_prefix = (string) $this->settings->get('oai_identifier_prefix', '');
        $this->oai_contact_mail = (string) $this->settings->get('oai_contact_mail', '');
    }
}
