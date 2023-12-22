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

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class GlossaryForeignTermTable
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected ServerRequestInterface $request;
    protected Data\Factory $df;
    protected \ilObjGlossary $glossary;

    public function __construct(\ilObjGlossary $glossary)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->df = new Data\Factory();
        $this->glossary = $glossary;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        $table = $this->ui_fac->table()
                              ->data($this->glossary->getTitle() . ": " . $this->lng->txt("glo_select_terms"), $columns, $data_retrieval)
                              ->withActions($actions)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "title" => $this->ui_fac->table()->column()->text($this->lng->txt("glo_term"))
                ->withIsSortable(false)
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        $query_params_namespace = ["glo_foreign_term_table"];

        $uri_copy = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilglossaryforeigntermcollectorgui", "copyTerms")
        );
        $url_builder_copy = new UI\URLBuilder($uri_copy);
        list($url_builder_copy, $action_parameter_token_copy, $row_id_token_copy) =
            $url_builder_copy->acquireParameters(
                $query_params_namespace,
                "action",
                "term_ids"
            );

        $uri_reference = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilglossaryforeigntermcollectorgui", "referenceTerms")
        );
        $url_builder_reference = new UI\URLBuilder($uri_reference);
        list($url_builder_reference, $action_parameter_token_reference, $row_id_token_reference) =
            $url_builder_reference->acquireParameters(
                $query_params_namespace,
                "action",
                "term_ids"
            );

        $actions = [
            "copy" => $this->ui_fac->table()->action()->multi(
                $this->lng->txt("glo_copy_terms"),
                $url_builder_copy->withParameter($action_parameter_token_copy, "copyTerms"),
                $row_id_token_copy
            ),
            "reference" => $this->ui_fac->table()->action()->multi(
                $this->lng->txt("glo_reference_terms"),
                $url_builder_reference->withParameter($action_parameter_token_reference, "referenceTerms"),
                $row_id_token_reference
            )
        ];

        return $actions;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->glossary
        ) implements UI\Component\Table\DataRetrieval {
            use TableRecords;

            public function __construct(
                protected \ilObjGlossary $glossary
            ) {
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
                    $row_id = (string) $record["term_id"];

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
                $records = [];
                $i = 0;
                foreach ($this->glossary->getTermList() as $term) {
                    $records[$i]["term_id"] = $term["id"];
                    $records[$i]["title"] = $term["term"];
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
