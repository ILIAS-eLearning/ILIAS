<?php declare(strict_types=1);

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

/**
* Class ilAdvancedSearchGUI
*
* GUI class for 'simple' search
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @ilCtrl_Calls ilAdvancedSearchGUI: ilObjectGUI, ilContainerGUI
* @ilCtrl_Calls ilAdvancedSearchGUI: ilObjCategoryGUI, ilObjCourseGUI, ilObjFolderGUI, ilObjGroupGUI
* @ilCtrl_Calls ilAdvancedSearchGUI: ilObjRootFolderGUI, ilObjectCopyGUI, ilPropertyFormGUI
*
* @package ilias-search
*
*/

class ilAdvancedSearchGUI extends ilSearchBaseGUI
{
    public const TYPE_LOM = 1;
    public const TYPE_ADV_MD = 2;

    protected string $last_section = 'adv_search';

    protected ilLuceneAdvancedSearchFields $fields;




    private bool $stored = false;
    private array $options = array();
    protected array $filter = array();

    protected ilTabsGUI $tabs_gui;
    protected ilHelpGUI $help_gui;

    public function __construct()
    {
        global $DIC;

        $this->tabs_gui = $DIC->tabs();
        $this->help_gui = $DIC->help();


        parent::__construct();

        $this->lng->loadLanguageModule('meta');
        $this->fields = ilLuceneAdvancedSearchFields::getInstance();

        $this->__setSearchOptions();
    }

    public function getRootNode(): int
    {
        return ROOT_FOLDER_ID;
    }


    public function executeCommand(): bool
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case "ilpropertyformgui":


            case 'ilobjectcopygui':
                $this->prepareOutput();
                $this->ctrl->setReturn($this, '');

                $cp = new ilObjectCopyGUI($this);
                $this->ctrl->forwardCommand($cp);
                break;

