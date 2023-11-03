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
 * Description of class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCGroups
{
    private static ?ilSCGroups $instance = null;

    private array $groups = array();

    protected ilDBInterface $db;

    private function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->read();
    }

    public static function getInstance(): ilSCGroups
    {
        return self::$instance ?? (self::$instance = new self());
    }

    public function updateFromComponentDefinition(string $a_component_id): int
    {
        foreach ($this->getGroups() as $group) {
            if ($group->getComponentId() === $a_component_id) {
                return $group->getId();
            }
        }

        $component_group = new ilSCGroup();
        $component_group->setComponentId($a_component_id);
        $component_group->create();

        return $component_group->getId();
    }

    public function lookupGroupByComponentId(string $a_component_id): int
    {
        $query = 'SELECT id FROM sysc_groups ' .
            'WHERE component = ' . $this->db->quote($a_component_id, ilDBConstants::T_TEXT);
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->id;
        }
        return 0;
    }

    /**
     * @return ilSCGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    protected function read(): void
    {
        $query = 'SELECT id FROM sysc_groups ' .
            'ORDER BY id ';
        $res = $this->db->query($query);

        $this->groups = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->groups[] = new ilSCGroup((int) $row->id);
        }
    }
}
