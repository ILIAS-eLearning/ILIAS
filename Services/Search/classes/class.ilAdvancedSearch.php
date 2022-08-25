<?php

declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilAdvancedSearch
*
* Base class for advanced meta search
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/


class ilAdvancedSearch extends ilAbstractSearch
{
    private string $mode = '';
    protected array $options = [];



    public function setMode(string $a_mode): void
    {
        $this->mode = $a_mode;
    }
    public function getMode(): string
    {
        return $this->mode;
    }

    public function setOptions(array &$options): void
    {
        $this->options = &$options;
    }

    public function performSearch(): ilSearchResult
    {
        switch ($this->getMode()) {
            case 'requirement':
                return $this->__searchRequirement();

            case 'educational':
                return $this->__searchEducational();

            case 'typical_age_range':
                return $this->__searchTypicalAgeRange();

            case 'rights':
                return $this->__searchRights();

            case 'classification':
                return $this->__searchClassification();

            case 'taxon':
                return $this->__searchTaxon();

            case 'keyword':
                return $this->__searchKeyword();

            case 'format':
                return $this->__searchFormat();

            case 'lifecycle':
                return $this->__searchLifecycle();

            case 'contribute':
                return $this->__searchContribute();

            case 'entity':
                return $this->__searchEntity();

            case 'general':
                return $this->__searchGeneral();

            case 'keyword_all':
                return $this->__searchKeyword(false);

            case 'title_description':
                return $this->__searchTitleDescription();

            case 'language':
                return $this->__searchLanguage();

            default:
                throw new InvalidArgumentException('ilMDSearch: no valid mode given');
        }
    }

    public function &__searchTitleDescription(): ilSearchResult
    {
        $this->setFields(array('title','description'));

        $and = ("AND type " . $this->__getInStatement($this->getFilter()));
        $where = $this->__createTitleDescriptionWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT obj_id,type " .
            $locate .
            "FROM object_data " .
            $where . " " . $and . ' ' .
            "ORDER BY obj_id DESC";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry((int) $row->obj_id, $row->type, $this->__prepareFound($row));
        }

