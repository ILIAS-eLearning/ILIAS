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
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @package ilias-search
*
*/
class ilLikeAdvancedSearch extends ilAdvancedSearch
{
    public function __createTaxonWhereCondition(): string
    {
        if ($this->options['lom_taxon']) {
            $where = " WHERE (";

            $counter = 0;
            foreach ($this->query_parser->getQuotedWords() as $word) {
                if ($counter++) {
                    $where .= "OR";
                }

                $where .= $this->db->like('taxon', 'text', '%' . $word . '%');
            }
            $where .= ') ';
            return $where;
        }
        return '';
    }

    public function __createKeywordWhereCondition(): string
    {
        $where = " WHERE (";

        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $where .= "OR";
            }

            $where .= $this->db->like('keyword', 'text', '%' . $word . '%');
        }
        $where .= ') ';
        return $where;
    }

    public function __createLifecycleWhereCondition(): string
    {
        if ($this->options['lom_version']) {
            $where = " WHERE (";

            $counter = 0;
            foreach ($this->query_parser->getQuotedWords() as $word) {
                if ($counter++) {
                    $where .= "OR";
                }

                $where .= $this->db->like('meta_version', 'text', '%' . $word . '%');
            }
            $where .= ') ';
            return $where;
        }
        return '';
    }

    public function __createEntityWhereCondition(): string
    {
        if ($this->options['lom_role_entry']) {
            $where = " WHERE (";

            $counter = 0;
            foreach ($this->query_parser->getQuotedWords() as $word) {
                if ($counter++) {
                    $where .= "OR";
                }

                $where .= $this->db->like('entity', 'text', '%' . $word . '%');
            }
            $where .= ') ';
            return $where;
        }
        return '';
    }

    public function __createCoverageAndCondition(): string
    {
        if ($this->options['lom_coverage']) {
            $where = " AND (";

            $counter = 0;
            foreach ($this->query_parser->getQuotedWords() as $word) {
                if ($counter++) {
                    $where .= "OR";
                }

                $where .= $this->db->like('coverage', 'text', '%' . $word . '%');
            }
            $where .= ') ';
            return $where;
        }
        return '';
    }

    public function __createObjectPropertiesWhereCondition(string ...$fields): string
    {
        $concat_array = [];
        foreach ($fields as $field) {
            $concat_array[] = [$field, 'text'];
        }
        $concat = $this->db->concat($concat_array);

        $where = " WHERE (";

        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $where .= "OR";
            }

            $where .= $this->db->like($concat, 'text', '%' . $word . '%');
        }
        $where .= ') ';

        return $where;
    }
}
