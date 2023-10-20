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

use ILIAS\MediaCast\StandardGUIRequest;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Component\Table;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\URLBuilderToken;

class ilMediaCastManageTableGUI implements Table\DataRetrieval
{
    protected $parent_obj;
    protected ilTemplate $tpl;
    protected URLBuilder $url_builder;
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\Data\Factory $df;
    protected string $parent_cmd;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    protected bool $preview;
    protected bool $playtime;
    protected ?ilObjMediaCast $mediacast;
    protected URLBuilderToken $action_parameter_token;
    protected URLBuilderToken $row_id_token;

    protected ilObjMediaCastGUI $parent_gui;
    protected \ILIAS\MediaObjects\MediaType\MediaTypeManager $media_type;
    protected bool $presentation_mode;
    protected StandardGUIRequest $request;
    protected ilAccessHandler $access;
    protected bool $downloadable = false;
    protected bool $edit_order;
    protected \ILIAS\DI\UIServices $ui;

    public function __construct(
        ilObjMediaCastGUI $parent_gui,
        string $parent_cmd = "",
        bool $a_edit_order = false,
        bool $a_presentation_mode = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->request = $DIC->mediaCast()
            ->internal()
            ->gui()
            ->standardRequest();
        $this->ui = $DIC->ui();
        $this->edit_order = $a_edit_order;
        $this->presentation_mode = $a_presentation_mode;
        $this->mediacast = $parent_gui->getObject();
        $this->downloadable = $this->mediacast->getDownloadable();

        $this->parent_gui = $parent_gui;
        $this->parent_cmd = $parent_cmd;
        $this->media_type = $DIC->mediaObjects()->internal()->domain()->mediaType();
        $this->df = new \ILIAS\Data\Factory();
        $this->http = $DIC->http();

        $this->playtime = ($this->mediacast->getViewMode() !== ilObjMediaCast::VIEW_IMG_GALLERY);
        $this->preview = ($this->mediacast->getViewMode() !== ilObjMediaCast::VIEW_PODCAST);

        $form_action = $this->df->uri(
            ILIAS_HTTP_PATH . '/' .
            $this->ctrl->getLinkTarget($this->parent_gui, $this->parent_cmd)
        );
        $this->url_builder = new URLBuilder($form_action);
        [$this->url_builder, $this->action_parameter_token, $this->row_id_token] =
            $this->url_builder->acquireParameters(
                ["mcst"], // namespace
                "table_action", //this is the actions's parameter name
                "ids"   //this is the parameter name to be used for row-ids
            );
    }

    protected function getColumns(): array
    {
        $f = $this->ui->factory();
        $c = $f->table()->column();
        $columns = [
            'title' => $c->text($this->lng->txt("title"))->withIsSortable(false),
            'type' => $c->text($this->lng->txt("type"))->withIsSortable(false),
            'size' => $c->text($this->lng->txt("size"))->withIsSortable(false)
        ];
        if ($this->playtime) {
            $columns['playtime'] = $c->text($this->lng->txt("mcst_play_time"))->withIsSortable(false);
        }
        $columns['creation_date'] = $c->text($this->lng->txt("created"))->withIsSortable(false);
        $columns['update_date'] = $c->text($this->lng->txt("last_update"))->withIsSortable(false);
        if ($this->preview) {
            $columns['preview'] = $c->statusIcon($this->lng->txt("preview"))->withIsSortable(false);
        }

        return $columns;
    }

