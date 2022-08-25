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
 * Class ilDBWrapperFactory
 *
 * DB Wrapper Factory. Delivers a DB wrapper object depending on given
 * DB type and DSN.
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 *
 * @ingroup ServicesDatabase
 */
class ilDBWrapperFactory
{
    /**
     * @param string $a_type
     * @return ilDBPdoInterface
     * @throws ilDatabaseException
     */
    public static function getWrapper(string $a_type): \ilDBPdoInterface
    {
        switch ($a_type) {
            case 'pdo-mysql-innodb':
            case ilDBConstants::TYPE_INNODB:
                $ilDB = new ilDBPdoMySQLInnoDB();
                break;
            case ilDBConstants::TYPE_GALERA:
                $ilDB = new ilDBPdoMySQLGalera();
                break;
            default:
                throw new ilDatabaseException("No viable database-type given: " . var_export($a_type, true));
        }

        return $ilDB;
    }
}
