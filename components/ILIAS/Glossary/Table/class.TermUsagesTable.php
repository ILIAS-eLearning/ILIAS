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
class TermUsagesTable
{
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected ServerRequestInterface $request;
    protected \ilAccessHandler $access;
    protected int $term_id;

    public function __construct(int $term_id)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->access = $DIC->access();
        $this->term_id = $term_id;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $data_retrieval = $this->getDataRetrieval();

        $table = $this->ui_fac->table()
                              ->data($this->lng->txt("cont_usage"), $columns, $data_retrieval)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "object" => $this->ui_fac->table()->column()->text($this->lng->txt("objects")),
            "sub_object" => $this->ui_fac->table()->column()->text($this->lng->txt("subobjects")),
            "version" => $this->ui_fac->table()->column()->text($this->lng->txt("cont_versions"))
                                    ->withIsSortable(false),
            "type" => $this->ui_fac->table()->column()->text($this->lng->txt("type")),
            "link" => $this->ui_fac->table()->column()->link($this->lng->txt("cont_link"))
                                    ->withIsSortable(false)
        ];

        return $columns;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->lng,
            $this->access,
            $this->ui_fac,
            $this->ui_ren,
            $this->term_id
        ) implements UI\Component\Table\DataRetrieval {
            use TableRecords;

            public function __construct(
                protected \ilLanguage $lng,
                protected \ilAccess $access,
                protected UI\Factory $ui_fac,
                protected UI\Renderer $ui_ren,
                protected int $term_id
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
                $records = $this->getRecords($range, $order);
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

            protected function getRecords(Data\Range $range = null, Data\Order $order = null): array
            {
                $usages = \ilGlossaryTerm::getUsages($this->term_id);

                $agg_usages = [];
                foreach ($usages as $usage) {
                    if (empty($agg_usages[$usage["type"] . ":" . $usage["id"]])) {
                        $usage["hist_nr"] = [$usage["hist_nr"] ?? 0];
                        $agg_usages[$usage["type"] . ":" . $usage["id"]] = $usage;
                    } else {
                        $agg_usages[$usage["type"] . ":" . $usage["id"]]["hist_nr"][] =
                            $usage["hist_nr"] ?? 0;
                    }
                }

                $records = [];
                $i = 0;
                foreach ($agg_usages as $k => $usage) {
                    $records[$i]["id"] = $k;

                    $cont_type = "";
                    if (is_int(strpos($usage["type"], ":"))) {
                        $us_arr = explode(":", $usage["type"]);
                        $usage["type"] = $us_arr[1];
                        $cont_type = $us_arr[0];
                    }

                    switch ($usage["type"]) {
                        case "pg":
                            $item = [];

                            switch ($cont_type) {
                                case "lm":
                                    $page_obj = new \ilLMPage($usage["id"]);
                                    $lm_obj = new \ilObjLearningModule($page_obj->getParentId(), false);
                                    $item["obj_type_txt"] = $this->lng->txt("obj_" . $cont_type);
                                    $item["obj_title"] = $lm_obj->getTitle();
                                    $item["sub_txt"] = $this->lng->txt("pg");
                                    $item["sub_title"] = \ilLMObject::_lookupTitle($page_obj->getId());
                                    $ref_id = $this->getFirstWritableRefId($lm_obj->getId());
                                    if ($ref_id > 0) {
                                        $item["obj_link"] = \ilLink::_getStaticLink($ref_id, "lm");
                                    }
                                    break;

                                case "wpg":
                                    $page_obj = new \ilWikiPage($usage["id"]);
                                    $item["obj_type_txt"] = $this->lng->txt("obj_wiki");
                                    $item["obj_title"] = \ilObject::_lookupTitle($page_obj->getParentId());
                                    $item["sub_txt"] = $this->lng->txt("pg");
                                    $item["sub_title"] = \ilWikiPage::lookupTitle($page_obj->getId());
                                    $ref_id = $this->getFirstWritableRefId($page_obj->getParentId());
                                    if ($ref_id > 0) {
                                        $item["obj_link"] = \ilLink::_getStaticLink($ref_id, "wiki");
                                    }
                                    break;

                                case "term":
                                    $page_obj = new \ilGlossaryDefPage($usage["id"]);
                                    $term_id = $page_obj->getId();
                                    $glo_id = \ilGlossaryTerm::_lookGlossaryID($term_id);
                                    $item["obj_type_txt"] = $this->lng->txt("obj_glo");
                                    $item["obj_title"] = \ilObject::_lookupTitle($glo_id);
                                    $item["sub_txt"] = $this->lng->txt("cont_term");
                                    $item["sub_title"] = \ilGlossaryTerm::_lookGlossaryTerm($term_id);
                                    $ref_id = $this->getFirstWritableRefId($page_obj->getParentId());
                                    if ($ref_id > 0) {
                                        $item["obj_link"] = \ilLink::_getStaticLink($ref_id, "glo");
                                    }
                                    break;

                                case "fold":
                                case "root":
                                case "crs":
                                case "grp":
                                case "cat":
                                case "cont":
                                    $item["obj_type_txt"] = $this->lng->txt("obj_" . $cont_type);
                                    $item["obj_title"] = \ilObject::_lookupTitle($usage["id"]);
                                    $ref_id = $this->getFirstWritableRefId($usage["id"]);
                                    if ($ref_id > 0) {
                                        $item["obj_link"] = \ilLink::_getStaticLink($ref_id, $cont_type);
                                    }
                                    break;

                                default:
                                    $item["obj_title"] = "Page " . $cont_type . ", " . $usage["id"];
                                    break;
                            }
                            break;

                        case "mep":
                            $item["obj_type_txt"] = $this->lng->txt("obj_mep");
                            $item["obj_title"] = \ilObject::_lookupTitle($usage["id"]);
                            $ref_id = $this->getFirstWritableRefId($usage["id"]);
                            if ($ref_id > 0) {
                                $item["obj_link"] = \ilLink::_getStaticLink($ref_id, "mep");
                            }
                            break;

                        case "map":
                            $item["obj_type_txt"] = $this->lng->txt("obj_mob");
                            $item["obj_title"] = \ilObject::_lookupTitle($usage["id"]);
                            $item["sub_txt"] = $this->lng->txt("cont_link_area");
                            break;

                        case "sqst":
                            $item["obj_type_txt"] = $this->lng->txt("cont_sqst");
                            $obj_id = \SurveyQuestion::lookupObjFi($usage["id"]);
                            $item["obj_title"] = \ilObject::_lookupTitle($obj_id);
                            $item["sub_txt"] = $this->lng->txt("question");
                            $item["sub_title"] = \SurveyQuestion::_getTitle($usage["id"]);
                            $ref_id = $this->getFirstWritableRefId($obj_id);
                            if ($ref_id > 0) {
                                $item["obj_link"] = \ilLink::_getStaticLink($ref_id);
                            }
                            break;

                        case "termref":
                            $item["obj_type_txt"] = $this->lng->txt("obj_glo");
                            $item["obj_title"] = \ilObject::_lookupTitle($usage["id"]);
                            $item["sub_txt"] = $this->lng->txt("glo_referenced_term");
                            $ref_id = $this->getFirstWritableRefId($usage["id"]);
                            if ($ref_id > 0) {
                                $item["obj_link"] = \ilLink::_getStaticLink($ref_id);
                            }
                            break;

                        default:
                            $item["obj_title"] = "Type " . $usage["type"] . ", " . $usage["id"];
                            break;
                    }

                    // show versions
                    if (is_array($usage["hist_nr"]) &&
                        (count($usage["hist_nr"]) > 1 || $usage["hist_nr"][0] > 0)) {
                        asort($usage["hist_nr"]);
                        $ver = $sep = "";
                        if ($usage["hist_nr"][0] == 0) {
                            array_shift($usage["hist_nr"]);
                            $usage["hist_nr"][] = 0;
                        }
                        foreach ($usage["hist_nr"] as $nr) {
                            if ($nr > 0) {
                                $ver .= $sep . $nr;
                            } else {
                                $ver .= $sep . $this->lng->txt("cont_current_version");
                            }
                            $sep = ", ";
                        }

                        $records[$i]["version"] = $ver;
                    }

                    if ($item["obj_type_txt"] != "") {
                        $records[$i]["type"] = $item["obj_type_txt"];
                    }

                    if ($usage["type"] != "clip") {
                        if ($item["obj_link"]) {
                            $records[$i]["object"] = $item["obj_title"];
                            $link = $this->ui_fac->link()->standard($this->lng->txt("cont_link"), $item["obj_link"]);
                            $records[$i]["link"] = $link;
                        } else {
                            $records[$i]["object"] = $item["obj_title"];
                        }

                        $sub_text = "";
                        if (($item["sub_txt"] ?? "") != "") {
                            $sub_text = $item["sub_txt"];
                            if (($item["sub_title"] ?? "") != "") {
                                $sub_text .= ": ";
                                $sub_text .= $item["sub_title"];
                            }
                            $records[$i]["sub_object"] = $sub_text;
                        }
                    } else {
                        $records[$i]["object"] = $this->lng->txt("cont_users_have_mob_in_clip1") .
                            " " . $usage["cnt"] . " " . $this->lng->txt("cont_users_have_mob_in_clip2");
                    }

                    $i++;
                }

                if ($order) {
                    $records = $this->orderRecords($records, $order);
                }

                if ($range) {
                    $records = $this->limitRecords($records, $range);
                }

                return $records;
            }

            protected function getFirstWritableRefId(int $obj_id): int
            {
                $ref_ids = \ilObject::_getAllReferences($obj_id);
                foreach ($ref_ids as $ref_id) {
                    if ($this->access->checkAccess("write", "", $ref_id)) {
                        return $ref_id;
                    }
                }
                return 0;
            }
        };

        return $data_retrieval;
    }
}
