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

use ILIAS\UI;
use ILIAS\Glossary\Presentation;
use ILIAS\UI\Component\Input\Container\Filter;
use ILIAS\UI\Component\Input\Container\ViewControl\ViewControl;
use ILIAS\UI\Component\Table;
use ILIAS\UI\Implementation\Component\Input\ViewControl\Pagination;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilPresentationTableGUI
{
    protected \ILIAS\Style\Content\Service $content_style;
    protected ilObjUser $user;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs_gui;
    protected ilNavigationHistory $nav_history;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected ServerRequestInterface $request;
    protected $parent_obj;
    protected ilObjGlossary $glossary;
    protected bool $offline = false;
    protected int $tax_node = 0;
    protected Presentation\PresentationGUIRequest $pres_gui_request;
    protected Presentation\PresentationManager $manager;
    protected \ilUIFilterService $filter_service;
    protected \ILIAS\AdvancedMetaData\Services\Services $adv_md_service;
    protected ?array $filter_data = null;
    protected array $terms = [];

    public function __construct(
        $parent_object,
        ilObjGlossary $glossary,
        bool $offline,
        int $tax_node = 0
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
        $this->request = $DIC->http()->request();
        $this->parent_obj = $parent_object;
        $this->glossary = $glossary;
        $this->offline = $offline;
        $this->tax_node = $tax_node;
        $this->pres_gui_request = $DIC->glossary()
                                      ->internal()
                                      ->gui()
                                      ->presentation()
                                      ->request();
        $this->manager = $DIC->glossary()
                             ->internal()
                             ->domain()
                             ->presentation($this->glossary);
        $this->filter_service = $DIC->uiService()->filter();
        $this->adv_md_service = new \ILIAS\AdvancedMetaData\Services\Services();
        $this->content_style = $DIC->contentStyle();
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

    public function show(): void
    {
        $this->ctrl->setParameter($this, "term_id", "");
        $this->tabs_gui->activateTab("terms");

        $this->nav_history->addItem(
            $this->glossary->getRefId(),
            $this->ctrl->getLinkTargetByClass("ilGlossaryPresentationGUI", "listTerms"),
            "glo"
        );

        $this->content_style->gui()->addCss(
            $this->tpl,
            $this->glossary->getRefId()
        );

        $filter = $this->initFilter();
        $this->filter_data = $this->filter_service->getData($filter);
        $this->manager->setSessionLetter($this->filter_data["letter"] ?? "");

        $adt_search_bridges = $this->getADTSearchBridges();
        $this->initTerms($adt_search_bridges);

        $view_control = $this->initViewControl();
        if ($vc_data = $view_control->getData()) {
            $this->setStartAndLengthForViewControl($vc_data);
        }

        $pres_table = $this->initPresentationTable();

        $this->tpl->setContent($this->ui_ren->render([$filter, $view_control, $pres_table]));
        $this->tpl->setPermanentLink("glo", $this->glossary->getRefId());
    }

    protected function applyFilter(): void
    {
        $this->manager->setSessionViewControlStart(0);
        $this->show();
    }

    protected function initTerms(array $adt_search_bridges): void
    {
        $this->terms = $this->glossary->getTermList(
            $this->filter_data["term"] ?? "",
            $this->filter_data["letter"] ?? "",
            $this->filter_data["definition"] ?? "",
            $this->tax_node,
            true,
            true,
            $adt_search_bridges,
            false,
            true
        );
    }

    protected function getADTSearchBridges(): array
    {
        $bridges = [];
        if (is_array($this->filter_data)) {
            foreach ($this->filter_data as $input_key => $value) {
                if (substr($input_key, 0, 7) != "adv_md_") {
                    continue;
                }
                if (!$value) {
                    continue;
                }

                $field_id = substr($input_key, 7);
                $field = \ilAdvancedMDFieldDefinition::getInstance((int) $field_id);
                $field_form = \ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance(
                    $field->getADTDefinition(),
                    true,
                    false
                );

                if (is_array($value)) {
                    switch (true) {
                        case $field_form instanceof ilADTDateSearchBridgeRange:
                            $start = $value[0];
                            $end = $value[1];
                            if ($start) {
                                $field_form->getLowerADT()->setDate(new ilDate($start, IL_CAL_DATE));
                            }
                            if ($end) {
                                $field_form->getUpperADT()->setDate(new ilDate($end, IL_CAL_DATE));
                            }
                            if ($start || $end) {
                                $bridges[$field_id] = $field_form;
                            }
                            break;
                        case $field_form instanceof ilADTDateTimeSearchBridgeRange:
                            if ($value[0]) {
                                $start = strtotime($value[0]);
                                $field_form->getLowerADT()->setDate(new ilDateTime($start, IL_CAL_UNIX));
                            }
                            if ($value[1]) {
                                $end = strtotime($value[1]);
                                $field_form->getUpperADT()->setDate(new ilDateTime($end, IL_CAL_UNIX));
                            }
                            if ($value[0] || $value[1]) {
                                $bridges[$field_id] = $field_form;
                            }
                            break;
                        case $field_form instanceof ilADTEnumSearchBridgeMulti:
                            $field_form->getADT()->setSelections($value);
                            $bridges[$field_id] = $field_form;
                            break;
                    }
                } else {
                    switch (true) {
                        case $field_form instanceof ilADTTextSearchBridgeSingle:
                            $field_form->getADT()->setText($value);
                            $bridges[$field_id] = $field_form;
                            break;
                        case $field_form instanceof ilADTFloatSearchBridgeSingle:
                        case $field_form instanceof ilADTIntegerSearchBridgeSingle:
                            // numeric metadata currently not supported as filter inputs
                            $field_form->getADT()->setNumber($value);
                            $bridges[$field_id] = $field_form;
                            break;
                        case $field_form instanceof ilADTEnumSearchBridgeSingle:
                            $field_form->getADT()->setSelection($value);
                            $bridges[$field_id] = $field_form;
                            break;
                        case $field_form instanceof ilADTExternalLinkSearchBridgeSingle:
                            $field_form->getADT()->setUrl($value);
                            $bridges[$field_id] = $field_form;
                            break;
                        case $field_form instanceof ilADTInternalLinkSearchBridgeSingle:
                            $field_form->getADT()->setTargetRefId(1);
                            $field_form->setTitleQuery($value);
                            $bridges[$field_id] = $field_form;
                            break;
                    }
                }
            }
        }

        return $bridges;
    }

    protected function initFilter(): Filter\Standard
    {
        $first_letters = $this->glossary->getFirstLetters($this->tax_node);
        $session_letter = ilUtil::stripSlashes($this->manager->getSessionLetter());
        if (!empty($session_letter) && !in_array($session_letter, $first_letters)) {
            $first_letters[$session_letter] = $session_letter;
        }

        $default_inputs = [
            "letter" => $this->ui_fac->input()->field()->select(
                $this->lng->txt("glo_term_letter"),
                $first_letters
            ),
            "term" => $this->ui_fac->input()->field()->text($this->lng->txt("cont_term")),
            "definition" => $this->ui_fac->input()->field()->text($this->lng->txt("cont_definition"))
        ];
        $adv_md_inputs = $this->adv_md_service->forSubObjects(
            "glo",
            $this->glossary->getRefId(),
            "term"
        )->inFilter()->getFilterInputs();
        $inputs = $default_inputs + $adv_md_inputs;

        $default_inputs_active = [true, true, true];
        $adv_md_inputs_active = [];
        for ($i = 0; $i < count($adv_md_inputs); $i++) {
            $adv_md_inputs_active[] = false;
        }
        $inputs_active = array_merge($default_inputs_active, $adv_md_inputs_active);

        $filter = $this->filter_service->standard(
            self::class . "_filter_" . $this->glossary->getRefId(),
            $this->ctrl->getLinkTarget($this, "applyFilter"),
            $inputs,
            $inputs_active,
            false,
            true
        );

        return $filter;
    }

    protected function initPresentationTable(bool $offline = false): Table\Presentation
    {
        $vc_start = 0;
        $vc_length = 9999;
        if (!$this->offline) {
            $vc_start = $this->manager->getSessionViewControlStart();
            $vc_length = $this->manager->getSessionViewControlLength();
        }

        $terms_sliced = array_slice(
            $this->terms,
            $vc_start,
            $vc_length
        );

        $data = [];
        foreach ($terms_sliced as $term) {
            $data[] = [
                "term_id" => (int) $term["id"],
                "term" => $term["term"],
                "short_txt" => $this->getShortTextForTerm((int) $term["id"]),
                "definition" => $this->parent_obj->listDefinitions(
                    $this->pres_gui_request->getRefId(),
                    (int) $term["id"],
                    true,
                    false,
                    ilPageObjectGUI::PRESENTATION,
                    false
                )
            ];
        }

        $table = $this->ui_fac->table()->presentation(
            $this->lng->txt("cont_terms"),
            [],
            function ($row, array $record, $ui_factory) {
                $mdgui = new ilObjectMetaDataGUI($this->glossary, "term", $record["term_id"]);
                $adv_term_sets = $this->adv_md_service->forObject(
                    "glo",
                    $this->glossary->getRefId(),
                    "term",
                    $record["term_id"]
                )->custom()->sets();
                $adv_data = [];
                foreach ($adv_term_sets as $set) {
                    if (!empty($fields = $set->fields())) {
                        $adv_data[] = "<b>" . $set->presentableTitle() . "</b>";
                    }
                    foreach ($fields as $field) {
                        $adv_data[] = $field->presentableTitle() . ": " . $field->valueAsHTML();
                    }
                }

                return $row
                    ->withHeadline($record["term"])
                    ->withImportantFields([$record["short_txt"]])
                    ->withContent(
                        $ui_factory->legacy($record["definition"])
                    )
                    ->withFurtherFieldsHeadline($this->lng->txt("md_advanced"))
                    ->withFurtherFields($adv_data)
                ;
            }
        )->withData($data);

        return $table;
    }

    protected function initViewControl(): ViewControl
    {
        $offset = $this->manager->getSessionViewControlStart() ?: 0;
        $limit = $this->manager->getSessionViewControlLength() ?: 25;
        $pagination = $this->ui_fac->input()->viewControl()->pagination()
                        ->withTotalCount(count($this->terms))
                        ->withValue([Pagination::FNAME_OFFSET => $offset, Pagination::FNAME_LIMIT => $limit]);
        ;
        $vc_container = $this->ui_fac->input()->container()->viewControl()->standard([$pagination])
                          ->withRequest($this->request);

        return $vc_container;
    }

    protected function setStartAndLengthForViewControl(array $vc_data): void
    {
        /** @var \ILIAS\Data\Range $range */
        $range = $vc_data[0];
        if (($start = $range->getStart()) >= 0) {
            $this->manager->setSessionViewControlStart($start);
        }
        if (($length = $range->getLength()) > 0) {
            $this->manager->setSessionViewControlLength($length);
        }
    }

    protected function getShortTextForTerm(int $term_id): string
    {
        $short_str = \ilGlossaryTerm::_lookShortText($term_id);

        if (\ilGlossaryTerm::_lookShortTextDirty($term_id)) {
            // #18022
            $term_obj = new \ilGlossaryTerm($term_id);
            $term_obj->updateShortText();
            $short_str = $term_obj->getShortText();
        }

        $page = new \ilGlossaryDefPage($term_id);

        // replace tex
        // if a tex end tag is missing a tex end tag
        $ltexs = strrpos($short_str, "[tex]");
        $ltexe = strrpos($short_str, "[/tex]");
        if ($ltexs > $ltexe) {
            $page->buildDom();
            $short_str = $page->getFirstParagraphText();
            $short_str = strip_tags($short_str, "<br>");
            $ltexe = strpos($short_str, "[/tex]", $ltexs);
            $short_str = \ilStr::shortenTextExtended($short_str, $ltexe + 6, true);
        }

        $short_str = \ilMathJax::getInstance()->insertLatexImages($short_str);

        $short_str = \ilPCParagraph::xml2output(
            $short_str,
            false,
            true,
            !$page->getPageConfig()->getPreventHTMLUnmasking()
        );

        return $short_str;
    }

    public function renderPresentationTableForOffline(): string
    {
        $pres_table = $this->initPresentationTable(true);
        $this->tpl->setVariable("ADM_CONTENT", $this->ui_ren->render($pres_table));
        return $this->tpl->printToString();
    }
}
