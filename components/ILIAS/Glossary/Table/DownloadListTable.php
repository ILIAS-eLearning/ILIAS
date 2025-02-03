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

namespace ILIAS\components\ILIAS\Glossary\Table;

use ILIAS\Data;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Glossary\Editing\EditingGUIRequest;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class DownloadListTable
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected ServerRequestInterface $request;
    protected Data\Factory $df;
    protected \ilObjUser $user;
    protected \ilObjGlossary $glossary;

    public function __construct(\ilObjGlossary $glossary)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->df = new Data\Factory();
        $this->user = $DIC->user();
        $this->glossary = $glossary;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        $table = $this->ui_fac->table()
                              ->data($this->lng->txt("download"), $columns, $data_retrieval)
                              ->withId(
                                  self::class . "_" .
                                  $this->glossary->getRefId()
                              )
                              ->withActions($actions)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        if ((int) $this->user->getTimeFormat() === \ilCalendarSettings::TIME_FORMAT_12) {
            $date_format = $this->df->dateFormat()->withTime12($this->user->getDateFormat());
        } else {
            $date_format = $this->df->dateFormat()->withTime24($this->user->getDateFormat());
        }

        $columns = [
            "format" => $this->ui_fac->table()->column()->text($this->lng->txt("cont_format"))->withIsSortable(false),
            "file" => $this->ui_fac->table()->column()->text($this->lng->txt("cont_file"))->withIsSortable(false),
            "size" => $this->ui_fac->table()->column()->number($this->lng->txt("size"))->withIsSortable(false),
            "date" => $this->ui_fac->table()->column()->date(
                $this->lng->txt("date"),
                $date_format
            )->withIsSortable(false),
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        $query_params_namespace = ["glo_download_list_table"];

        $uri_download = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilglossarypresentationgui", "downloadExportFile")
        );
        $url_builder_download = new UI\URLBuilder($uri_download);
        list($url_builder_download, $action_parameter_token_download, $row_id_token_download) =
            $url_builder_download->acquireParameters(
                $query_params_namespace,
                "action",
                "file_ids"
            );

        $actions["download"] = $this->ui_fac->table()->action()->single(
            $this->lng->txt("download"),
            $url_builder_download->withParameter($action_parameter_token_download, "downloadExportFile"),
            $row_id_token_download
        );

        return $actions;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->glossary,
            $this->df
        ) implements UI\Component\Table\DataRetrieval {
            use TableRecords;

            public function __construct(
                protected \ilObjGlossary $glossary,
                protected Data\Factory $df
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
                    $row_id = (string) $record["file_id"];

                    yield $row_builder->buildDataRow($row_id, $record);
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return count($this->getRecords());
            }

            protected function getRecords(?Data\Range $range = null): array
            {
                $export_files = [];
                $types = ["xml", "html"];
                foreach ($types as $type) {
                    $pe_file = $this->glossary->getPublicExportFile($type);
                    if ($pe_file != "") {
                        $dir = $this->glossary->getExportDirectory($type);
                        if (is_file($dir . "/" . $pe_file)) {
                            $size = filesize($dir . "/" . $pe_file);
                            $export_files[] = [
                                "type" => $type,
                                "file" => $pe_file,
                                "size" => $size
                            ];
                        }
                    }
                }

                $records = [];
                $i = 0;
                foreach ($export_files as $exp_file) {
                    $records[$i]["file_id"] = $exp_file["type"] . ":" . $exp_file["file"];
                    $records[$i]["format"] = strtoupper($exp_file["type"]);
                    $records[$i]["file"] = $exp_file["file"];
                    $records[$i]["size"] = $exp_file["size"];
                    $file_arr = explode("__", $exp_file["file"]);
                    $records[$i]["date"] = (new \DateTimeImmutable())->setTimestamp((int) $file_arr[0]);

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
