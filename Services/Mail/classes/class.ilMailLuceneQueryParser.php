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
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailLuceneQueryParser extends ilLuceneQueryParser
{
    protected array $fields = [];

    public function parse(): void
    {
        if ($this->getFields()) {
            $queried_fields = [];
            foreach ($this->getFields() as $field => $status) {
                if ($status) {
                    $queried_fields[] = $field . ':' . $this->query_string;
                }
            }

            if ($queried_fields) {
                $this->parsed_query = implode(' OR ', $queried_fields);
            } else {
                $this->parsed_query = $this->query_string;
            }
        } else {
            $this->parsed_query = $this->query_string;
        }
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}
