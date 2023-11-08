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

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 */
class ilMDSettings
{
    protected static ?self $instance = null;

    protected ilSetting $settings;
    private bool $copyright_selection_active = false;
    private string $delimiter = '';

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

    public function activateCopyrightSelection(bool $a_status): void
    {
        $this->copyright_selection_active = $a_status;
    }

    public function setDelimiter(string $a_val): void
    {
        $this->delimiter = $a_val;
    }

    public function getDelimiter(): string
    {
        if (trim($this->delimiter) === '') {
            return ",";
        }
        return $this->delimiter;
    }

    public function save(): void
    {
        $this->settings->set('copyright_selection_active', (string) $this->isCopyrightSelectionActive());
        $this->settings->set('delimiter', $this->getDelimiter());
    }

    private function read(): void
    {
        $this->settings = new ilSetting('md_settings');

        $this->copyright_selection_active = (bool) $this->settings->get('copyright_selection_active', '0');
        $this->delimiter = (string) $this->settings->get('delimiter', ",");
    }
}