            default:
                $this->initUserSearchCache();
                if (!$cmd) {
                    $last_sub_section = (int) ilSession::get('search_last_sub_section');
                    switch ($last_sub_section) {
                        case self::TYPE_ADV_MD:
                            $cmd = "showSavedAdvMDResults";
                            break;

                        default:
                            $cmd = "showSavedResults";
                            break;
                    }
                }
                $this->prepareOutput();
                $this->handleCommand($cmd);
                break;
        }
        return true;
    }
    public function reset(): void
    {
        $this->initSearchType(self::TYPE_LOM);
        $this->options = array();
        $this->search_cache->setQuery(array());
        $this->search_cache->save();
        $this->showSearch();
    }

    public function searchInResults(): bool
    {
        $this->initSearchType(self::TYPE_LOM);
        $this->search_mode = 'in_results';
        $this->search_cache->setResultPageNumber(1);
        ilSession::clear('adv_max_page');
        $this->performSearch();

        return true;
    }

    protected function remoteSearch(): void
    {
        $root_id = 0;
        if ($this->http->wrapper()->post()->has('root_id')) {
            $root_id = $this->http->wrapper()->post()->retrieve(
                'root_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $queryString = '';
        if ($this->http->wrapper()->post()->has('queryString')) {
            $queryString = $this->http->wrapper()->post()->retrieve(
                'queryString',
                $this->refinery->kindlyTo()->string()
            );
        }
        $this->search_cache->setRoot($root_id);
        $this->search_cache->setResultPageNumber(1);
        $this->search_cache->setQuery(array('lom_content' => $queryString));
        $this->search_cache->save();

        $this->options = $this->search_cache->getQuery();
        $this->options['type'] = 'all';

        $this->performSearch();
    }



    public function performSearch(): bool
    {
        global $DIC;
        $this->initSearchType(self::TYPE_LOM);
        $page_number = $this->initPageNumberFromQuery();
        if (!$page_number and $this->search_mode != 'in_results') {
            ilSession::clear('adv_max_page');
            $this->search_cache->deleteCachedEntries();
        }

        if ($this->http->wrapper()->post()->has('query')) {
            $this->search_cache->setQuery(
                $this->http->wrapper()->post()->retrieve(
                    'query',
                    $this->refinery->kindlyTo()->dictOf(
                        $this->refinery->kindlyTo()->string()
                    )
                )
            );
        }
        $res = new ilSearchResult();
        if ($res_con = $this->__performContentSearch()) {
            $this->__storeEntries($res, $res_con);
        }
        if ($res_lan = $this->__performLanguageSearch()) {
            $this->__storeEntries($res, $res_lan);
        }
        if ($res_gen = $this->__performGeneralSearch()) {
            $this->__storeEntries($res, $res_gen);
        }
        if ($res_lif = $this->__performLifecycleSearch()) {
            $this->__storeEntries($res, $res_lif);
        }
        if ($res_con = $this->__performContributeSearch()) {
            $this->__storeEntries($res, $res_con);
        }
        if ($res_ent = $this->__performEntitySearch()) {
            $this->__storeEntries($res, $res_ent);
        }
        if ($res_req = $this->__performRequirementSearch()) {
            $this->__storeEntries($res, $res_req);
        }
        if ($res_for = $this->__performFormatSearch()) {
            $this->__storeEntries($res, $res_for);
        }
        if ($res_edu = $this->__performEducationalSearch()) {
            $this->__storeEntries($res, $res_edu);
        }
        if ($res_typ = $this->__performTypicalAgeRangeSearch()) {
            $this->__storeEntries($res, $res_typ);
        }
        if ($res_rig = $this->__performRightsSearch()) {
            $this->__storeEntries($res, $res_rig);
        }
        if ($res_cla = $this->__performClassificationSearch()) {
            $this->__storeEntries($res, $res_cla);
        }
        if ($res_tax = $this->__performTaxonSearch()) {
            $this->__storeEntries($res, $res_tax);
        }
        if ($res_key = $this->__performKeywordSearch()) {
            $this->__storeEntries($res, $res_key);
        }

        $this->searchAdvancedMD($res);

        if ($this->search_mode == 'in_results') {
            $old_result_obj = new ilSearchResult($this->user->getId());
            $old_result_obj->read(ilUserSearchCache::ADVANCED_MD_SEARCH);

            $res->diffEntriesFromResult();
        }

        $res->filter($this->getRootNode(), (ilSearchSettings::getInstance()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND));
        $res->save();
        $this->showSearch();

        if (!count($res->getResults())) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('search_no_match'));
        }

        if ($res->isLimitReached()) {
            #$message = sprintf($this->lng->txt('search_limit_reached'),$this->settings->getMaxHits());
            #ilUtil::sendInfo($message);
        }

        $this->addPager($res, 'adv_max_page');

        $presentation = new ilSearchResultPresentation($this, ilSearchResultPresentation::MODE_STANDARD);
        $presentation->setResults($res->getResultsForPresentation());
        $presentation->setPreviousNext($this->prev_link, $this->next_link);

        if ($presentation->render()) {
            $this->tpl->setVariable('RESULTS', $presentation->getHTML());
        }
        return true;
    }


    protected function initAdvancedMetaDataForm(): ?ilPropertyFormGUI
    {
        if (is_object($this->form)) {
            return $this->form;
        }
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'performAdvMDSearch'));
        $this->form->setTitle($this->lng->txt('adv_md_search_title'));
        $this->form->addCommandButton('performAdvMDSearch', $this->lng->txt('search'));
        #$this->form->setSubformMode('right');

        $content = new ilTextInputGUI($this->lng->txt('meta_title') . '/' .
            $this->lng->txt('meta_keyword') . '/' .
            $this->lng->txt('meta_description'), 'title');
        $content->setValue($this->options['title']);
        $content->setSize(30);
        $content->setMaxLength(255);
        //		$content->setSubformMode('right');
        $group = new ilRadioGroupInputGUI('', 'title_ao');
        $group->setValue($this->options['title_ao']);
        $radio_option = new ilRadioOption($this->lng->txt("search_any_word"), '0');
        $group->addOption($radio_option);
        $radio_option = new ilRadioOption($this->lng->txt("search_all_words"), '1');
        $group->addOption($radio_option);
        $content->addSubItem($group);
        $this->form->addItem($content);

        $type = new ilSelectInputGUI($this->lng->txt('type'), 'type');
        $options['adv_all'] = $this->lng->txt('search_any');
        foreach (ilAdvancedMDRecord::_getActivatedObjTypes() as $obj_type) {
            $options[$obj_type] = $this->lng->txt('objs_' . $obj_type);
        }
        $type->setOptions($options);
        $type->setValue($this->options['type']);
        $this->form->addItem($type);

        $record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_SEARCH);
        $record_gui->setPropertyForm($this->form);
        $record_gui->setSearchValues($this->options);
        $record_gui->parse();
        return null;
    }


    protected function performAdvMDSearch(): bool
    {
        $this->initSearchType(self::TYPE_ADV_MD);
        $page_number = $this->initPageNumberFromQuery();
        if (!$page_number and $this->search_mode != 'in_results') {
            ilSession::clear('adv_max_page');
            $this->search_cache->delete();
        }
        $res = new ilSearchResult();
        if ($res_tit = $this->__performTitleSearch()) {
            $this->__storeEntries($res, $res_tit);
        }
        $this->searchAdvancedMD($res);
        if ($this->search_mode == 'in_results') {
            $old_result_obj = new ilSearchResult($this->user->getId());
            $old_result_obj->read(ilUserSearchCache::ADVANCED_MD_SEARCH);

            $res->diffEntriesFromResult();
        }
        $res->filter($this->getRootNode(), true);
        $res->save();
        $this->showAdvMDSearch();

        if (!count($res->getResults())) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('search_no_match'));
        }

        if ($res->isLimitReached()) {
            #$message = sprintf($this->lng->txt('search_limit_reached'),$this->settings->getMaxHits());
            #ilUtil::sendInfo($message);
        }

        $this->addPager($res, 'adv_max_page');

        $presentation = new ilSearchResultPresentation($this, ilSearchResultPresentation::MODE_STANDARD);
        $presentation->setResults($res->getResultsForPresentation());
        $presentation->setPreviousNext($this->prev_link, $this->next_link);

        if ($presentation->render()) {
            $this->tpl->setVariable('RESULTS', $presentation->getHTML());
        }
        return true;
    }


    public function showAdvMDSearch(): bool
    {
        $session_options = ilSession::get('search_adv_md');
        if ($session_options !== null) {
            $this->options = $session_options;
        }
        $this->setSubTabs();
        $this->tabs_gui->setSubTabActive('search_adv_md');

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.advanced_adv_search.html', 'Services/Search');

        $this->initAdvancedMetaDataForm();
        $this->tpl->setVariable('SEARCH_FORM', $this->form->getHTML());
        return true;
    }


    protected function initFormSearch(): bool
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'performSearch'));
        $this->form->setTitle($this->lng->txt('search_advanced'));
        $this->form->addCommandButton('performSearch', $this->lng->txt('search'));
        $this->form->addCommandButton('reset', $this->lng->txt('reset'));
        foreach ($this->fields->getActiveSections() as $definition) {
            if ($definition['name'] != 'default') {
                $section = new ilFormSectionHeaderGUI();
                $section->setTitle($definition['name']);
                $this->form->addItem($section);
            }

            foreach ($definition['fields'] as $field_name) {
                if (is_object($element = $this->fields->getFormElement($this->search_cache->getQuery(), $field_name, $this->form))) {
                    $this->form->addItem($element);
                }
            }
        }
        return true;
    }



    public function showSearch(): bool
    {
        $this->setSubTabs();
        $this->tabs_gui->setSubTabActive('search_lom');

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.advanced_search.html', 'Services/Search');

        $this->initFormSearch();
        $this->tpl->setVariable('SEARCH_FORM', $this->form->getHTML());
        return true;
    }

    public function prepareOutput(): void
    {
        parent::prepareOutput();

        $this->help_gui->setScreenIdComponent("src");

        $this->tabs_gui->addTab(
            "search",
            $this->lng->txt("search"),
            $this->ctrl->getLinkTargetByClass('ilsearchgui')
        );
        $this->tabs_gui->addTab(
            "adv_search",
            $this->lng->txt("search_advanced"),
            $this->ctrl->getLinkTarget($this)
        );
        $this->tabs_gui->activateTab("adv_search");
    }


    private function showSavedAdvMDResults(): bool
    {
        $this->initSearchType(self::TYPE_ADV_MD);
        $result_obj = new ilSearchResult($this->user->getId());
        $result_obj->read(ilUserSearchCache::ADVANCED_MD_SEARCH);

        $this->showAdvMDSearch();

        // Show them
        if (count($result_obj->getResults())) {
            $this->addPager($result_obj, 'adv_max_page');

            $presentation = new ilSearchResultPresentation($this, ilSearchResultPresentation::MODE_STANDARD);
            $presentation->setResults($result_obj->getResultsForPresentation());
            $presentation->setPreviousNext($this->prev_link, $this->next_link);

            if ($presentation->render()) {
                $this->tpl->setVariable('RESULTS', $presentation->getHTML());
            }
        }

        return true;
    }


    public function showSavedResults(): bool
    {
        $this->initSearchType(self::TYPE_LOM);
        $result_obj = new ilSearchResult($this->user->getId());
        $result_obj->read(ilUserSearchCache::ADVANCED_SEARCH);

        $this->showSearch();

        // Show them
        if (count($result_obj->getResults())) {
            $this->addPager($result_obj, 'adv_max_page');

            $presentation = new ilSearchResultPresentation($this, ilSearchResultPresentation::MODE_STANDARD);
            $presentation->setResults($result_obj->getResultsForPresentation());
            $presentation->setPreviousNext($this->prev_link, $this->next_link);

            if ($presentation->render()) {
                $this->tpl->setVariable('RESULTS', $presentation->getHTML());
            }
        }

        return true;
    }

    public function __performContentSearch(): ?ilSearchResult
    {
        if (!($this->options['lom_content'] ?? null)) {
            return null;
        }

        $res = new ilSearchResult();

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_content']));
        #$query_parser->setCombination($this->options['content_ao']);
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_OR);
        $query_parser->parse();

        if (!isset($this->options['type'])) {
            if ($tit_res = $this->__performTitleSearch()) {
                $res->mergeEntries($tit_res);
            }

            return $res;
        }

        if ($this->options['type'] == 'all' or $this->options['type'] == 'lms') {
            // LM content search
            $lm_search = ilObjectSearchFactory::_getLMContentSearchInstance($query_parser);
            $res_cont = $lm_search->performSearch();
            $res->mergeEntries($res_cont);
        }
        if ($this->options['type'] == 'all' or $this->options['type'] == 'tst') {
            $tst_search = ilObjectSearchFactory::_getTestSearchInstance($query_parser);
            $res_tes = $tst_search->performSearch();
            $res->mergeEntries($res_tes);
        }
        if ($this->options['type'] == 'all' or $this->options['type'] == 'mep') {
            $med_search = ilObjectSearchFactory::_getMediaPoolSearchInstance($query_parser);
            $res_med = $med_search->performSearch();
            $res->mergeEntries($res_med);
        }
        if ($this->options['type'] == 'all' or $this->options['type'] == 'glo') {
            $glo_search = ilObjectSearchFactory::_getGlossaryDefinitionSearchInstance($query_parser);
            $res_glo = $glo_search->performSearch();
            $res->mergeEntries($res_glo);
        }
        if ($this->options['type'] == 'all' or $this->options['type'] == 'webr') {
            $web_search = ilObjectSearchFactory::_getWebresourceSearchInstance($query_parser);
            $res_web = $web_search->performSearch();
            $res->mergeEntries($res_web);
        }
        if ($tit_res = $this->__performTitleSearch()) {
            $res->mergeEntries($tit_res);
        }

        return $res;
    }


    public function __performTitleSearch(): ?ilSearchResult
    {
        if (!($this->options['lom_content'] ?? null)) {
            return null;
        }


        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_content']));
        #$query_parser->setCombination($this->options['title_ao']);
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_OR);
        $query_parser->parse();
        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);

        $meta_search->setFilter($this->filter);
        $meta_search->setMode('title_description');
        $meta_search->setOptions($this->options);
        $res_tit = $meta_search->performSearch();

        $meta_search->setMode('keyword_all');
        $res_key = $meta_search->performSearch();

        // merge them
        $res_tit->mergeEntries($res_key);


        return $res_tit;
    }



    public function __performGeneralSearch(): ?ilSearchResult
    {
        if (
            !($this->options['lom_coverage'] ?? null) and
            !($this->options['lom_structure'] ?? null)
        ) {
            return null;
        }


        if (($this->options['lom_coverage'] ?? null)) {
            $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_coverage']));
            #$query_parser->setCombination($this->options['coverage_ao']);
            $query_parser->setCombination(ilQueryParser::QP_COMBINATION_OR);
            $query_parser->parse();
        } else {
            $query_parser = new ilQueryParser('');
        }
        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('general');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }

    public function __performLifecycleSearch(): ?ilSearchResult
    {
        // Return if 'any'
        if (
            !($this->options['lom_status'] ?? null) and
            !($this->options['lom_version'] ?? null)
        ) {
            return null;
        }

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_version']));
        #$query_parser->setCombination($this->options['version_ao']);
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_OR);
        $query_parser->parse();

        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('lifecycle');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }
    public function __performLanguageSearch(): ?ilSearchResult
    {
        if (!($this->options['lom_language'] ?? null)) {
            return null;
        }


        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('language');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }
    public function __performContributeSearch(): ?ilSearchResult
    {
        if (!strlen($this->options['lom_role'] ?? '')) {
            return null;
        }


        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('contribute');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }
    public function __performEntitySearch(): ?ilSearchResult
    {
        // Return if 'any'
        if (!($this->options['lom_role_entry'] ?? null)) {
            return null;
        }


        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_role_entry']));
        #$query_parser->setCombination($this->options['entity_ao']);
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_OR);
        $query_parser->parse();

        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('entity');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }


    public function __performRequirementSearch(): ?ilSearchResult
    {
        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('requirement');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }
    public function __performFormatSearch(): ?ilSearchResult
    {
        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('format');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }
    public function __performEducationalSearch(): ?ilSearchResult
    {
        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('educational');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }
    public function __performTypicalAgeRangeSearch(): ?ilSearchResult
    {
        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('typical_age_range');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }
    public function __performRightsSearch(): ?ilSearchResult
    {
        if (
            !($this->options['lom_copyright'] ?? null) and
            !($this->options['lom_costs'] ?? null)
        ) {
            return null;
        }


        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('rights');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }

    public function __performClassificationSearch(): ?ilSearchResult
    {
        // Return if 'any'
        if (!($this->options['lom_purpose'] ?? null)) {
            return null;
        }


        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance(new ilQueryParser(''));
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('classification');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }

    public function __performTaxonSearch(): ?ilSearchResult
    {
        // Return if 'any'
        if (!($this->options['lom_taxon'] ?? null)) {
            return null;
        }

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_taxon']));
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_OR);
        $query_parser->parse();

        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('taxon');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }

    private function searchAdvancedMD(ilSearchResult $res): void
    {
        $this->initFormSearch();

        foreach (array_keys($this->options) as $key) {
            if (substr((string) $key, 0, 3) != 'adv') {
                continue;
            }

            // :TODO: ?
            if (!$key) {
                continue;
            }

            $field_id = substr($key, 4);
            $field = ilAdvancedMDFieldDefinition::getInstance((int) $field_id);

            $field_form = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($field->getADTDefinition(), true, false);
            $field_form->setElementId("query[" . $key . "]");
            $field_form->setForm($this->form);

            // reload search values
            $field_form->importFromPost($this->options);
            $field_form->validate();

            $parser_value = $field->getSearchQueryParserValue($field_form);

            $adv_md_search = ilObjectSearchFactory::_getAdvancedMDSearchInstance(new ilQueryParser($parser_value));
            $adv_md_search->setFilter($this->filter);
            $adv_md_search->setDefinition($field);
            $adv_md_search->setSearchElement($field_form);
            $res_field = $adv_md_search->performSearch();
            if ($res_field instanceof ilSearchResult) {
                $this->__storeEntries($res, $res_field);
            }
        }
    }

    public function __performKeywordSearch(): ?ilSearchResult
    {
        // Return if 'any'
        if (!($this->options['lom_keyword'] ?? null)) {
            return null;
        }

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->options['lom_keyword']));
        #$query_parser->setCombination($this->options['keyword_ao']);
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_OR);
        $query_parser->parse();

        $meta_search = ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);
        $meta_search->setFilter($this->filter);
        $meta_search->setMode('keyword');
        $meta_search->setOptions($this->options);
        $res = $meta_search->performSearch();

        return $res;
    }

    public function __setSearchOptions(): bool
    {
        $query = '';
        if ($this->http->wrapper()->post()->has('query')) {
            $query = $this->http->wrapper()->post()->retrieve(
                'query',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            );
        }

        $post_cmd = (array) ($this->http->request()->getParsedBody()['cmd'] ?? []);

        if (isset($post_cmd['performSearch'])) {
            $this->options = $query;
            ilSession::set('search_adv', $this->options);
        } elseif (isset($post_cmd['performAdvMDSearch'])) {
            $this->options = (array) $this->http->request()->getParsedBody();
            ilSession::set('search_adv_md', $this->options);
        } else {
            $this->options = ilSession::get('search_adv') ?? [];
        }
        $this->filter = array();

        $this->options['type'] = 'all';
        switch ($this->options['type']) {
            case 'cat':
                $this->filter[] = 'cat';
                break;

            case 'webr':
                $this->filter[] = 'webr';
                break;

            case 'lms':
                $this->filter[] = 'lm';
                $this->filter[] = 'dbk';
                $this->filter[] = 'pg';
                $this->filter[] = 'st';
                $this->filter[] = 'sahs';
                $this->filter[] = 'htlm';
                break;

            case 'glo':
                $this->filter[] = 'glo';
                break;

            case 'tst':
                $this->filter[] = 'tst';
                $this->filter[] = 'svy';
                $this->filter[] = 'qpl';
                $this->filter[] = 'spl';
                break;

            case 'mep':
                $this->filter[] = 'mep';
                $this->filter[] = 'mob';
                $this->filter[] = 'mpg';
                break;

            case 'crs':
                $this->filter[] = 'crs';
                break;

            case 'file':
                $this->filter[] = 'file';
                break;

            case 'adv_all':
                $this->filter = ilAdvancedMDRecord::_getActivatedObjTypes();
                break;

            case 'all':
            default:
                $this->filter[] = 'sess';
                $this->filter[] = 'webr';
                $this->filter[] = 'crs';
                $this->filter[] = 'mep';
                $this->filter[] = 'tst';
                $this->filter[] = 'svy';
                $this->filter[] = 'qpl';
                $this->filter[] = 'spl';
                $this->filter[] = 'glo';
                $this->filter[] = 'lm';
                $this->filter[] = 'dbk';
                $this->filter[] = 'pg';
                $this->filter[] = 'st';
                $this->filter[] = 'sahs';
                $this->filter[] = 'htlm';
                $this->filter[] = 'file';
                $this->filter[] = 'mob';
                $this->filter[] = 'mpg';
        }

        return true;
    }

    public function __getFilterSelect(): string
    {
        $options = array('all' => $this->lng->txt('search_any'),
                         'crs' => $this->lng->txt('objs_crs'),
                         'lms' => $this->lng->txt('obj_lrss'),
                         'glo' => $this->lng->txt('objs_glo'),
                         'mep' => $this->lng->txt('objs_mep'),
                         'tst' => $this->lng->txt('search_tst_svy'),
                         'file' => $this->lng->txt('objs_file'),
                         'webr' => $this->lng->txt('objs_webr'),
                         'sess' => $this->lng->txt('objs_sess')
            );


        return ilLegacyFormElementsUtil::formSelect($this->options['type'], 'search_adv[type]', $options, false, true);
    }


    public function __storeEntries(ilSearchResult $res, ilSearchResult $new_res): bool
    {
        if ($this->stored == false) {
            $res->mergeEntries($new_res);
            $this->stored = true;

            return true;
        } else {
            $res->intersectEntries($new_res);
            return true;
        }
    }

    private function initUserSearchCache(): void
    {
        $this->search_cache = ilUserSearchCache::_getInstance($this->user->getId());
        $this->search_cache->switchSearchType(ilUserSearchCache::ADVANCED_SEARCH);
        $page_number = $this->initPageNumberFromQuery();
        if ($page_number) {
            $this->search_cache->setResultPageNumber($page_number);
        }
        $post_cmd = (array) ($this->http->request()->getParsedBody()['cmd'] ?? []);
        $post_query = (array) ($this->http->request()->getParsedBody()['query'] ?? []);
        if ($post_cmd['performSearch'] ?? null) {
            $this->search_cache->setQuery($post_query['lomContent'] ?? '');
            $this->search_cache->save();
        }
    }

    public function setSubTabs(): bool
    {
        if (!count(ilAdvancedMDFieldDefinition::getSearchableDefinitionIds())) {
            return true;
        }
        $this->tabs_gui->addSubTabTarget('search_lom', $this->ctrl->getLinkTarget($this, 'showSavedResults'));
        return true;
    }


    private function toUnixTime(array $date, array $time = array()): int
    {
        return mktime($time['h'], $time['m'], 0, $date['m'], $date['d'], $date['y']);
    }


    private function initSearchType(int $type): void
    {
        if ($type == self::TYPE_LOM) {
            ilSession::set('search_last_sub_section', self::TYPE_LOM);
            $this->search_cache->switchSearchType(ilUserSearchCache::ADVANCED_SEARCH);
        } else {
            ilSession::set('search_last_sub_section', self::TYPE_ADV_MD);
            $this->search_cache->switchSearchType(ilUserSearchCache::ADVANCED_MD_SEARCH);
        }
    }
}