    public function getRows(
        Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        foreach ($this->mediacast->getSortedItemsArray() as $item) {

            $row_id = (string) $item['id'];
            $mob = new ilObjMediaObject($item["mob_id"]);
            $med = $mob->getMediaItem("Standard");


            $data['title'] = $item["title"];
            $data['creation_date'] = ilDatePresentation::formatDate(new ilDateTime($item["creation_date"], IL_CAL_DATETIME));
            if ($item["update_date"] !== $item["creation_date"]) {
                $data['update_date'] =
                    ilDatePresentation::formatDate(new ilDateTime($item["update_date"], IL_CAL_DATETIME));
            } else {
                $data['update_date'] = "-";
            }
            if ($item["playtime"] !== "00:00:00") {
                $data['playtime'] = $item["playtime"];
            } else {
                $data['playtime'] = "-";
            }

            $file = ilObjMediaObject::_lookupItemPath($med->getMobId(), false, false, "Standard");
            if (is_file($file)) {
                $size = filesize($file);
                $size = sprintf("%.1f MB", $size / 1024 / 1024);
                $data['size'] = $size;
            } else {
                $data['size'] = "-";
            }
            $data['type'] = $med->getFormat();
            if ($mob->getVideoPreviewPic() !== "") {
                // workaround since we dont have image columns yet
                $image = $this->ui->factory()->image()->responsive($mob->getVideoPreviewPic(), $item["title"]);
                $html = $this->ui->renderer()->render($image);
                $html = str_replace("<img ", "<img style='max-width:150px;' ", $html);
                $data['preview'] = $html;
            } else {
                $data['preview'] = "";
            }


            yield $row_builder->buildDataRow($row_id, $data);
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return null;
    }

    public function handleCommand(): void
    {
        $action = $this->request->getTableAction($this->action_parameter_token->getName());
        if ($action !== "") {
            $action .= "Object";
            // currently done in request wrapper
            //$ids = $this->request->getTableIds($this->row_id_token->getName());
            $this->parent_gui->$action();
        }
    }

    public function get(): Table\Data
    {
        $f = $this->ui->factory();
        $a = $f->table()->action();

        $form_action = $this->df->uri(
            ILIAS_HTTP_PATH . '/' .
            $this->ctrl->getLinkTarget($this->parent_gui, $this->parent_cmd)
        );

        $url_builder = $this->url_builder;
        $action_parameter_token = $this->action_parameter_token;
        $row_id_token = $this->row_id_token;

        $actions["editCastItem"] = $a->single(
            $this->lng->txt("edit"),
            $url_builder->withParameter($action_parameter_token, "editCastItem"),
            $row_id_token
        );

        $actions["showCastItem"] = $a->single(
            $this->lng->txt("show"),
            $url_builder->withParameter($action_parameter_token, "showCastItem"),
            $row_id_token
        )->withAsync();

        if ($this->playtime) {
            $actions["determinePlaytime"] = $a->single(
                $this->lng->txt("mcst_det_playtime"),
                $url_builder->withParameter($action_parameter_token, "determinePlaytime"),
                $row_id_token
            );
        }

        if ($this->downloadable) {
            $actions["downloadItem"] = $a->single(
                $this->lng->txt("download"),
                $url_builder->withParameter($action_parameter_token, "downloadItem"),
                $row_id_token
            );
        }

        $actions["confirmItemDeletion"] = $a->standard(
            $this->lng->txt("delete"),
            $url_builder->withParameter($action_parameter_token, "confirmItemDeletion"),
            $row_id_token
        )->withAsync();

        $table = $f->table()
                   ->data($this->lng->txt("mcst_items"), $this->getColumns(), $this)
                   ->withActions($actions)
                   ->withRequest($this->http->request());
        return $table;
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $size = 0;
        $ui = $this->ui;

        $news_set = new ilSetting("news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");

        if ($this->presentation_mode) {
            $ilCtrl->setParameterByClass("ilobjmediacastgui", "presentation", "1");
        }

        // access
        if ($enable_internal_rss && !$this->presentation_mode) {
            $this->tpl->setCurrentBlock("access");
            $this->tpl->setVariable("TXT_ACCESS", $lng->txt("news_news_item_visibility"));
            if ($a_set["visibility"] == NEWS_PUBLIC) {
                $this->tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_public"));
            } else {
                $this->tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_users"));
            }
            $this->tpl->parseCurrentBlock();
        }

        $ilCtrl->setParameterByClass("ilobjmediacastgui", "item_id", "");

        if (ilObject::_exists($a_set["mob_id"])) {

            $mob = new ilObjMediaObject($a_set["mob_id"]);
            $med = $mob->getMediaItem("Standard");

            $this->tpl->setVariable(
                "VAL_DESCRIPTION",
                $a_set["content"]
            );

            $this->tpl->setVariable(
                "TXT_DURATION",
                $lng->txt("mcst_play_time")
            );

            if (!$this->edit_order) {
                if ($this->downloadable) {
                    $ilCtrl->setParameterByClass("ilobjmediacastgui", "item_id", $a_set["id"]);
                    // to keep always the order of the purposes
                    // iterate through purposes and display the according mediaitems
                    foreach (ilObjMediaCast::$purposes as $purpose) {
                        $a_mob = $mob->getMediaItem($purpose);
                        if (!is_object($a_mob)) {
                            continue;
                        }
                        $ilCtrl->setParameterByClass("ilobjmediacastgui", "purpose", $a_mob->getPurpose());
                        $file = ilObjMediaObject::_lookupItemPath($a_mob->getMobId(), false, false, $a_mob->getPurpose());
                        $this->tpl->setCurrentBlock("downloadable");
                        $this->tpl->setVariable("TXT_DOWNLOAD", $lng->txt("mcst_download_" . strtolower($a_mob->getPurpose())));
                        $this->tpl->setVariable("CMD_DOWNLOAD", $ilCtrl->getLinkTargetByClass("ilobjmediacastgui", "downloadItem"));
                        $this->tpl->parseCurrentBlock();
                    }
                }

                // the news id will be used as player id, see also ilObjMediaCastGUI
                $event_url = ($this->presentation_mode)
                    ? $ilCtrl->getLinkTarget($this->parent_obj, "handlePlayerEvent", "", true, false)
                    : "";
                if (!is_null($med)) {
                    if ($med->getLocationType() === "Reference") {
                        $file = $med->getLocation();
                        if (in_array($med->getFormat(), ["video/vimeo", "video/youtube"])) {
                            if (!is_int(strpos($file, "?"))) {
                                $file .= "?controls=0";
                            } else {
                                $file .= "&controls=0";
                            }
                        }
                    } else {
                        $file = ilWACSignedPath::signFile(
                            ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation()
                        );
                    }
                    $comp = null;
                    if ($this->media_type->isAudio($med->getFormat())) {
                        $comp = $ui->factory()->player()->audio(
                            $file,
                            ""
                        );
                    } elseif ($this->media_type->isVideo($med->getFormat())) {
                        $comp = $ui->factory()->player()->video(
                            $file
                        );
                    } elseif ($this->media_type->isImage($med->getFormat())) {
                        $comp = $ui->factory()->image()->responsive($file, "");
                    }
                    if (!is_null($comp)) {
                        $this->tpl->setVariable("PLAYER", $ui->renderer()->render($comp));
                    }
                }

                // edit link
                $ilCtrl->setParameterByClass("ilobjmediacastgui", "item_id", $a_set["id"]);
                if ($ilAccess->checkAccess("write", "", $this->request->getRefId()) &&
                    !$this->presentation_mode) {

                    if (!is_int(strpos($med->getFormat(), "image/"))) {
                        $this->tpl->setVariable("TXT_DET_PLAYTIME", $lng->txt("mcst_det_playtime"));
                        $this->tpl->setVariable(
                            "CMD_DET_PLAYTIME",
                            $ilCtrl->getLinkTargetByClass("ilobjmediacastgui", "determinePlaytime")
                        );
                    }
                    $this->tpl->parseCurrentBlock();

                    $this->tpl->setCurrentBlock("edit_checkbox");
                    $this->tpl->setVariable("VAL_ID", $a_set["id"]);
                    $this->tpl->parseCurrentBlock();
                    //					$this->tpl->touchBlock("contrl_col");
                }
            } else {
                $this->tpl->setCurrentBlock("edit_order");
                $this->tpl->setVariable("VAL_ID", $a_set["id"]);
                $this->tpl->setVariable("VAL_ORDER", $a_set["order"]);
                $this->tpl->parseCurrentBlock();
                //				$this->tpl->touchBlock("contrl_col");
            }

            // download and play counter
            if (!$this->presentation_mode) {
                if ($a_set["mob_cnt_download"] > 0) {
                    $this->tpl->setCurrentBlock("prop");
                    $this->tpl->setVariable("TXT_PROP", $lng->txt("mcst_download_cnt"));
                    $this->tpl->setVariable("VAL_PROP", $a_set["mob_cnt_download"]);
                    $this->tpl->parseCurrentBlock();
                }
                if ($a_set["mob_cnt_play"] > 0) {
                    $this->tpl->setCurrentBlock("prop");
                    $this->tpl->setVariable("TXT_PROP", $lng->txt("mcst_play_cnt"));
                    $this->tpl->setVariable("VAL_PROP", $a_set["mob_cnt_play"]);
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
    }
}
