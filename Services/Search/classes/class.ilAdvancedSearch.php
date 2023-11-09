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


    public function performSearch(): ?ilSearchResult
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

            case 'title':
                return $this->__searchTitle();

            case 'description':
                return $this->__searchDescription();

            case 'language':
                return $this->__searchLanguage();

            default:
                throw new InvalidArgumentException('ilMDSearch: no valid mode given');
        }
    }

    public function &__searchTitleDescription(): ilSearchResult
    {
        $this->searchObjectProperties('title', 'description');
        return $this->search_result;
    }

    public function __searchTitle(): ilSearchResult
    {
        return $this->searchObjectProperties('title');
    }

    public function __searchDescription(): ilSearchResult
    {
        return $this->searchObjectProperties('description');
    }

    protected function searchObjectProperties(string ...$fields): ilSearchResult
    {
        $this->setFields($fields);

        $and = ("AND type " . $this->__getInStatement($this->getFilter()));
        $where = $this->__createObjectPropertiesWhereCondition(...$fields);
        $locate = $this->__createLocateString();

        $query = "SELECT obj_id,type " .
            $locate .
            "FROM object_data " .
            $where . " " . $and . ' ' .
            "ORDER BY obj_id DESC";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->obj_id,
                (string) $row->type,
                $this->__prepareFound($row)
            );
        }

        return $this->search_result;
    }

    public function __searchGeneral(): ?ilSearchResult
    {
        global $DIC;

        $ilDB = $DIC->database();

        $coverage_query = $general_query = '';
        if ($this->options['lom_coverage'] ?? null) {
            $this->setFields(array('coverage'));
            $and = $this->__createCoverageAndCondition();
            $locate = $this->__createLocateString();
            $coverage_query = "SELECT rbac_id,obj_type,obj_id " .
                $locate . " " .
                "FROM il_meta_coverage " .
                "WHERE obj_type " . $this->__getInStatement($this->getFilter()) . " " .
                $and;
        }
        if ($this->options['lom_structure'] ?? null) {
            $and = ("AND general_structure = " . $ilDB->quote($this->options['lom_structure'], ilDBConstants::T_TEXT) . " ");
            $general_query = "SELECT rbac_id,obj_type,obj_id " .
                "FROM il_meta_general " .
                "WHERE obj_type " . $this->__getInStatement($this->getFilter()) . " " .
                $and;
        }

        $query = $this->joinOnRessourceIDs($general_query, $coverage_query);
        if ($query === '') {
            return null;
        }

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($this->options['lom_coverage'] ?? null) {
                $found = $this->__prepareFound($row);
                if (!in_array(0, $found)) {
                    $this->search_result->addEntry(
                        (int) $row->rbac_id,
                        (string) $row->obj_type,
                        $found,
                        (int) $row->obj_id
                    );
                }
            } else {
                $this->search_result->addEntry(
                    (int) $row->rbac_id,
                    (string) $row->obj_type,
                    array(),
                    (int) $row->obj_id
                );
            }
        }

        return $this->search_result;
    }

    public function __searchLanguage(): ?ilSearchResult
    {
        if (!($this->options['lom_language'] ?? null)) {
            return null;
        }

        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_language " .
            "WHERE language = " . $this->db->quote($this->options['lom_language'], 'text') . " " .
            "AND obj_type " . $this->__getInStatement($this->getFilter()) . ' ' .
            "AND parent_type = 'meta_general'";

        $res = $this->db->query($query);
        #var_dump("<pre>",$query,"<pre>");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->rbac_id,
                (string) $row->obj_type,
                array(),
                (int) $row->obj_id
            );
        }
        return $this->search_result;
    }

    public function __searchContribute(): ?ilSearchResult
    {
        if (!($this->options['lom_role'] ?? null)) {
            return null;
        }

        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_contribute " .
            "WHERE role = " . $this->db->quote($this->options['lom_role'], 'text') . " " .
            "AND obj_type " . $this->__getInStatement($this->getFilter());

        $res = $this->db->query($query);
        #var_dump("<pre>",$query,"<pre>");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->rbac_id,
                (string) $row->obj_type,
                array(),
                (int) $row->obj_id
            );
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
                $this->search_result->addEntry(
                    (int) $row->rbac_id,
                    (string) $row->obj_type,
                    $found,
                    (int) $row->obj_id
                );
            }
        }

        return $this->search_result;
    }



    public function __searchRequirement(): ?ilSearchResult
    {
        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_or_composite " .
            "WHERE obj_type " . $this->__getInStatement($this->getFilter());

        $os_query = $browser_query = '';
        if ($this->options['lom_operating_system'] ?? null) {
            $os_query = $query . " AND type = 'operating system' AND " .
                "name = " . $this->db->quote($this->options['lom_operating_system'], ilDBConstants::T_TEXT);
        }
        if ($this->options['lom_browser'] ?? null) {
            $browser_query = $query . " AND type = 'browser' AND " .
                "name = " . $this->db->quote($this->options['lom_browser'], ilDBConstants::T_TEXT);
        }

        $query = $this->joinOnRessourceIDs($os_query, $browser_query);
        if ($query === '') {
            return null;
        }

        $res = $this->db->query($query);
        #var_dump("<pre>",$query,"<pre>");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->rbac_id,
                (string) $row->obj_type,
                array(),
                (int) $row->obj_id
            );
        }
        return $this->search_result;
    }

    public function __searchEducational(): ?ilSearchResult
    {
        $query_start = "SELECT rbac_id,obj_id,obj_type ";
        $and = " AND obj_type " . $this->__getInStatement($this->getFilter());

        $ed_query = $lr_type_query = $end_user_query = $context_query = '';
        if ($where = $this->__createEducationalWhere()) {
            $ed_query = $query_start . 'FROM il_meta_educational ' . $where . $and;
        }
        if ($this->options['lom_resource'] ?? null) {
            $where = " WHERE learning_resource_type = " . $this->db->quote($this->options['lom_resource'], 'text');
            $lr_type_query = $query_start . 'FROM il_meta_lr_type ' . $where . $and;
        }
        if ($this->options['lom_user_role'] ?? null) {
            $where = " WHERE intended_end_user_role = " . $this->db->quote($this->options['lom_user_role'], 'text');
            $end_user_query = $query_start . 'FROM il_meta_end_usr_role ' . $where . $and;
        }
        if ($this->options['lom_context'] ?? null) {
            $where = " WHERE context = " . $this->db->quote($this->options['lom_context'], 'text');
            $context_query = $query_start . 'FROM il_meta_context ' . $where . $and;
        }

        $query = $this->joinOnRessourceIDs(
            $ed_query,
            $lr_type_query,
            $end_user_query,
            $context_query
        );
        if ($query === '') {
            return null;
        }

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->rbac_id,
                (string) $row->obj_type,
                array(),
                (int) $row->obj_id
            );
        }
        return $this->search_result;
    }

    public function __searchTypicalAgeRange(): ?ilSearchResult
    {
        if (
            !($this->options['typ_age_1'] ?? null) or
            !($this->options['typ_age_2'] ?? null)
        ) {
            return null;
        }

        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_typical_age_range " .
            "WHERE typical_age_range_min >= '" . (int) $this->options['typ_age_1'] . "' " .
            "AND typical_age_range_max <= '" . (int) $this->options['typ_age_2'] . "'";


        $res = $this->db->query($query);
        #var_dump("<pre>",$query,"<pre>");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->rbac_id,
                (string) $row->obj_type,
                array(),
                (int) $row->obj_id
            );
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
            $this->search_result->addEntry(
                (int) $row->rbac_id,
                (string) $row->obj_type,
                array(),
                (int) $row->obj_id
            );
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
            $this->search_result->addEntry(
                (int) $row->rbac_id,
                (string) $row->obj_type,
                array(),
                (int) $row->obj_id
            );
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
                $this->search_result->addEntry(
                    (int) $row->rbac_id,
                    (string) $row->obj_type,
                    $found,
                    (int) $row->obj_id
                );
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
                $this->search_result->addEntry(
                    (int) $row->rbac_id,
                    (string) $row->obj_type,
                    $found,
                    (int) $row->obj_id
                );
            }
        }

        return $this->search_result;
    }
    public function __searchLifecycle(): ilSearchResult
    {
        $this->setFields(array('meta_version'));

        $locate = '';
        if ($this->options['lom_version'] ?? null) {
            $where = $this->__createLifecycleWhereCondition();
            $locate = $this->__createLocateString();
        } else {
            $where = "WHERE 1 = 1 ";
        }
        $and = ("AND obj_type " . $this->__getInStatement($this->getFilter()));

        if ($this->options['lom_status'] ?? null) {
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
                $this->search_result->addEntry(
                    (int) $row->rbac_id,
                    (string) $row->obj_type,
                    $found,
                    (int) $row->obj_id
                );
            }
        }

        return $this->search_result;
    }

    public function __searchFormat(): ?ilSearchResult
    {
        if (!($this->options['lom_format'] ?? null)) {
            return null;
        }

        $query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_format " .
            "WHERE format LIKE(" . $this->db->quote($this->options['lom_format'], ilDBConstants::T_TEXT) . ") " .
            "AND obj_type " . $this->__getInStatement($this->getFilter());

        $res = $this->db->query($query);
        #var_dump("<pre>",$query,"<pre>");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->rbac_id,
                (string) $row->obj_type,
                array(),
                (int) $row->obj_id
            );
        }
        return $this->search_result;
    }


    public function __createRightsWhere(): string
    {
        $counter = 0;
        $where = 'WHERE ';


        if ($this->options['lom_costs'] ?? null) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "costs = " . $this->db->quote($this->options['lom_costs'], 'text') . " ");
        }
        if ($this->options['lom_copyright'] ?? null) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "cpr_and_or = " . $this->db->quote($this->options['lom_copyright'], 'text') . " ");
        }
        return $counter ? $where : '';
    }
    public function __createClassificationWhere(): string
    {
        $counter = 0;
        $where = 'WHERE ';


        if ($this->options['lom_purpose'] ?? null) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "purpose = " . $this->db->quote($this->options['lom_purpose'], ilDBConstants::T_TEXT) . " ");
        }
        return $counter ? $where : '';
    }
    public function __createEducationalWhere(): string
    {
        $counter = 0;
        $where = 'WHERE ';


        if ($this->options['lom_interactivity'] ?? null) {
            $and = $counter++ ? 'AND ' : ' ';
            $where .= ($and . "interactivity_type = " . $this->db->quote($this->options['lom_interactivity'], 'text') . " ");
        }
        if (
            ($this->options['lom_level_start'] ?? null) or
            ($this->options['lom_level_end'] ?? null)) {
            $and = $counter++ ? 'AND ' : ' ';

            $fields = $this->__getDifference(
                (int) $this->options['lom_level_start'],
                (int) $this->options['lom_level_end'],
                array('VeryLow','Low','Medium','High','VeryHigh')
            );

            $where .= ($and . "interactivity_level " . $this->__getInStatement($fields));
        }
        if (
            ($this->options['lom_density_start'] ?? null) or
            ($this->options['lom_density_end'] ?? null)
        ) {
            $and = $counter++ ? 'AND ' : ' ';

            $fields = $this->__getDifference(
                (int) $this->options['lom_density_start'],
                (int) $this->options['lom_density_end'],
                array('VeryLow','Low','Medium','High','VeryHigh')
            );

            $where .= ($and . "semantic_density " . $this->__getInStatement($fields));
        }
        if (
            ($this->options['lom_difficulty_start'] ?? null) or
            ($this->options['lom_difficulty_end'] ?? null)
        ) {
            $and = $counter++ ? 'AND ' : ' ';

            $fields = $this->__getDifference(
                (int) $this->options['lom_difficulty_start'],
                (int) $this->options['lom_difficulty_end'],
                array('VeryEasy','Easy','Medium','Difficult','VeryDifficult')
            );

            $where .= ($and . "difficulty " . $this->__getInStatement($fields));
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

    protected function joinOnRessourceIDs(string ...$individual_queries): string
    {
        $non_empty_queries = [];
        foreach ($individual_queries as $query) {
            if ($query !== '') {
                $non_empty_queries[] = $query;
            }
        }

        if (count($non_empty_queries) < 2) {
            return $non_empty_queries[0] ?? '';
        }

        $total_query = '';
        foreach ($non_empty_queries as $query) {
            if ($total_query === '') {
                $total_query = $query;
                continue;
            }
            $total_query = "SELECT t1.rbac_id, t1.obj_type, t1.obj_id " .
                "FROM (" . $total_query . ") AS t1 JOIN (" . $query .
                ") AS t2 ON t1.rbac_id = t2.rbac_id AND t1.obj_type = t2.obj_type AND t1.obj_id = t2.obj_id";
        }
        return $total_query;
    }
}
