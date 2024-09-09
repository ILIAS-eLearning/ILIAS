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

class ilMailLuceneQueryParser extends ilLuceneQueryParser
{
    protected array $fields = [];

    public function parse(): void
    {
        if (!empty($this->getFields())) {
            $queried_fields = [];
            $token_operator = ' OR ';
            if (ilSearchSettings::getInstance()->getDefaultOperator() === ilSearchSettings::OPERATOR_AND) {
                $token_operator = ' AND ';
            }

            // ILIAS 10: each filter field has now its own query string
            foreach ($this->getFields() as $field => $query_string) {
                if (empty($query_string)) {
                    continue;
                }

                $field_query = '';
                $tokens = array_map(trim(...), explode(' ', $query_string));
                foreach ($tokens as $token) {
                    if ($field_query !== '') {
                        $field_query .= $token_operator;
                    }
                    $field_query .= '(' . $field . ':' . $token . ')';
                }

                $queried_fields[] = '(' . $field_query . ')';
            }

            if ($queried_fields !== []) {
                $this->parsed_query = implode(' OR ', $queried_fields);
                return;
            }
        }

        parent::parse();
    }

    /**
     * Set the fields to query for
     * ILIAS 10: the values are not boolean, but different query strings for the fields
     *
     * @param array{'title': ?string, 'content': ?string, 'mattachment': ?string, 'msender': ?string}  $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * Get the fields to query for
     * ILIAS 10 the values are not boolean, but different query strings for the fields
     * @return array{'title': ?string, 'content': ?string, 'mattachment': ?string, 'msender': ?string}
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
