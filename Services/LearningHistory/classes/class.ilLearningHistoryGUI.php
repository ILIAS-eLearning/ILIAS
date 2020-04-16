<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history main GUI class
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
class ilLearningHistoryGUI
{
    const TAB_ID_LEARNING_HISTORY = 'lhist_learning_history';
    const TAB_ID_MY_CERTIFICATES = 'certificates';
    const MAX = 50;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $main_tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /** @var ilSetting */
    protected $certificateSettings;

    /** @var ilTabsGUI */
    protected $tabs;

    /** @var bool */
    protected $show_more = false;

    /** @var int */
    protected $last_ts = 0;

    /**
     * Constructor
     */
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

        $this->from = null;
        $this->to = ((int) $_GET["to_ts"] > 0)
            ? (int) $_GET["to_ts"]
            : null;

        $this->main_tpl->addJavaScript("./Services/LearningHistory/js/LearningHistory.js");
    }

    /**
     * Set user id
     *
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }


    /**
     * Execute command
     */
    public function executeCommand()
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


    /**
     * Show
     */
    protected function show()
    {
        $main_tpl = $this->main_tpl;
        $lng = $this->lng;
        $f = $this->ui->factory();
        $renderer = $this->ui->renderer();

        $html = $this->getHistoryHtml($this->from, $this->to);

        if ($html != "") {
            $main_tpl->setContent($html);
        } else {
            $main_tpl->setContent(
                $renderer->render(
                    $f->messageBox()->info($lng->txt("lhist_no_entries"))
                )
            );
        }
    }

    /**
     * Render Async
     */
    protected function renderAsync()
    {
        $response["timeline"] = $this->renderTimeline($this->from, $this->to);
        $response["more"] = $this->show_more ? $this->renderButton() : "";
        echo json_encode($response);
        exit;
    }

    /**
     * Get HTML
     *
     * @param null $from
     * @param null $to
     * @param null $classes
     * @return string
     * @throws ilCtrlException
     */
    public function getEmbeddedHTML($from = null, $to = null, $classes = null, $a_mode = null)
    {
        $ctrl = $this->ctrl;

        return $ctrl->getHTML($this, ["from" => $from, "to" => $to, "classes" => $classes, "mode" => $a_mode]);
    }

    /**
     * Get HTML
     *
     * @param
     * @return string
     */
    public function getHTML($par)
    {
        return $this->getHistoryHtml($par["from"], $par["to"], $par["classes"], $par["mode"]);
    }
    
    /**
     * Get history html
     *
     * @return string
     */
    protected function getHistoryHtml($from = null, $to = null, $classes = null, $mode = null)
    {
        $tpl = new ilTemplate("tpl.timeline.html", true, true, "Services/LearningHistory");

        $tpl->setVariable("TIMELINE", $this->renderTimeline($from, $to, $classes, $mode));

        if ($this->show_more && $mode != "print") {
            $tpl->setCurrentBlock("show_more");
            $tpl->setVariable("SHOW_MORE_BUTTON", $this->renderButton());
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * render timeline
     *
     * @param int $from unix timestamp
     * @param int $to unix timestamp
     * @param array $classes
     * @return string
     */
    protected function renderTimeline(int $from = null, int $to = null, array $classes = null, string $mode = null) : string
    {
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
        /** @var ilLearningHistoryEntry $e */
        while (($e = current($entries)) && $cnt < self::MAX) {
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


    /**
     * render Button
     */
    protected function renderButton()
    {
        $ctrl = $this->ctrl;
        $f = $this->ui->factory();
        $renderer = $this->ui->renderer();
        $ctrl->setParameter($this, "to_ts", $this->last_ts - 1);
        $url = $ctrl->getLinkTarget($this, "renderAsync", "", true);

        $button = $f->button()->standard($this->lng->txt("lhist_show_more"), "")
            ->withLoadingAnimationOnClick(true)
            ->withOnLoadCode(function ($id) use ($url) {
                return "il.LearningHistory.initShowMore('$id', '" . $url . "');";
            });
        if ($ctrl->isAsynch()) {
            return $renderer->renderAsync($button);
        } else {
            return $renderer->render($button);
        }
    }
}
