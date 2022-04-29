<?php declare(strict_types=1);
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
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDSubstitution
{
    private static ?array $instances = null;
    private static ?array $mappings = null;

    protected ilDBInterface $db;

    protected string $type;
    protected array $substitutions;
    protected array $bold = array();
    protected array $newline = array();

    protected bool $enabled_desc = true;
    protected bool $enabled_field_names = true;
    protected bool $active = false;
    protected array $date_fields = array();
    protected array $datetime_fields = array();
    protected array $active_fields = array();

    private function __construct(string $a_type)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->type = $a_type;

        $this->initECSMappings();
        $this->read();
    }

    /**
     */
    public static function _getInstanceByObjectType(string $a_type) : ilAdvancedMDSubstitution
    {
        if (isset(self::$instances[$a_type])) {
            return self::$instances[$a_type];
        }
        return self::$instances[$a_type] = new ilAdvancedMDSubstitution($a_type);
    }

    /**
     * Sort definitions
     */
    public function sortDefinitions(array $a_definitions) : array
    {
        $sorted = array();
        foreach ($this->substitutions as $field_id) {
            if (isset($a_definitions[$field_id])) {
                $sorted[$field_id] = $a_definitions[$field_id];
                unset($a_definitions[$field_id]);
            }
        }
        return array_merge($sorted, $a_definitions);
    }

    public function isActive() : bool
    {
        return $this->active;
    }

    public function isDescriptionEnabled() : bool
    {
        return $this->enabled_desc;
    }

    public function enableDescription(bool $a_status) : void
    {
        $this->enabled_desc = $a_status;
    }

    public function enabledFieldNames() : bool
    {
        return $this->enabled_field_names;
    }

    public function enableFieldNames(bool $a_status) : void
    {
        $this->enabled_field_names = $a_status;
    }

    public function getParsedSubstitutions(int $a_ref_id, int $a_obj_id) : array
    {
        if (!count($this->getSubstitutions())) {
            return array();
        }

        $values_records = ilAdvancedMDValues::preloadedRead($this->type, $a_obj_id);

        $counter = 0;
        $substituted = [];
        foreach ($this->getSubstitutions() as $field_id) {
            if (!isset($this->active_fields[$field_id])) {
                continue;
            }

            $value = $this->parseValue($field_id, $values_records);

            if ($value === null) {
                if ($this->hasNewline($field_id) and $counter) {
                    $substituted[$counter - 1]['newline'] = true;
                }
                continue;
            }

            $substituted[$counter]['name'] = $this->active_fields[$field_id];
            $substituted[$counter]['value'] = $value;
            $substituted[$counter]['bold'] = $this->isBold($field_id);
            if ($this->hasNewline($field_id)) {
                $substituted[$counter]['newline'] = true;
            } else {
                $substituted[$counter]['newline'] = false;
            }
            $substituted[$counter]['show_field'] = $this->enabledFieldNames();
            $counter++;
        }
        return $substituted;
    }

    private function parseValue(int $a_field_id, array $a_values_records) : ?string
    {
        foreach ($a_values_records as $a_values) {
            if ($a_values->getADTGroup()->hasElement((string) $a_field_id)) {
                $element = $a_values->getADTGroup()->getElement((string) $a_field_id);
                if (!$element->isNull()) {
                    return ilADTFactory::getInstance()->getPresentationBridgeForInstance($element)->getList();
                }
            }
        }
        return null;
    }

    public function resetSubstitutions() : void
    {
        $this->substitutions = array();
        $this->bold = array();
        $this->newline = array();
    }

    /**
     * append field to substitutions
     * @access public
     * @param int field id
     */
    public function appendSubstitution(int $a_field_id, bool $a_bold = false, bool $a_newline = false) : void
    {
        $this->substitutions[] = $a_field_id;
        if ($a_bold) {
            $this->bold[] = $a_field_id;
        }
        if ($a_newline) {
            $this->newline[] = $a_field_id;
        }
    }

    public function getSubstitutions() : array
    {
        return !$this->substitutions ? array() : $this->substitutions;
    }

    public function isSubstituted(int $a_field_id) : bool
    {
        return in_array($a_field_id, $this->getSubstitutions());
    }

    public function isBold(int $a_field_id) : bool
    {
        return in_array($a_field_id, $this->bold);
    }

    public function hasNewline(int $a_field_id) : bool
    {
        return in_array($a_field_id, $this->newline);
    }

    /**
     * update
     * @access public
     */
    public function update() : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $counter = 0;
        $substitutions = array();

        foreach ($this->substitutions as $field_id) {
            $substitutions[$counter]['field_id'] = $field_id;
            $substitutions[$counter]['bold'] = $this->isBold($field_id);
            $substitutions[$counter]['newline'] = $this->hasNewline($field_id);
            $counter++;
        }

        $query = "DELETE FROM adv_md_substitutions WHERE obj_type = " . $ilDB->quote($this->type, 'text');
        $res = $ilDB->manipulate($query);

        $values = array(
            'obj_type' => array('text', $this->type),
            'substitution' => array('clob', serialize($substitutions)),
            'hide_description' => array('integer', !$this->isDescriptionEnabled()),
            'hide_field_names' => array('integer', !$this->enabledFieldNames())
        );
        $ilDB->insert('adv_md_substitutions', $values);
    }

    /**
     * Read db entries
     * @access private
     */
    private function read() : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Check active status
        $query = "SELECT active,field_id,amfd.title FROM adv_md_record amr " .
            "JOIN adv_md_record_objs amro ON amr.record_id = amro.record_id " .
            "JOIN adv_mdf_definition amfd ON amr.record_id = amfd.record_id " .
            "WHERE active = 1 " .
            "AND obj_type = " . $this->db->quote($this->type, 'text') . " ";
        $res = $this->db->query($query);
        $this->active = $res->numRows() ? true : false;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->active_fields[(int) $row->field_id] = (string) $row->title;
        }

        $query = "SELECT * FROM adv_md_substitutions " .
            "WHERE obj_type = " . $this->db->quote($this->type, 'text') . " ";
        $res = $this->db->query($query);
        $this->substitutions = array();
        $this->bold = array();
        $this->newline = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $tmp_substitutions = unserialize((string) $row->substitution);
            if (is_array($tmp_substitutions)) {
                foreach ($tmp_substitutions as $substitution) {
                    if ($substitution['field_id']) {
                        $this->substitutions[] = $substitution['field_id'];
                    }
                    if ($substitution['bold']) {
                        $this->bold[] = $substitution['field_id'];
                    }
                    if ($substitution['newline']) {
                        $this->newline[] = $substitution['field_id'];
                    }
                }
            }
            $this->enabled_desc = !$row->hide_description;
            $this->enabled_field_names = !$row->hide_field_names;
        }
    }

    private function initECSMappings() : void
    {
    }
}
