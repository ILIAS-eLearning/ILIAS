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

namespace ILIAS\LearningModule\Menu;

use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMMenuGUI
{
    protected bool $offline;
    protected \ilLMPresentationService $service;
    protected \ilCtrl $ctrl;
    protected \ILIAS\DI\UIServices $ui;
    protected \ilObjLearningModule $lm;
    protected \ilLanguage $lng;
    protected \ilAccessHandler $access;
    protected \ilObjUser $user;

    public function __construct(
        \ilLMPresentationService $lm_pres_service
    ) {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->service = $lm_pres_service;
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->lm = $this->service->getLearningModule();
        $this->offline = $lm_pres_service->getPresentationStatus()->offline();
        $this->user = $DIC->user();
    }

    public function getEntries(): array
    {
        $ui = $this->ui;
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $access = $this->access;
        $user = $this->user;

        $entries = [];

        $ctrl->setParameterByClass("illmpresentationgui", 'ref_id', $this->lm->getRefId());

        // print selection
        if ($this->lm->isActivePrintView() && $access->checkAccess("read", "", $this->lm->getRefId())) {
            if (!$this->offline) {
                $ui->mainTemplate()->addJavaScript("./Services/Form/js/Form.js");
                $modal = $ui->factory()->modal()->roundtrip(
                    $lng->txt("cont_print_view"),
                    $ui->factory()->legacy('some modal')
                )->withAsyncRenderUrl($this->ctrl->getLinkTargetByClass("illmpresentationgui", "showPrintViewSelection"));

                $entries[] = [
                    "label" => $this->lng->txt("cont_print_view"),
                    "signal" => $modal->getShowSignal(),
                    "modal" => $modal,
                    "on_load" => ""
                ];
            }
        }

        // download
        if ($user->getId() == ANONYMOUS_USER_ID) {
            $is_public = $this->lm->isActiveDownloadsPublic();
        } else {
            $is_public = true;
        }

        if ($this->lm->isActiveDownloads() && !$this->offline && $is_public &&
            $access->checkAccess("read", "", $this->lm->getRefId())) {
            $modal = $ui->factory()->modal()->roundtrip(
                $lng->txt("download"),
                $ui->factory()->legacy('some modal')
            )->withAsyncRenderUrl($this->ctrl->getLinkTargetByClass("illmpresentationgui", "showDownloadList"));
            $entries[] = [
                "label" => $this->lng->txt("download"),
                "signal" => $modal->getShowSignal(),
                "modal" => $modal,
                "on_load" => ""
            ];
        }

        // user defined menu entries
        $menu_editor = new \ilLMMenuEditor();
        $menu_editor->setObjId($this->lm->getId());

        $cust_menu = $menu_editor->getMenuEntries(true);
        $generator = new SignalGenerator();
        if (count($cust_menu) > 0 && $access->checkAccess("read", "", $this->lm->getRefId())) {
            foreach ($cust_menu as $entry) {
                // build goto-link for internal resources
                if ($entry["type"] == "intern") {
                    $entry["link"] = ILIAS_HTTP_PATH . "/goto.php?target=" . $entry["link"];
                }

                // add http:// prefix if not exist
                if (!strstr($entry["link"], '://') && !strstr($entry["link"], 'mailto:')) {
                    $entry["link"] = "https://" . $entry["link"];
                }

                if (!strstr($entry["link"], 'mailto:')) {
                    $entry["link"] = \ilUtil::appendUrlParameterString($entry["link"], "ref_id=" . $this->lm->getRefId());
                }

                $signal = $generator->create();

                $entries[] = [
                    "label" => $entry["title"],
                    "signal" => $signal,
                    "modal" => null,
                    "on_load" => "$(document).on('" .
                        $signal->getId() .
                        "', function(event, signalData) {il.LearningModule.openMenuLink('" . $entry["link"] . "');});"
                ];
            }
        }


        return $entries;
    }
}
