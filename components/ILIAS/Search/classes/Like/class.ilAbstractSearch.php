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
* Class ilSearchGUI
*
* Base class for all search classes
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/

abstract class ilAbstractSearch
{
    protected ilDBInterface $db;
    protected ilQueryParser $query_parser;
    protected ilSearchResult $search_result;

    /**
     * @var string[]
     */
    protected array $object_types = [];

    /**
     * @var int[]
     */
    private array $id_filter = [];

    /**
     * @var string[]
     */
    private array $fields = [];



    public function __construct(ilQueryParser $qp_obj)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->query_parser = $qp_obj;
        $this->search_result = new ilSearchResult();

        $this->object_types = $this->getValidObjectTypes($DIC['objDefinition']);
    }

    protected function getValidObjectTypes(ilObjectDefinition $object_definition): array
    {
        $valid_types = [];
        foreach ($object_definition->getAllObjects() as $type) {
            if (
                !$object_definition->isSystemObject($type) &&
                !$object_definition->isAdministrationObject($type) &&
                !$object_definition->isSideBlock($type) &&
                $object_definition->isRBACObject($type) &&
                $object_definition->isAllowedInRepository($type)
            ) {
                $valid_types[] = $type;
            }
        }

        $grouped_types = [];
        foreach ($object_definition->getAllObjects() as $type) {
            if (in_array($object_definition->getGroupOfObj($type), $valid_types)) {
                $grouped_types[] = $type;
            }
        }

        return array_unique(array_merge($valid_types, $grouped_types));
    }

    public function setFields(array $a_fields): void
    {
        $this->fields = $a_fields;
    }

    /**
    * @return string[] array of search fields. E.g. array(title,description)
    */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFilter(array $a_filter): void
    {
        $this->object_types = $a_filter;
    }

    public function setIdFilter(array $a_id_filter): void
    {
        $this->id_filter = $a_id_filter;
    }

    /**
     * @return int[]
     */
    public function getIdFilter(): array
    {
        return $this->id_filter;
    }

    public function appendToFilter(string $a_type): void
    {
        if (!in_array($a_type, $this->object_types)) {
            $this->object_types[] = $a_type;
        }
    }


    /**
     * @param string[] Array of object types (e.g array('lm','st','pg','dbk'))
     */
    public function getFilter(): array
    {
        return $this->object_types;
    }

    public function __createLocateString(): string
    {
        if ($this->query_parser->getCombination() == ilQueryParser::QP_COMBINATION_OR) {
            return '';
        }
        if (count($this->fields) > 1) {
            $tmp_fields = [];
            foreach ($this->fields as $field) {
                $tmp_fields[] = array($field,'text');
            }
            $complete_str = $this->db->concat($tmp_fields);
        } else {
            $complete_str = $this->fields[0];
        }

        $counter = 0;
        $locate = '';
        foreach ($this->query_parser->getQuotedWords() as $word) {
            $word = str_replace('\%', '%', $word);
            $word = str_replace('\_', '_', $word);

            $locate .= ',';
            $locate .= $this->db->locate($this->db->quote($word, 'text'), $complete_str);
            $locate .= (' found' . $counter++);
            $locate .= ' ';
        }

        return $locate;
    }

    public function __prepareFound(object $row): array
    {
        if ($this->query_parser->getCombination() == 'or') {
            return array();
        }
        $counter = 0;
        $found = [];
        foreach ($this->query_parser->getQuotedWords() as $word) {
            $res_found = "found" . $counter++;
            $found[] = (int) $row->$res_found;
        }
        return $found;
    }

    abstract public function performSearch(): ?ilSearchResult;
}
