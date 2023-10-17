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
 * Meta Data class (element meta_data)
 * @package ilias-core
 * @version $Id$
 */
class ilMDMetaMetadata extends ilMDBase
{
    private string $meta_data_scheme = 'LOM v 1.0';
    private ?ilMDLanguageItem $language = null;

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    private int $schema_id = 0;

    /**
     * @return array<string, string>
     */
    public function getPossibleSubelements(): array
    {
        $subs['Identifier'] = 'meta_identifier';
        $subs['Contribute'] = 'meta_contribute';

        return $subs;
    }

    // SUBELEMENTS

    /**
     * @return int[]
     */
    public function getIdentifierIds(): array
    {
        return ilMDIdentifier::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_meta_data');
    }

    public function getIdentifier(int $a_identifier_id): ?ilMDIdentifier
    {
        if (!$a_identifier_id) {
            return null;
        }
        $ide = new ilMDIdentifier();
        $ide->setMetaId($a_identifier_id);

        return $ide;
    }

    public function addIdentifier(): ilMDIdentifier
    {
        $ide = new ilMDIdentifier($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $ide->setParentId($this->getMetaId());
        $ide->setParentType('meta_meta_data');

        return $ide;
    }

    /**
     * @return int[]
     */
    public function getContributeIds(): array
    {
        return ilMDContribute::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_meta_data');
    }

    public function getContribute(int $a_contribute_id): ?ilMDContribute
    {
        if (!$a_contribute_id) {
            return null;
        }
        $con = new ilMDContribute();
        $con->setMetaId($a_contribute_id);

        return $con;
    }

    public function addContribute(): ilMDContribute
    {
        $con = new ilMDContribute($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $con->setParentId($this->getMetaId());
        $con->setParentType('meta_meta_data');

        return $con;
    }

    // SET/GET
    //TODO: check fixed attribute
    public function setMetaDataScheme(string $a_val): void
    {
        $this->meta_data_scheme = $a_val;
    }

    public function getMetaDataScheme(): string
    {
        // Fixed attribute
        return 'LOM v 1.0';
    }

    public function setLanguage(ilMDLanguageItem $lng_obj): void
    {
        $this->language = $lng_obj;
    }

    public function getLanguage(): ?ilMDLanguageItem
    {
        return is_object($this->language) ? $this->language : null;
    }

    public function getLanguageCode(): string
    {
        return is_object($this->language) ? $this->language->getLanguageCode() : '';
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_meta_data_id'] = array('integer', $next_id = $this->db->nextId('il_meta_meta_data'));

        if ($this->db->insert('il_meta_meta_data', $fields)) {
            $this->setMetaId($next_id);
            $this->createOrUpdateFirstSchema();
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        if (!$this->getMetaId()) {
            return false;
        }

        $this->createOrUpdateFirstSchema();

        return (bool) $this->db->update(
            'il_meta_meta_data',
            $this->__getFields(),
            array("meta_meta_data_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_meta_data " .
                "WHERE meta_meta_data_id = " . $this->db->quote($this->getMetaId(), 'integer');
            $res = $this->db->manipulate($query);

            $this->deleteAllSchemas();

            foreach ($this->getIdentifierIds() as $id) {
                $ide = $this->getIdentifier($id);
                $ide->delete();
            }

            foreach ($this->getContributeIds() as $id) {
                $con = $this->getContribute($id);
                $con->delete();
            }
            return true;
        }

        return false;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function __getFields(): array
    {
        return array(
            'rbac_id' => array('integer', $this->getRBACId()),
            'obj_id' => array('integer', $this->getObjId()),
            'obj_type' => array('text', $this->getObjType()),
            //'meta_data_scheme' => array('text', $this->getMetaDataScheme()),
            'language' => array('text', $this->getLanguageCode())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_meta_data " .
                "WHERE meta_meta_data_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                //$this->setMetaDataScheme($row->meta_data_scheme ?? '');
                $this->setLanguage(new ilMDLanguageItem($row->language ?? ''));
            }

            $this->readFirstSchema();

            return true;
        }
        return false;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        $attr = null;
        if ($this->getMetaDataScheme()) {
            $attr['MetadataScheme'] = $this->getMetaDataScheme();
        }
        if ($this->getLanguageCode()) {
            $attr['Language'] = $this->getLanguageCode();
        }
        $writer->xmlStartTag('Meta-Metadata', $attr);

        // ELEMENT IDENTIFIER
        $identifiers = $this->getIdentifierIds();
        foreach ($identifiers as $id) {
            $ide = $this->getIdentifier($id);
            $ide->toXML($writer);
        }
        if (!count($identifiers)) {
            $ide = new ilMDIdentifier($this->getRBACId(), $this->getObjId());
            $ide->toXML($writer);
        }

        // ELEMETN Contribute
        $contributes = $this->getContributeIds();
        foreach ($contributes as $id) {
            $con = $this->getContribute($id);
            $con->toXML($writer);
        }
        if (!count($contributes)) {
            $con = new ilMDContribute($this->getRBACId(), $this->getObjId());
            $con->toXML($writer);
        }

        $writer->xmlEndTag('Meta-Metadata');
    }

    // STATIC
    public static function _getId(int $a_rbac_id, int $a_obj_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_meta_data_id FROM il_meta_meta_data " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->meta_meta_data_id;
        }
        return 0;
    }


    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function createOrUpdateFirstSchema(): void
    {
        if ($this->getMetaDataScheme() === '') {
            return;
        }

        if (!$this->getSchemaId()) {
            $this->db->insert(
                'il_meta_meta_schema',
                [
                    'meta_meta_schema_id' => ['integer', $next_id = $this->db->nextId('il_meta_meta_schema')],
                    'rbac_id' => ['integer', $this->getRBACId()],
                    'obj_id' => ['integer', $this->getObjId()],
                    'obj_type' => ['text', $this->getObjType()],
                    'parent_type' => ['text', 'meta_general'],
                    'parent_id' => ['integer', $this->getMetaId()],
                    'meta_data_schema' => ['text', 'LOMv1.0'],
                ]
            );
            $this->schema_id = $next_id;
        }
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function deleteAllSchemas(): void
    {
        $query = "DELETE FROM il_meta_meta_schema WHERE parent_type = 'meta_meta_data'
                AND parent_id = " . $this->db->quote($this->getMetaId(), 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function readFirstSchema(): void
    {
        $query = "SELECT * FROM il_meta_meta_schema WHERE meta_meta_schema_id = " .
                $this->db->quote($this->getMetaId(), 'integer');

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            $this->setMetaDataScheme((string) $row['meta_data_schema']);
        }
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function getSchemaId(): int
    {
        return $this->schema_id;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function readSchemaId(int $parent_id): void
    {
        $query = "SELECT meta_meta_schema_id FROM il_meta_meta_schema WHERE parent_type = 'meta_meta_data'
                AND parent_id = " . $this->db->quote($parent_id, 'integer') .
            " ORDER BY meta_meta_schema_id";

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            $this->schema_id = (int) $row['meta_meta_schema_id'];
        }
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    public function setMetaId(int $a_meta_id, bool $a_read_data = true): void
    {
        $this->readSchemaId($a_meta_id);
        parent::setMetaId($a_meta_id, $a_read_data);
    }
}