        return $this->search_result;
    }

    public function __searchGeneral(): ?ilSearchResult
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$this->options['lom_coverage'] and !$this->options['lom_structure']) {
            return null;
        }
        $and = $locate = '';

        if ($this->options['lom_coverage']) {
            $this->setFields(array('coverage'));
            $and = $this->__createCoverageAndCondition();
            $locate = $this->__createLocateString();
        }
        if ($this->options['lom_structure']) {
            $and .= ("AND general_structure = " . $ilDB->quote($this->options['lom_structure'], ilDBConstants::T_TEXT) . " ");
        }

        $query = "SELECT rbac_id,obj_type,obj_id " .
            $locate . " " .
            "FROM il_meta_general " .
            "WHERE obj_type " . $this->__getInStatement($this->getFilter()) . " " .
            $and;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($this->options['lom_coverage']) {
                $found = $this->__prepareFound($row);
                if (!in_array(0, $found)) {
                    $this->search_result->addEntry($row->rbac_id, $row->obj_type, $found, $row->obj_id);
                }
            } else {
                $this->search_result->addEntry($row->rbac_id, $row->obj_type, array(), $row->obj_id);
            }
        }

        return $this->search_result;
    }

    public function __searchLanguage(): ?ilSearchResult
    {
        if (!$this->options['lom_language']) {
            return null;
        }

        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_language " .
            "WHERE language = " . $this->db->quote($this->options['lom_language'], 'text') . " " .
            "AND obj_type " . $this->__getInStatement($this->getFilter()) . ' ' .
            "AND parent_type = 'meta_general'";

        $res = $this->db->query($query);
        #var_dump("<pre>",$query,"<pre>");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->rbac_id, $row->obj_type, array(), $row->obj_id);
        }
        return $this->search_result;
    }

    public function __searchContribute(): ?ilSearchResult
    {
        if (!$this->options['lom_role']) {
            return null;
        }

        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_contribute " .
            "WHERE role = " . $this->db->quote($this->options['lom_role'], 'text') . " " .
            "AND obj_type " . $this->__getInStatement($this->getFilter());

        $res = $this->db->query($query);
        #var_dump("<pre>",$query,"<pre>");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->rbac_id, $row->obj_type, array(), $row->obj_id);
        }
        return $this->search_result;
    }

    public function __searchEntity(): ?ilSearchResult
    {
        $this->setFields(array('entity'));

        $and = ("AND obj_type " . $this->__getInStatement($this->getFilter()));
        $where = $this->__createEntityWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT rbac_id,obj_id,obj_type " .
            $locate .
            "FROM il_meta_entity " .
            $where . " " . $and . ' ';

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $found = $this->__prepareFound($row);
            if (!in_array(0, $found)) {
                $this->search_result->addEntry($row->rbac_id, $row->obj_type, $found, $row->obj_id);
            }
        }

        return $this->search_result;
    }



    public function __searchRequirement(): ?ilSearchResult
    {
        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_requirement ";

        if (!strlen($where = $this->__createRequirementWhere())) {
            return null;
        }
        $and = ("AND obj_type " . $this->__getInStatement($this->getFilter()));
        $query = $query . $where . $and;
        $res = $this->db->query($query);
        #var_dump("<pre>",$query,"<pre>");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->rbac_id, $row->obj_type, array(), $row->obj_id);
        }
        return $this->search_result;
    }

    public function __searchEducational(): ?ilSearchResult
    {
        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_educational ";

        if (!strlen($where = $this->__createEducationalWhere())) {
            return null;
        }
        $and = ("AND obj_type " . $this->__getInStatement($this->getFilter()));
        $query = $query . $where . $and;
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->rbac_id, $row->obj_type, array(), $row->obj_id);
        }
        return $this->search_result;
    }

    public function __searchTypicalAgeRange(): ?ilSearchResult
    {
        if (!$this->options['typ_age_1'] or !$this->options['typ_age_2']) {
            return null;
        }

        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_typical_age_range " .
            "WHERE typical_age_range_min >= '" . (int) $this->options['typ_age_1'] . "' " .
            "AND typical_age_range_max <= '" . (int) $this->options['typ_age_2'] . "'";


        $res = $this->db->query($query);
        #var_dump("<pre>",$query,"<pre>");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->rbac_id, $row->obj_type, array(), $row->obj_id);
        }
        return $this->search_result;
    }

    public function __searchRights(): ?ilSearchResult
    {
        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_rights ";

        if (!strlen($where = $this->__createRightsWhere())) {
            return null;
        }
        $and = ("AND obj_type " . $this->__getInStatement($this->getFilter()));
        $query = $query . $where . $and;
        $res = $this->db->query($query);
        #var_dump("<pre>",$query,"<pre>");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->rbac_id, $row->obj_type, array(), $row->obj_id);
        }
        return $this->search_result;
    }

    public function __searchClassification(): ?ilSearchResult
    {
        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_classification ";

        if (!strlen($where = $this->__createClassificationWhere())) {
            return null;
        }
        $and = ("AND obj_type " . $this->__getInStatement($this->getFilter()));
        $query = $query . $where . $and;
        $res = $this->db->query($query);
        #var_dump("<pre>",$query,"<pre>");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->rbac_id, $row->obj_type, array(), $row->obj_id);
        }
        return $this->search_result;
    }

    public function __searchTaxon(): ?ilSearchResult
    {
        $this->setFields(array('taxon'));

        $and = ("AND obj_type " . $this->__getInStatement($this->getFilter()));
        $where = $this->__createTaxonWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT rbac_id,obj_id,obj_type " .
            $locate .
            "FROM il_meta_taxon " .
            $where . " " . $and . ' ';

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $found = $this->__prepareFound($row);
            if (!in_array(0, $found)) {
                $this->search_result->addEntry($row->rbac_id, $row->obj_type, $found, $row->obj_id);
            }
        }

        return $this->search_result;
    }

    public function __searchKeyword(bool $a_in_classification = false): ilSearchResult
    {
        $this->setFields(array('keyword'));

        $and = ("AND obj_type " . $this->__getInStatement($this->getFilter()));
        if ($a_in_classification) {
            $and .= " AND parent_type = 'meta_classification' ";
        }
        $where = $this->__createKeywordWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT rbac_id,obj_id,obj_type " .
            $locate .
            "FROM il_meta_keyword " .
            $where . " " . $and . ' ';

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $found = $this->__prepareFound($row);
            if (!in_array(0, $found) or !$a_in_classification) {
                $this->search_result->addEntry($row->rbac_id, $row->obj_type, $found, $row->obj_id);
            }
        }

        return $this->search_result;
    }
    public function __searchLifecycle(): ilSearchResult
    {
        $this->setFields(array('meta_version'));

        $locate = '';
        if ($this->options['lom_version']) {
            $where = $this->__createLifecycleWhereCondition();
            $locate = $this->__createLocateString();
        } else {
            $where = "WHERE 1 = 1 ";
        }
        $and = ("AND obj_type " . $this->__getInStatement($this->getFilter()));

        if ($this->options['lom_status']) {
            $and .= (" AND lifecycle_status = " . $this->db->quote($this->options['lom_status'], 'text') . "");
        }

        $query = "SELECT rbac_id,obj_id,obj_type " .
            $locate .
            "FROM il_meta_lifecycle " .
            $where . " " . $and . ' ';

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $found = $this->__prepareFound($row);
            if (!in_array(0, $found)) {
                $this->search_result->addEntry($row->rbac_id, $row->obj_type, $found, $row->obj_id);
            }
        }

        return $this->search_result;
    }

    public function __searchFormat(): ?ilSearchResult
    {
        if (!$this->options['lom_format']) {
            return null;
        }

        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_format " .
            "WHERE format LIKE(" . $this->db->quote($this->options['lom_format'], ilDBConstants::T_TEXT) . ") " .
            "AND obj_type " . $this->__getInStatement($this->getFilter());

        $res = $this->db->query($query);
        #var_dump("<pre>",$query,"<pre>");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->rbac_id, $row->obj_type, array(), $row->obj_id);
        }
        return $this->search_result;
    }


    public function __createRightsWhere(): string
    {
        $counter = 0;
        $where = 'WHERE ';


        if ($this->options['lom_costs']) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "costs = " . $this->db->quote($this->options['lom_costs'], 'text') . " ");
        }
        if ($this->options['lom_copyright']) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "cpr_and_or = " . $this->db->quote($this->options['lom_copyright'], 'text') . " ");
        }
        return $counter ? $where : '';
    }
    public function __createClassificationWhere(): string
    {
        $counter = 0;
        $where = 'WHERE ';


        if ($this->options['lom_purpose']) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "purpose = " . $this->db->quote($this->options['lom_purpose'], ilDBConstants::T_TEXT) . " ");
        }
        return $counter ? $where : '';
    }
    public function __createEducationalWhere(): string
    {
        $counter = 0;
        $where = 'WHERE ';


        if ($this->options['lom_interactivity']) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "interactivity_type = " . $this->db->quote($this->options['lom_interactivity'], 'text') . " ");
        }
        if ($this->options['lom_resource']) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "learning_resource_type = " . $this->db->quote($this->options['lom_resource'], 'text') . " ");
        }
        if ($this->options['lom_user_role']) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "intended_end_user_role = " . $this->db->quote($this->options['lom_user_role'], 'text') . " ");
        }
        if ($this->options['lom_context']) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "context = " . $this->db->quote($this->options['lom_context'], 'text') . " ");
        }
        if ($this->options['lom_level_start'] or $this->options['lom_level_end']) {
            $and = $counter++ ? 'AND ' : ' ';

            $fields = $this->__getDifference(
                $this->options['lom_level_start'],
                $this->options['lom_level_end'],
                array('VeryLow','Low','Medium','High','VeryHigh')
            );

            $where .= ($and . "interactivity_level " . $this->__getInStatement($fields));
        }
        if ($this->options['lom_density_start'] or $this->options['lom_density_end']) {
            $and = $counter++ ? 'AND ' : ' ';

            $fields = $this->__getDifference(
                $this->options['lom_density_start'],
                $this->options['lom_density_end'],
                array('VeryLow','Low','Medium','High','VeryHigh')
            );

            $where .= ($and . "semantic_density " . $this->__getInStatement($fields));
        }
        if ($this->options['lom_difficulty_start'] or $this->options['lom_difficulty_end']) {
            $and = $counter++ ? 'AND ' : ' ';

            $fields = $this->__getDifference(
                $this->options['lom_difficulty_start'],
                $this->options['lom_difficulty_end'],
                array('VeryEasy','Easy','Medium','Difficult','VeryDifficult')
            );

            $where .= ($and . "difficulty " . $this->__getInStatement($fields));
        }

        return $counter ? $where : '';
    }
    public function __createRequirementWhere(): string
    {
        $counter = 0;
        $where = 'WHERE ';


        if ($this->options['lom_operating_system']) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "operating_system_name = " . $this->db->quote($this->options['lom_operating_system'], ilDBConstants::T_TEXT) . " ");
        }
        if ($this->options['lom_browser']) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "browser_name = " . $this->db->quote($this->options['lom_browser'], ilDBConstants::T_TEXT) . " ");
        }
        return $counter ? $where : '';
    }

    /**
     * @return string[]
     */
    public function __getDifference(int $a_val1, int $a_val2, array $options): array
    {
        $a_val2 = $a_val2 ?: count($options);
        // Call again if a > b
        if ($a_val1 > $a_val2) {
            return $this->__getDifference($a_val2, $a_val1, $options);
        }

        $counter = 0;
        $fields = [];
        foreach ($options as $option) {
            if ($a_val1 > ++$counter) {
                continue;
            }
            if ($a_val2 < $counter) {
                break;
            }
            $fields[] = $option;
        }
        return $fields;
    }

    public function __getInStatement(array $a_fields): string
    {
        if (!$a_fields) {
            return '';
        }
        $in = " IN ('";
        $in .= implode("','", $a_fields);
        $in .= "') ";

        return $in;
    }
}
