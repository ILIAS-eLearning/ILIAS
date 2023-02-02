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
*
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*/
class ilLuceneAdvancedQueryParser extends ilLuceneQueryParser
{
    protected ilLuceneAdvancedSearchFields $field_definition;
    /**
     * @var array|string
     */
    protected $query_data;

    /**
     * Constructor
     */
    public function __construct($a_query_data)
    {
        parent::__construct('');

        $this->field_definition = ilLuceneAdvancedSearchFields::getInstance();
        $this->query_data = $a_query_data;
    }

    /**
     * Get field definition settings
     */
    public function getFieldDefinition(): ilLuceneAdvancedSearchFields
    {
        return $this->field_definition;
    }

    /**
     * @return array|string
     */
    public function getQueryData()
    {
        if (is_array($this->query_data)) {
            return $this->query_data;
        }
        return $this->query_data ?? '';
    }

    public function parse(): void
    {
        foreach ((array) $this->getQueryData() as $field => $query) {
            if (!is_array($query) && !trim($query)) {
                continue;
            }
            $parsed = $this->getFieldDefinition()->parseFieldQuery($field, $query);
            if (strlen($parsed)) {
                $this->parsed_query .= " +(";
                $this->parsed_query .= $parsed;
                $this->parsed_query .= ") ";
            }
        }
    }
}
