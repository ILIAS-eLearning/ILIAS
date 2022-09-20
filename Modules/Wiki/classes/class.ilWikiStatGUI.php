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

use ILIAS\Wiki\Editing\EditingGUIRequest;

/**
 * Wiki statistics GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilWikiStatGUI
{
    protected EditingGUIRequest $request;
    protected ilCtrl $ctrl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected int $wiki_id;
    protected int $page_id;

    public function __construct(
        int $a_wiki_id,
        ?int $a_page_id = null
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->wiki_id = $a_wiki_id;
        $this->page_id = (int) $a_page_id;
        $this->request = $DIC
            ->wiki()
            ->internal()
            ->gui()
            ->editing()
            ->request();
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("view");

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    protected function viewToolbar(
        bool $a_is_initial = false
    ): ?array {
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $current_figure = $this->request->getStatFig();
        $current_time_frame = $this->request->getStatTfr();
        $current_scope = $this->request->getStatScp();

        $view = new ilSelectInputGUI($lng->txt("wiki_stat_figure"), "fig");
        $view->setOptions($this->page_id
                ? ilWikiStat::getFigureOptionsPage()
                : ilWikiStat::getFigureOptions());
        if ($current_figure) {
            $view->setValue($current_figure);
        } elseif ($a_is_initial) {
            // default
            $current_figure = $this->page_id
                ? ilWikiStat::KEY_FIGURE_WIKI_PAGE_CHANGES
                : ilWikiStat::KEY_FIGURE_WIKI_NUM_PAGES;
        }
        $ilToolbar->addInputItem($view, true);

        $options = array();
        $lng->loadLanguageModule("dateplaner");
        foreach (ilWikiStat::getAvailableMonths($this->wiki_id) as $month) {
            $parts = explode("-", $month);
            $options[$month] = ilCalendarUtil::_numericMonthToString((int) $parts[1]) .
                " " . $parts[0];
        }
        krsort($options);

        $tframe = new ilSelectInputGUI($lng->txt("month"), "tfr");
        $tframe->setOptions($options);
        if ($current_time_frame) {
            $tframe->setValue($current_time_frame);
        } elseif ($a_is_initial) {
            $opt = array_keys($options);
            $current_time_frame = array_shift($opt); // default
        }
        $ilToolbar->addInputItem($tframe, true);

        $scope = new ilSelectInputGUI($lng->txt("wiki_stat_scope"), "scp");
        $scope->setOptions(array(
            1 => "1 " . $lng->txt("month"),
            2 => "2 " . $lng->txt("months"),
            3 => "3 " . $lng->txt("months"),
            4 => "4 " . $lng->txt("months"),
            5 => "5 " . $lng->txt("months"),
            6 => "6 " . $lng->txt("months")
        ));
        if ($current_scope) {
            $scope->setValue($current_scope);
        } elseif ($a_is_initial) {
            $current_scope = 1; // default
        }
        $ilToolbar->addInputItem($scope, true);

        $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "view"));
        $ilToolbar->addFormButton($lng->txt("show"), "view");

        if ($current_figure && $current_time_frame && $current_scope) {
            $ilToolbar->addSeparator();
            $ilToolbar->addFormButton($lng->txt("export"), "export");

            return array(
                "figure" => $current_figure,
                "month" => $current_time_frame,
                "scope" => $current_scope
            );
        }
        return null;
    }

    protected function export(): void
    {
        $ilCtrl = $this->ctrl;

        $params = $this->viewToolbar();
        if ($params) {
            // data

            $tfr = explode("-", (string) $params["month"]);
            $day_from = date("Y-m-d", mktime(0, 0, 1, $tfr[1] - ($params["scope"] - 1), 1, $tfr[0]));
            $day_to = date("Y-m-d", mktime(0, 0, 1, $tfr[1] + 1, 0, $tfr[0]));
            unset($tfr);

            $chart_data = $this->getChartData($params["figure"], $params["scope"], $day_from, $day_to);


            // excel

            $period = ilDatePresentation::formatPeriod(
                new ilDate($day_from, IL_CAL_DATE),
                new ilDate($day_to, IL_CAL_DATE)
            );

            $filename = ilObject::_lookupTitle($this->wiki_id);
            if ($this->page_id) {
                $filename .= " - " . ilWikiPage::lookupTitle($this->page_id);
            }
            $filename .= " - " . ilWikiStat::getFigureTitle($params["figure"]) . " - " . $period;

            $excel = new ilExcel();
            $excel->addSheet($this->lng->txt("statistics"));

            $row = 1;
            foreach ($chart_data as $day => $value) {
                $excel->setCell($row, 0, $day);
                $excel->setCell($row++, 1, $value);
            }

            $excel->sendToClient($filename);
        }

        $ilCtrl->redirect($this, "view");
    }

    protected function initial(): void
    {
        $this->view(true);
    }

    protected function view(
        bool $a_is_initial = false
    ): void {
        $tpl = $this->tpl;
        $lng = $this->lng;

        $params = $this->viewToolbar($a_is_initial);
        if (is_array($params)) {
            // data

            $tfr = explode("-", (string) $params["month"]);
            $day_from = date("Y-m-d", mktime(0, 0, 1, $tfr[1] - ($params["scope"] - 1), 1, $tfr[0]));
            $day_to = date("Y-m-d", mktime(0, 0, 1, $tfr[1] + 1, 0, $tfr[0]));
            unset($tfr);

            $chart_data = $this->getChartData($params["figure"], $params["scope"], $day_from, $day_to);
            $list_data = $this->getListData();


            // render

            $vtpl = new ilTemplate("tpl.wiki_stat_list.html", true, true, "Modules/Wiki");

            $chart_panel = ilPanelGUI::getInstance();

            $vtpl->setVariable("CHART", $this->renderGraph($params["figure"], $chart_data));

            $vtpl->setCurrentBlock("row_bl");
            foreach ($list_data as $figure => $values) {
                $vtpl->setVariable("FIGURE", $figure);
                $vtpl->setVariable("YESTERDAY_VALUE", $values["yesterday"]);
                $vtpl->setVariable("TODAY_VALUE", $values["today"]);
                $vtpl->parseCurrentBlock();
            }

            $vtpl->setVariable("FIGURE_HEAD", $lng->txt("wiki_stat_figure"));
            $vtpl->setVariable("YESTERDAY_HEAD", $lng->txt("yesterday"));
            $vtpl->setVariable("TODAY_HEAD", $lng->txt("today"));

            $chart_panel->setHeading($lng->txt("statistics"));
            $chart_panel->setBody($vtpl->get());
            $chart_panel->setHeadingStyle(ilPanelGUI::HEADING_STYLE_SUBHEADING);

            $tpl->setContent($chart_panel->getHTML());
        }
    }

    protected function getChartData(
        int $a_figure,
        int $a_scope,
        string $a_from,
        string $a_to
    ): array {
        $data = array();

        $raw = $this->page_id
            ? ilWikiStat::getFigureDataPage($this->wiki_id, $this->page_id, $a_figure, $a_from, $a_to)
            : ilWikiStat::getFigureData($this->wiki_id, $a_figure, $a_from, $a_to);

        $parts = explode("-", $a_from);
        for ($loop = 0; $loop <= ($a_scope * 31); $loop++) {
            $current_day = date("Y-m-d", mktime(0, 0, 1, $parts[1], $parts[2] + $loop, $parts[0]));
            if ($current_day <= $a_to) {
                $data[$current_day] = (float) ($raw[$current_day] ?? 0);
            }
        }

        return $data;
    }

    protected function getListData(): array
    {
        $data = array();

        $today = date("Y-m-d");
        $yesterday = date("Y-m-d", strtotime("yesterday"));

        $all = $this->page_id
            ? ilWikiStat::getFigureOptionsPage()
            : ilWikiStat::getFigureOptions();
        foreach ($all as $figure => $title) {
            if ($this->page_id) {
                $tmp = ilWikiStat::getFigureDataPage($this->wiki_id, $this->page_id, $figure, $yesterday, $today);
            } else {
                $tmp = ilWikiStat::getFigureData($this->wiki_id, $figure, $yesterday, $today);
            }
            $data[$title] = array(
                "yesterday" => (float) ($tmp[$yesterday] ?? 0),
                "today" => (float) ($tmp[$today] ?? 0)
            );
        }

        return $data;
    }

    protected function renderGraph(
        int $a_figure,
        array $a_data
    ): string {
        $scope = ceil(count($a_data) / 31);

        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, "wikistat");
        $chart->setSize("100%", "400");
        $chart->setColors(array("#C0E0FF"));

        $legend = new ilChartLegend();
        $chart->setLegend($legend);

        // lines vs. bars
        if (in_array($a_figure, array(
            // wiki
            ilWikiStat::KEY_FIGURE_WIKI_NUM_PAGES
            ,ilWikiStat::KEY_FIGURE_WIKI_INTERNAL_LINKS
            ,ilWikiStat::KEY_FIGURE_WIKI_INTERNAL_LINKS_AVG
            ,ilWikiStat::KEY_FIGURE_WIKI_EXTERNAL_LINKS
            ,ilWikiStat::KEY_FIGURE_WIKI_EXTERNAL_LINKS_AVG
            ,ilWikiStat::KEY_FIGURE_WIKI_WORDS
            ,ilWikiStat::KEY_FIGURE_WIKI_WORDS_AVG
            ,ilWikiStat::KEY_FIGURE_WIKI_CHARS
            ,ilWikiStat::KEY_FIGURE_WIKI_CHARS_AVG
            ,ilWikiStat::KEY_FIGURE_WIKI_FOOTNOTES
            ,ilWikiStat::KEY_FIGURE_WIKI_FOOTNOTES_AVG
            ,ilWikiStat::KEY_FIGURE_WIKI_RATING_AVG
            // page
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_INTERNAL_LINKS
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_EXTERNAL_LINKS
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_WORDS
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_CHARS
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_FOOTNOTES
            ), true)) {
            $series = $chart->getDataInstance(ilChartGrid::DATA_LINES);
            $series->setLineSteps(true);
            $series->setFill(true, "#E0F0FF");
        } else {
            $series = $chart->getDataInstance(ilChartGrid::DATA_BARS);
            $series->setBarOptions(round(10 / ($scope * 2)) / 10);
        }
        $series->setLabel(ilWikiStat::getFigureTitle($a_figure));

        $labels = array();
        $x = 0;
        foreach ($a_data as $date => $value) {
            $series->addPoint($x, $value);

            $day = (int) substr($date, 8, 2);

            // match scale to scope
            if ($scope == 1) {
                // daily
                $labels[$x] = substr($date, 8, 2);
            } elseif ($scope == 2) {
                // weekly
                if (!($x % 7)) {
                    $labels[$x] = substr($date, 8, 2) . "." . substr($date, 5, 2) . ".";
                }
            } elseif ($day === 1 || $day === 15 || $x === count($a_data) - 1) {
                // 1st/15th
                $labels[$x] = substr($date, 8, 2) . "." . substr($date, 5, 2) . ".";
            }

            $x++;
        }

        $chart->addData($series);
        $chart->setTicks($labels, null, true);

        // int vs. float (averages)
        if (in_array($a_figure, array(
            // wiki
            ilWikiStat::KEY_FIGURE_WIKI_NUM_PAGES
            ,ilWikiStat::KEY_FIGURE_WIKI_NEW_PAGES
            ,ilWikiStat::KEY_FIGURE_WIKI_EDIT_PAGES
            ,ilWikiStat::KEY_FIGURE_WIKI_DELETED_PAGES
            ,ilWikiStat::KEY_FIGURE_WIKI_READ_PAGES
            ,ilWikiStat::KEY_FIGURE_WIKI_USER_EDIT_PAGES
            ,ilWikiStat::KEY_FIGURE_WIKI_NUM_RATING
            ,ilWikiStat::KEY_FIGURE_WIKI_INTERNAL_LINKS
            ,ilWikiStat::KEY_FIGURE_WIKI_EXTERNAL_LINKS
            ,ilWikiStat::KEY_FIGURE_WIKI_WORDS
            ,ilWikiStat::KEY_FIGURE_WIKI_CHARS
            ,ilWikiStat::KEY_FIGURE_WIKI_FOOTNOTES
            // page
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_CHANGES
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_USER_EDIT
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_READ
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_INTERNAL_LINKS
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_EXTERNAL_LINKS
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_WORDS
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_CHARS
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_FOOTNOTES
            ,ilWikiStat::KEY_FIGURE_WIKI_PAGE_RATINGS
            ), true)) {
            $chart->setYAxisToInteger(true);
        }

        return $chart->getHTML();
    }
}
