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
 * individual log levels for components
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 */
class ilLogComponentLevels
{
    protected static ?ilLogComponentLevels $instance = null;
    /**
     * @var ilLogComponentLevel[]
     */
    protected array $components = array();

    protected ilDBInterface $db;

    /**
     * constructor
     */
    protected function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->read();
    }

    public static function getInstance(): ilLogComponentLevels
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param string $a_component_id
     */
    public static function updateFromXML($a_component_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        if (!$a_component_id) {
            return false;
        }

        $query = 'SELECT * FROM log_components ' .
                'WHERE component_id = ' . $ilDB->quote($a_component_id, 'text');
        $res = $ilDB->query($query);
        if (!$res->numRows()) {
            $query = 'INSERT INTO log_components (component_id) ' .
                    'VALUES (' .
                    $ilDB->quote($a_component_id, 'text') .
                    ')';
            $ilDB->manipulate($query);
        }
        return true;
    }

    /**
     * Get component levels
     * @return ilLogComponentLevel[]
     */
    public function getLogComponents(): array
    {
        return $this->components;
    }

    public function read(): void
    {
        $query = 'SELECT * FROM log_components ';
        $res = $this->db->query($query);

        $this->components = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->components[] = new ilLogComponentLevel((string) $row->component_id, (int) $row->log_level);
        }
    }
}
