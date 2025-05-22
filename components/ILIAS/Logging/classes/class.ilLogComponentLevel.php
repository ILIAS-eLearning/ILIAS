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
class ilLogComponentLevel
{
    private string $compontent_id = '';
    private ?int $component_level = null;

    protected ilDBInterface $db;

    public function __construct(string $a_component_id, ?int $a_level = null)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->compontent_id = $a_component_id;
        if ($a_level === null) {
            $this->read();
        } else {
            $this->setLevel($a_level);
        }
    }

    public function getComponentId(): string
    {
        return $this->compontent_id;
    }

    public function setLevel(?int $a_level): void
    {
        $this->component_level = $a_level;
    }

    public function getLevel(): ?int
    {
        return $this->component_level;
    }

    public function update(): void
    {
        $this->db->replace(
            'log_components',
            array('component_id' => array('text',$this->getComponentId())),
            array('log_level' => array('integer',$this->getLevel()))
        );
    }

    public function read(): void
    {
        $query = 'SELECT * FROM log_components ' .
                'WHERE component_id = ' . $this->db->quote($this->getComponentId(), 'text');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->component_level = (int) $row->log_level;
        }
    }
}
