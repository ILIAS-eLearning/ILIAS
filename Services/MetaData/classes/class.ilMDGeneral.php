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
 * Meta Data class (element general)
 * @author  Stefan Meyer <meyer@leifos.com>
 * @package ilias-core
 * @version $Id$
 */
class ilMDGeneral extends ilMDBase
{
    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    private const STRUCTURE_TRANSLATION = [
        'atomic' => 'Atomic',
        'collection' => 'Collection',
        'networked' => 'Networked',
        'hierarchical' => 'Hierarchical',
        'linear' => 'Linear'
    ];

    protected ?ilMDLanguageItem $coverage_language = null;

    private string $coverage = '';
    private string $structure = '';
    private string $title = '';
    private ?ilMDLanguageItem $title_language = null;

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    private int $coverage_id = 0;

    /**
     * @return array<string, string>
     */
    public function getPossibleSubelements(): array
    {
        $subs['Keyword'] = 'meta_keyword';
        $subs['Language'] = 'meta_language';
        $subs['Identifier'] = 'meta_identifier';
        $subs['Description'] = 'meta_description';

        return $subs;
    }

    // Subelements (Identifier, Language, Description, Keyword)

    /**
     * @return int[]
     */
    public function getIdentifierIds(): array
    {
        return ilMDIdentifier::_getIds($this->getRBACId(), $this->getObjId(), (int) $this->getMetaId(), 'meta_general');
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
        $ide->setParentType('meta_general');

        return $ide;
    }

    /**
     * @return int[]
     */
    public function getLanguageIds(): array
    {
        return ilMDLanguage::_getIds($this->getRBACId(), $this->getObjId(), (int) $this->getMetaId(), 'meta_general');
    }

    public function getLanguage(int $a_language_id): ?ilMDLanguage
    {
        if (!$a_language_id) {
            return null;
        }
        $lan = new ilMDLanguage();
        $lan->setMetaId($a_language_id);

        return $lan;
    }

