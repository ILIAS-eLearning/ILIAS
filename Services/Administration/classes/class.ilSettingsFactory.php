<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 2019 Richard Klees, ILIAS Open Source e.V.                    |
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
* A factory that builds ilSettings that can be used for DI.
*
* @author Richard Klees <richard.klees@concepts-and-training.de>

* @version $Id$
*/
class ilSettingsFactory
{
    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Get currernt module
     */
    public function settingsFor(string $a_module = "common")
    {
        $tmp_dic = $GLOBALS["DIC"] ?? null;
        try {
            // ilSetting pulls the database once in the constructor, we force it to
            // use ours.
            $DIC = new ILIAS\DI\Container;
            $DIC["ilDB"] = $this->db;
            $DIC["ilBench"] = null;
            $GLOBALS["DIC"] = $DIC;

            // Always load from db, as caching could be implemented as a
            // decorator to this.
            $settings = new ilSetting($a_module, true);

            // Provoke a setting to populate the value_type in ilSettings,
            // use a field that is likely to exist.
            $settings->set(
                "common",
                "system_user_id",
                $settings->get("common", "system_user_id")
            );
        } finally {
            $GLOBALS["DIC"] = $tmp_dic;
        }

        return $settings;
    }
}
