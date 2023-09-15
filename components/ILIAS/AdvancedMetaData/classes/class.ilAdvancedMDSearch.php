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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDSearch extends ilAbstractSearch
{
    protected ?ilAdvancedMDFieldDefinition $definition = null;
    protected ?ilADTSearchBridge $adt = null;

    public function __construct($query_parser)
    {
        parent::__construct($query_parser);
    }

    public function setDefinition(ilAdvancedMDFieldDefinition $a_def): void
    {
        $this->definition = $a_def;
    }

    public function getDefinition(): ilAdvancedMDFieldDefinition
    {
        return $this->definition;
    }

    public function setSearchElement(ilADTSearchBridge $a_adt): void
    {
        $this->adt = $a_adt;
    }

    public function getSearchElement(): ilADTSearchBridge
    {
        return $this->adt;
    }

    public function performSearch(): ilSearchResult
    {
        $this->query_parser->parse();

        $locate = '';
        $parser_value = $this->getDefinition()->getSearchQueryParserValue($this->getSearchElement());
        if ($parser_value) {
            $this->setFields(
                [
                    $this->getSearchElement()->getSearchColumn()
                ]
            );
            $locate = $this->__createLocateString();
        }

        $search_type = strtolower(substr(get_class($this), 12, -6));

        $res_field = $this->getDefinition()->searchObjects(
            $this->getSearchElement(),
            $this->query_parser,
            $this->getFilter(),
            $locate,
            $search_type
        );

        if (is_array($res_field)) {
            foreach ($res_field as $row) {
                $found = is_array($row["found"] ?? null) ? $row["found"] : [];
                $this->search_result->addEntry((int) $row["obj_id"], $row["type"], $found);
            }
            return $this->search_result;
        }
        return $this->search_result;
    }
}