    public function addLanguage(): ilMDLanguage
    {
        $lan = new ilMDLanguage($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $lan->setParentId($this->getMetaId());
        $lan->setParentType('meta_general');

        return $lan;
    }

    /**
     * @return int[]
     */
    public function getDescriptionIds(): array
    {
        return ilMDDescription::_getIds($this->getRBACId(), $this->getObjId(), (int) $this->getMetaId(), 'meta_general');
    }

    public function getDescription(int $a_description_id): ?ilMDDescription
    {
        if (!$a_description_id) {
            return null;
        }
        $des = new ilMDDescription();
        $des->setMetaId($a_description_id);

        return $des;
    }

    public function addDescription(): ilMDDescription
    {
        $des = new ilMDDescription($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $des->setParentId($this->getMetaId());
        $des->setParentType('meta_general');

        return $des;
    }

    /**
     * @return int[]
     */
    public function getKeywordIds(): array
    {
        return ilMDKeyword::_getIds($this->getRBACId(), $this->getObjId(), (int) $this->getMetaId(), 'meta_general');
    }

    public function getKeyword(int $a_keyword_id): ?ilMDKeyword
    {
        if (!$a_keyword_id) {
            return null;
        }
        $key = new ilMDKeyword();
        $key->setMetaId($a_keyword_id);

        return $key;
    }

    public function addKeyword(): ilMDKeyword
    {
        $key = new ilMDKeyword($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $key->setParentId($this->getMetaId());
        $key->setParentType('meta_general');

        return $key;
    }

    // SET/GET
    public function setStructure(string $a_structure): bool
    {
        switch ($a_structure) {
            case 'Atomic':
            case 'Collection':
            case 'Networked':
            case 'Hierarchical':
            case 'Linear':
                $this->structure = $a_structure;
                return true;

            default:
                return false;
        }
    }

    public function getStructure(): string
    {
        return $this->structure;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitleLanguage(ilMDLanguageItem $lng_obj): void
    {
        $this->title_language = $lng_obj;
    }

    public function getTitleLanguage(): ?ilMDLanguageItem
    {
        return is_object($this->title_language) ? $this->title_language : null;
    }

    public function getTitleLanguageCode(): string
    {
        return is_object($this->title_language) ? $this->title_language->getLanguageCode() : '';
    }

    public function setCoverage(string $a_coverage): void
    {
        $this->coverage = $a_coverage;
    }

    public function getCoverage(): string
    {
        return $this->coverage;
    }

    public function setCoverageLanguage(ilMDLanguageItem $lng_obj): void
    {
        $this->coverage_language = $lng_obj;
    }

    public function getCoverageLanguage(): ?ilMDLanguageItem
    {
        return is_object($this->coverage_language) ? $this->coverage_language : null;
    }

    public function getCoverageLanguageCode(): string
    {
        return is_object($this->coverage_language) ? $this->coverage_language->getLanguageCode() : '';
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_general_id'] = array('integer', $next_id = $this->db->nextId('il_meta_general'));

        $this->log->debug("Insert General " . print_r($fields, true));
        $this->log->logStack(ilLogLevel::DEBUG);
        //ilUtil::printBacktrace(10);

        if ($this->db->insert('il_meta_general', $fields)) {
            $this->setMetaId($next_id);
            $this->createOrUpdateCoverage();
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        if (!$this->getMetaId()) {
            return false;
        }

        $this->createOrUpdateCoverage();

        return (bool) $this->db->update(
            'il_meta_general',
            $this->__getFields(),
            array("meta_general_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if (!$this->getMetaId()) {
            return false;
        }
        // Identifier
        foreach ($this->getIdentifierIds() as $id) {
            $ide = $this->getIdentifier($id);
            $ide->delete();
        }

        // Language
        foreach ($this->getLanguageIds() as $id) {
            $lan = $this->getLanguage($id);
            $lan->delete();
        }

        // Description
        foreach ($this->getDescriptionIds() as $id) {
            $des = $this->getDescription($id);
            $des->delete();
        }

        // Keyword
        foreach ($this->getKeywordIds() as $id) {
            $key = $this->getKeyword($id);
            $key->delete();
        }

        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_general " .
                "WHERE meta_general_id = " . $this->db->quote($this->getMetaId(), 'integer');
            $res = $this->db->manipulate($query);

            $this->deleteAllCoverages();
            return true;
        }

        return false;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function __getFields(): array
    {
        /**
         * Compatibility fix for legacy MD classes for new db tables
         */
        $structure = (string) array_search(
            $this->getStructure(),
            self::STRUCTURE_TRANSLATION
        );

        return array(
            'rbac_id' => array('integer', $this->getRBACId()),
            'obj_id' => array('integer', $this->getObjId()),
            'obj_type' => array('text', $this->getObjType()),
            'general_structure' => array('text', $structure),
            'title' => array('text', $this->getTitle()),
            'title_language' => array('text', $this->getTitleLanguageCode()),
            //'coverage' => array('text', $this->getCoverage()),
            //'coverage_language' => array('text', $this->getCoverageLanguageCode())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_general " .
                "WHERE meta_general_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                /**
                 * Compatibility fix for legacy MD classes for new db tables
                 */
                if (key_exists($row->general_structure ?? '', self::STRUCTURE_TRANSLATION)) {
                    $row->general_structure = self::STRUCTURE_TRANSLATION[$row->general_structure ?? ''];
                }

                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType((string) $row->obj_type);
                $this->setStructure((string) $row->general_structure);
                $this->setTitle((string) $row->title);
                $this->setTitleLanguage(new ilMDLanguageItem($row->title_language ?? ''));
                //$this->setCoverage((string) $row->coverage);
                //$this->setCoverageLanguage(new ilMDLanguageItem($row->coverage_language ?? ''));
            }

            $this->readFirstCoverage();
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        $writer->xmlStartTag('General', array(
            'Structure' => $this->getStructure() ?: 'Atomic'
        ));

        // Identifier
        $first = true;
        $identifiers = $this->getIdentifierIds();
        foreach ($identifiers as $id) {
            $ide = $this->getIdentifier($id);
            $ide->setExportMode($this->getExportMode());
            $ide->toXML($writer);
            $first = false;
        }
        if (!count($identifiers)) {
            $ide = new ilMDIdentifier(
                $this->getRBACId(),
                $this->getObjId(),
                $this->getObjType()
            );        // added type, alex, 31 Oct 2007
            $ide->setExportMode(true);
            $ide->toXML($writer);
        }

        // Title
        $writer->xmlElement(
            'Title',
            array(
                'Language' => $this->getTitleLanguageCode() ?: 'en'
            ),
            $this->getTitle()
        );

        // Language
        $languages = $this->getLanguageIds();
        foreach ($languages as $id) {
            $lan = $this->getLanguage($id);
            $lan->toXML($writer);
        }
        if (!count($languages)) {
            // Default

            $lan = new ilMDLanguage($this->getRBACId(), $this->getObjId());
            $lan->toXML($writer);
        }

        // Description
        $descriptions = $this->getDescriptionIds();
        foreach ($descriptions as $id) {
            $des = $this->getDescription($id);
            $des->toXML($writer);
        }
        if (!count($descriptions)) {
            // Default

            $des = new ilMDDescription($this->getRBACId(), $this->getObjId());
            $des->toXML($writer);
        }

        // Keyword
        $keywords = $this->getKeywordIds();
        foreach ($keywords as $id) {
            $key = $this->getKeyword($id);
            $key->toXML($writer);
        }
        if (!count($keywords)) {
            // Default

            $key = new ilMDKeyword($this->getRBACId(), $this->getObjId());
            $key->toXML($writer);
        }

        // Copverage
        if ($this->getCoverage() !== '') {
            $writer->xmlElement(
                'Coverage',
                array(
                    'Language' => $this->getCoverageLanguageCode() ?: 'en'
                ),
                $this->getCoverage()
            );
        }
        $writer->xmlEndTag('General');
    }

    // STATIC
    public static function _getId(int $a_rbac_id, int $a_obj_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_general_id FROM il_meta_general " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->meta_general_id;
        }
        return 0;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function createOrUpdateCoverage(): void
    {
        if ($this->getCoverage() === '' && $this->getCoverageLanguageCode() === '') {
            return;
        }

        if (!$this->getCoverageId()) {
            $this->db->insert(
                'il_meta_coverage',
                [
                    'meta_coverage_id' => ['integer', $next_id = $this->db->nextId('il_meta_coverage')],
                    'rbac_id' => ['integer', $this->getRBACId()],
                    'obj_id' => ['integer', $this->getObjId()],
                    'obj_type' => ['text', $this->getObjType()],
                    'parent_type' => ['text', 'meta_general'],
                    'parent_id' => ['integer', $this->getMetaId()],
                    'coverage' => ['text', $this->getCoverage()],
                    'coverage_language' => ['text', $this->getCoverageLanguageCode()]
                ]
            );
            $this->coverage_id = $next_id;
            return;
        }

        $this->db->update(
            'il_meta_coverage',
            [
                'coverage' => ['text', $this->getCoverage()],
                'coverage_language' => ['text', $this->getCoverageLanguageCode()]
            ],
            ['meta_coverage_id' => ['integer', $this->getCoverageId()]]
        );
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function deleteAllCoverages(): void
    {
        $query = "DELETE FROM il_meta_coverage WHERE parent_type = 'meta_general'
                AND parent_id = " . $this->db->quote($this->getMetaId(), 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function readFirstCoverage(): void
    {
        $query = "SELECT * FROM il_meta_coverage WHERE meta_coverage_id = " .
            $this->db->quote($this->getCoverageId(), 'integer');

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            $this->setCoverage((string) $row['coverage']);
            $this->setCoverageLanguage(new ilMDLanguageItem((string) $row['coverage_language']));
        }
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function getCoverageId(): int
    {
        return $this->coverage_id;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function readCoverageId(int $parent_id): void
    {
        $query = "SELECT meta_coverage_id FROM il_meta_coverage WHERE parent_type = 'meta_general'
                AND parent_id = " . $this->db->quote($parent_id, 'integer') .
            " ORDER BY meta_coverage_id";

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            $this->coverage_id = (int) $row['meta_coverage_id'];
        }
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    public function setMetaId(int $a_meta_id, bool $a_read_data = true): void
    {
        $this->readCoverageId($a_meta_id);
        parent::setMetaId($a_meta_id, $a_read_data);
    }
}
