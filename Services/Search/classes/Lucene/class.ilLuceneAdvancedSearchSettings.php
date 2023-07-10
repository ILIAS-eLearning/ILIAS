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
* En/disable single lom/advanced meta data fields
*
* @author Stefan Meyer <meyer@leifos.com>
* @ingroup ServicesSearch
*/
class ilLuceneAdvancedSearchSettings
{
    private static ?ilLuceneAdvancedSearchSettings $instance = null;
    private array $fields = [];

    protected ilSetting $storage;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->storage = new ilSetting('lucene_adv_search');
        $this->read();
    }

    public static function getInstance(): ilLuceneAdvancedSearchSettings
    {
        if (self::$instance instanceof ilLuceneAdvancedSearchSettings) {
            return self::$instance;
        }
        return self::$instance = new ilLuceneAdvancedSearchSettings();
    }

    /**
     * check if field is active
     */
    public function isActive(string $a_field): bool
    {
        return $this->fields[$a_field] ?: false;
    }

    public function setActive(string $a_field, bool $a_status): void
    {
        $this->fields[$a_field] = $a_status;
    }

    public function save(): void
    {
        foreach ($this->fields as $name => $status) {
            $this->storage->set($name, $status ? "1" : "0");
        }
    }

    private function read(): void
    {
        foreach (ilLuceneAdvancedSearchFields::getFields() as $name => $translation) {
            $this->fields[$name] = (bool) $this->storage->get($name, 'true');
        }
    }
}
