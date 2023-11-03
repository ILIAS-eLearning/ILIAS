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

use ILIAS\Filesystem\Stream\Streams;

/**
 * Learning history main GUI class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningHistoryGUI
{
    public const TAB_ID_LEARNING_HISTORY = 'lhist_learning_history';
    public const TAB_ID_MY_CERTIFICATES = 'certificates';
    public const MAX = 50;
    protected \ILIAS\HTTP\Services $http;
    protected ?int $to;
    protected ?int $from;
    protected int $user_id;
    protected ilAccessHandler $access;
    protected ilLearningHistoryService $lhist_service;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilLanguage $lng;
    protected \ILIAS\DI\UIServices $ui;
    protected ilSetting $certificateSettings;
    protected ilTabsGUI $tabs;
    protected bool $show_more = false;
    protected int $last_ts = 0;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();

        $this->lhist_service = $DIC->learningHistory();
        $this->ui = $this->lhist_service->ui();
        $this->main_tpl = $this->ui->mainTemplate();
        $this->lng = $this->lhist_service->language();
        $this->access = $this->lhist_service->access();
        $this->tabs = $DIC->tabs();

        $this->lng->loadLanguageModule("lhist");

        $this->user_id = $this->lhist_service->user()->getId();

        $this->certificateSettings = new ilSetting("certificate");

        $request = $this->lhist_service->request();
        $to = $request->getToTS();
        $this->from = null;
        $this->to = ($to > 0)
            ? $to
            : null;

        $this->main_tpl->addJavaScript("./Services/LearningHistory/js/LearningHistory.js");
        $this->http = $DIC->http();
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("show", "renderAsync"))) {
                    $this->$cmd();
                }
        }
    }

    protected function show(): void
    {
        $main_tpl = $this->main_tpl;
        $lng = $this->lng;
        $f = $this->ui->factory();
        $renderer = $this->ui->renderer();

        $html = $this->getHistoryHtml($this->from, $this->to);

        if ($html !== "") {
            $main_tpl->setContent($html);
        } else {
            $main_tpl->setContent(
                $renderer->render(
                    $f->messageBox()->info($lng->txt("lhist_no_entries"))
                )
            );
        }
    }

    protected function renderAsync(): void
    {
        $response["timeline"] = $this->renderTimeline($this->from, $this->to);
        $response["more"] = $this->show_more ? $this->renderButton() : "";
        $this->send(json_encode($response, JSON_THROW_ON_ERROR));
    }

    /**
     * @param string $output
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    protected function send(string $output): void
    {
        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($output)
        ));
        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * @throws ilCtrlException
     */
    public function getEmbeddedHTML(
        ?int $from = null,
        ?int $to = null,
        ?array $classes = null,
        ?string $a_mode = null
    ): string {
        return $this->ctrl->getHTML($this, ["from" => $from, "to" => $to, "classes" => $classes, "mode" => $a_mode]);
    }

    /**
     * Get HTML
     */
    public function getHTML(array $par): string
    {
        return $this->getHistoryHtml($par["from"], $par["to"], $par["classes"], $par["mode"]);
    }

    /**
     * Get history html
     */
    protected function getHistoryHtml(
        ?int $from = null,
        ?int $to = null,
        ?array $classes = null,
        ?string $mode = null
    ): string {
        $tpl = new ilTemplate("tpl.timeline.html", true, true, "Services/LearningHistory");

        $tpl->setVariable("TIMELINE", $this->renderTimeline($from, $to, $classes, $mode));

        if ($this->show_more && $mode !== "print") {
            $tpl->setCurrentBlock("show_more");
            $tpl->setVariable("SHOW_MORE_BUTTON", $this->renderButton());
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * render timeline
     */
    protected function renderTimeline(
        int $from = null,
        int $to = null,
        array $classes = null,
        string $mode = null
    ): string {
        $collector = $this->lhist_service->factory()->collector();
        $ctrl = $this->ctrl;

        $to = (is_null($to))
            ? time()
            : $to;
        $from = (is_null($from))
            ? $to - (365 * 24 * 60 * 60)
            : $from;

        $entries = $collector->getEntries($from, $to, $this->user_id, $classes);

        $timeline = ilTimelineGUI::getInstance();
        $cnt = 0;

        reset($entries);
        while (($e = current($entries)) && $cnt < self::MAX) {
            /** @var ilLearningHistoryEntry $e */
            $timeline->addItem(new ilLearningHistoryTimelineItem(
                $e,
                $this->ui,
                $this->user_id,
                $this->access,
                $this->lhist_service->repositoryTree()
            ));
            $this->last_ts = $e->getTimestamp();
            next($entries);
            $cnt++;
        }

        $html = "";
        if (count($entries) > 0) {
            $html = $timeline->render($ctrl->isAsynch());
        }

        $this->show_more = (count($entries) > $cnt);

        return $html;
    }

    protected function renderButton(): string
    {
        $ctrl = $this->ctrl;
        $f = $this->ui->factory();
        $renderer = $this->ui->renderer();
        $ctrl->setParameter($this, "to_ts", $this->last_ts - 1);
        $url = $ctrl->getLinkTarget($this, "renderAsync", "", true);

        $button = $f->button()->standard($this->lng->txt("lhist_show_more"), "")
            ->withLoadingAnimationOnClick(true)
            ->withOnLoadCode(static function ($id) use ($url): string {
                return "il.LearningHistory.initShowMore('$id', '" . $url . "');";
            });
        if ($ctrl->isAsynch()) {
            return $renderer->renderAsync($button);
        }

        return $renderer->render($button);
    }
}
