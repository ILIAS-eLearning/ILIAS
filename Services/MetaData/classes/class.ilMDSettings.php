<?php

declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ilCtrl_Calls
 * @ingroup ServicesMetaData
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
