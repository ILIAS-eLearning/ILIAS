<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

require_once('ilDatabaseImplementationBaseTest.php');

/**
 * TestCase for the ilDatabasePDOGaleraTest
 *
 * @group   needsInstalledILIAS
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDatabasePDOGaleraTest extends ilDatabaseImplementationBaseTest
{

    /**
     * @return \ilDBPdoMySQLGalera
     * @throws \ilDatabaseException
     */
    protected function getDBInstance()
    {
        require_once('./Services/Database/classes/PDO/class.ilDBPdoMySQLGalera.php');

        return new ilDBPdoMySQLGalera();
    }


    /**
     * @return string
     */
    protected function getTableName()
    {
        return strtolower(self::TABLE_NAME . '_pdo_g');
    }
}
