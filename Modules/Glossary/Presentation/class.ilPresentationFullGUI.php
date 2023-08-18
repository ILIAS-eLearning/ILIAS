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

use ILIAS\UI;
use ILIAS\Glossary\Presentation;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilPresentationFullGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs_gui;
    protected ilNavigationHistory $nav_history;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected $parent_obj;
    protected ilObjGlossary $glossary;
    protected bool $offline = false;
    protected int $tax_node = 0;
    protected Presentation\PresentationGUIRequest $request;
    protected Presentation\PresentationManager $manager;
    protected \ilUIFilterService $filter_service;
    protected ?array $filter_data = null;

    public function __construct(
        $parent_object,
        ilObjGlossary $glossary,
        bool $offline,
        int $tax_node = 0,
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs_gui = $DIC->tabs();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->parent_obj = $parent_object;
        $this->glossary = $glossary;
        $this->offline = $offline;
        $this->tax_node = $tax_node;
        $this->request = $DIC->glossary()
            ->internal()
            ->gui()
            ->presentation()
            ->request();
        $this->manager = $DIC->glossary()
            ->internal()
            ->domain()
            ->presentation($this->glossary);
        $this->filter_service = $DIC->uiService()->filter();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd("show");
                $ret = $this->$cmd();
                break;
        }
    }

    protected function determinePageLength(): int
    {
        if ($this->request->getPageLength() === -1) {
            $page_length = (int) $this->user->getPref("hits_per_page") ?: 9999;
            $this->manager->setSessionPageLength($page_length);
        } elseif ($this->request->getPageLength() > 0) {
            $page_length = $this->request->getPageLength();
            $this->manager->setSessionPageLength($page_length);
        } elseif ($this->manager->getSessionPageLength() > 0) {
            $page_length = $this->manager->getSessionPageLength();
        } else {
            $page_length = (int) $this->user->getPref("hits_per_page") ?: 9999;
        }

        return $page_length;
    }

    public function show(): void
    {
        $this->ctrl->setParameter($this, "term_id", "");
        $this->tabs_gui->activateTab("terms");

        $this->nav_history->addItem(
            $this->glossary->getRefId(),
            $this->ctrl->getLinkTargetByClass("ilGlossaryPresentationGUI", "listTerms"),
            "glo"
        );

        $filter = $this->initFilter();
        $this->filter_data = $this->filter_service->getData($filter);
        $this->manager->setSessionLetter($this->filter_data["letter"] ?? "");

        $panel = $this->initPanel();

        $this->tpl->setContent($this->ui_ren->render([$filter, $panel]));
        $this->tpl->setPermanentLink("glo", $this->glossary->getRefId());
    }

    protected function initFilter(): UI\Component\Input\Container\Filter\Standard
    {
        $first_letters = $this->glossary->getFirstLetters($this->tax_node);
        $session_letter = ilUtil::stripSlashes($this->manager->getSessionLetter());
        if (!empty($session_letter) && !in_array($session_letter, $first_letters)) {
            $first_letters[$session_letter] = $session_letter;
        }

        $filter = $this->filter_service->standard(
            self::class . "_filter_" . $this->glossary->getRefId(),
            $this->ctrl->getLinkTarget($this, "show"),
            [
                "letter" => $this->ui_fac->input()->field()->select(
                    $this->lng->txt("glo_term_letter"),
                    $first_letters
                ),
                "term" => $this->ui_fac->input()->field()->text($this->lng->txt("cont_term")),
                "definition" => $this->ui_fac->input()->field()->text($this->lng->txt("cont_definition"))
            ],
            [true, true, true],
            true,
            true
        );

        return $filter;
    }

    protected function initPanel(int $page_length = 0): UI\Component\Panel\Panel
    {
        if (!$page_length) {
            $page_length = $this->determinePageLength();
        }

        $terms = $this->glossary->getTermList(
            $this->filter_data["term"] ?? "",
            $this->filter_data["letter"] ?? "",
            $this->filter_data["definition"] ?? "",
            $this->tax_node,
            false,
            false,
            null,
            false,
            true
        );

        $terms_sliced = array_slice(
            $terms,
            $this->request->getCurrentPage() * $page_length,
            $page_length
        );

        $subs = [];
        foreach ($terms_sliced as $term) {
            $subs[] = $this->ui_fac->panel()->sub(
                $term["term"],
                $this->ui_fac->legacy($this->parent_obj->listDefinitions(
                    $this->request->getRefId(),
                    (int) $term["id"],
                    true,
                    false
                ))
            );
        }

        $panel = $this->ui_fac->panel()->standard($this->lng->txt("cont_terms"), $subs);
        if (!$this->offline) {
            $pagination = $this->ui_fac->viewControl()->pagination()
                            ->withTargetURL(
                                $this->ctrl->getLinkTarget($this, "show"),
                                "current_page"
                            )
                            ->withTotalEntries(count($terms))
                            ->withPageSize($page_length)
                            ->withMaxPaginationButtons(5)
                            ->withCurrentPage($this->request->getCurrentPage());

            $dropdown = $this->initDropdown($page_length);

            $panel = $panel
                        ->withViewControls([$pagination])
                        ->withActions($dropdown);
        }

        return $panel;
    }

    protected function initDropdown(int $page_length): UI\Component\Dropdown\Dropdown
    {
        $hpp = ($this->user->getPref("hits_per_page") != 9999)
            ? $this->user->getPref("hits_per_page")
            : $this->lng->txt("no_limit");

        $terms_per_page_sel = [-1 => $this->lng->txt("default") . " (" . $hpp . ")", 5 => "5", 10 => "10",
                                15 => "15", 20 => "20", 30 => "30", 40 => "40", 50 => "50", 100 => "100"];

        foreach ($terms_per_page_sel as $count => $count_text) {
            $this->ctrl->setParameter($this->parent_obj, "page_length", $count);
            $items[] = $this->ui_fac->button()->shy($count_text, $this->ctrl->getLinkTarget($this, "show"));
            $this->ctrl->setParameter($this->parent_obj, "page_length", "");
        }
        $dropdown = $this->ui_fac->dropdown()->standard($items)
                        ->withLabel($page_length . " " . $this->lng->txt("glo_terms_per_page"));

        return $dropdown;
    }

    public function renderPanelForOffline(): string
    {
        $panel = $this->initPanel(9999);
        $panel_html = $this->ui_ren->render($panel);
        //$this->tpl->setVariable("ADM_CONTENT", $panel_html);
        //return $this->tpl->printToString();
        return $panel_html;
    }
}
