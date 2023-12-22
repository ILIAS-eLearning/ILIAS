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
 ********************************************************************
 */

namespace ILIAS\components\ILIAS\Glossary\Table;

use ILIAS\Data;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Glossary\Term\TermManager;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class TermDefinitionBulkCreationTable
{
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected ServerRequestInterface $request;
    protected string $raw_data;
    protected \ilObjGlossary $glossary;

    public function __construct(string $raw_data, \ilObjGlossary $glossary)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->raw_data = $raw_data;
        $this->glossary = $glossary;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $data_retrieval = $this->getDataRetrieval();

        $table = $this->ui_fac->table()
                              ->data($this->lng->txt("glo_term_definition_pairs"), $columns, $data_retrieval)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "term" => $this->ui_fac->table()->column()->text($this->lng->txt("cont_term"))
                                    ->withIsSortable(false),
            "definition" => $this->ui_fac->table()->column()->text($this->lng->txt("cont_definition"))
                                   ->withIsSortable(false)
        ];

        return $columns;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->raw_data,
            $this->glossary
        ) implements UI\Component\Table\DataRetrieval {
            use TableRecords;

            protected TermManager $term_manager;

            public function __construct(
                protected string $raw_data,
                protected \ilObjGlossary $glossary
            ) {
                global $DIC;

                $this->term_manager = $DIC->glossary()->internal()->domain()->term($this->glossary);
            }

            public function getRows(
                UI\Component\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Data\Range $range,
                Data\Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                $records = $this->getRecords($range);
                foreach ($records as $idx => $record) {
                    $row_id = (string) $record["id"];

                    yield $row_builder->buildDataRow($row_id, $record);
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return count($this->getRecords());
            }

            protected function getRecords(Data\Range $range = null): array
            {
                $data = $this->term_manager->getDataArrayFromInputString($this->raw_data);

                $records = [];
                $i = 0;
                foreach ($data as $pair) {
                    $records[$i]["id"] = $i + 1;
                    $records[$i]["term"] = $pair["term"];
                    $records[$i]["definition"] = $pair["definition"];

                    $i++;
                }

                if ($range) {
                    $records = $this->limitRecords($records, $range);
                }

                return $records;
            }
        };

        return $data_retrieval;
    }
}
