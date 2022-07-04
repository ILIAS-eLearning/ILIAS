<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


use ILIAS\Repository\Clipboard\ClipboardManager;
use ILIAS\Container\Content\ViewManager;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
* Class ilSearchBaseGUI
*
* Base class for all search gui classes. Offers functionallities like set Locator set Header ...
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @package ilias-search
*
* @ilCtrl_IsCalledBy ilSearchBaseGUI: ilSearchControllerGUI
*
*
*/
class ilSearchBaseGUI implements ilDesktopItemHandling, ilAdministrationCommandHandling
{
    public const SEARCH_FAST = 1;
    public const SEARCH_DETAILS = 2;
    public const SEARCH_AND = 'and';
    public const SEARCH_OR = 'or';
    
    public const SEARCH_FORM_LUCENE = 1;
    public const SEARCH_FORM_STANDARD = 2;
    public const SEARCH_FORM_USER = 3;
    
    protected ilUserSearchCache $search_cache;
    protected string $search_mode = '';

    protected ilSearchSettings $settings;
    protected ?ilPropertyFormGUI $form = null;
    protected ClipboardManager $clipboard;
    protected ViewManager $container_view_manager;
    protected ilFavouritesManager $favourites;

    protected ilCtrl $ctrl;
    protected ILIAS $ilias;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLocatorGUI $locator;
    protected ilObjUser $user;
    protected ilTree $tree;
    protected GlobalHttpState $http;
    protected Factory $refinery;


    protected string $prev_link = '';
    protected string $next_link = '';

    public function __construct()
    {
        global $DIC;


        $this->ilias = $DIC['ilias'];
        $this->locator = $DIC['ilLocator'];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tree = $DIC->repositoryTree();

        $this->lng->loadLanguageModule('search');
        $this->settings = new ilSearchSettings();
        $this->favourites = new ilFavouritesManager();
        $this->user = $DIC->user();
        $this->clipboard = $DIC
            ->repository()
            ->internal()
            ->domain()
            ->clipboard();
        $this->container_view_manager = $DIC
            ->container()
            ->internal()
            ->domain()
            ->content()
            ->view();
        $this->search_cache = ilUserSearchCache::_getInstance($this->user->getId());
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    protected function initPageNumberFromQuery() : int
    {
        if ($this->http->wrapper()->query()->has('page_number')) {
            return $this->http->wrapper()->query()->retrieve(
                'page_number',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }


    public function prepareOutput() : void
    {
        $this->tpl->loadStandardTemplate();
        
        $this->tpl->setTitleIcon(
            ilObject::_getIcon(0, "big", "src"),
            ""
        );
        $this->tpl->setTitle($this->lng->txt("search"));
    }
    
    public function initStandardSearchForm(int $a_mode) : ilPropertyFormGUI
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setOpenTag(false);
        $this->form->setCloseTag(false);

        // term combination
        $radg = new ilHiddenInputGUI('search_term_combination');
        $radg->setValue((string) ilSearchSettings::getInstance()->getDefaultOperator());
        $this->form->addItem($radg);
        
        if (ilSearchSettings::getInstance()->isLuceneItemFilterEnabled()) {
            if ($a_mode == self::SEARCH_FORM_STANDARD) {
                // search type
                $radg = new ilRadioGroupInputGUI($this->lng->txt("search_type"), "type");
                $radg->setValue(
                    $this->getType() ==
                        ilSearchBaseGUI::SEARCH_FAST ?
                        (string) ilSearchBaseGUI::SEARCH_FAST :
                        (string) ilSearchBaseGUI::SEARCH_DETAILS
                );
                $op1 = new ilRadioOption($this->lng->txt("search_fast_info"), (string) ilSearchBaseGUI::SEARCH_FAST);
                $radg->addOption($op1);
                $op2 = new ilRadioOption($this->lng->txt("search_details_info"), (string) ilSearchBaseGUI::SEARCH_DETAILS);
            } else {
                $op2 = new ilCheckboxInputGUI($this->lng->txt('search_filter_by_type'), 'item_filter_enabled');
                $op2->setValue('1');
            }

            
            $cbgr = new ilCheckboxGroupInputGUI('', 'filter_type');
            $cbgr->setUseValuesAsKeys(true);
            $details = $this->getDetails();
            $det = false;
            foreach (ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions() as $type => $data) {
                $cb = new ilCheckboxOption($this->lng->txt($data['trans']), $type);
                if (isset($details[$type])) {
                    $det = true;
                }
                $cbgr->addOption($cb);
            }
            $mimes = [];
            if ($a_mode == self::SEARCH_FORM_LUCENE) {
                if (ilSearchSettings::getInstance()->isLuceneMimeFilterEnabled()) {
                    $mimes = $this->getMimeDetails();
                    foreach (ilSearchSettings::getInstance()->getEnabledLuceneMimeFilterDefinitions() as $type => $data) {
                        $op3 = new ilCheckboxOption($this->lng->txt($data['trans']), $type);
                        if (isset($mimes[$type])) {
                            $det = true;
                        }
                        $cbgr->addOption($op3);
                    }
                }
            }
            
            $cbgr->setValue(array_merge((array) $details, (array) $mimes));
            $op2->addSubItem($cbgr);
            
            if ($a_mode != self::SEARCH_FORM_STANDARD && $det) {
                $op2->setChecked(true);
            }

            if ($a_mode == ilSearchBaseGUI::SEARCH_FORM_STANDARD) {
                $radg->addOption($op2);
                $this->form->addItem($radg);
            } else {
                $this->form->addItem($op2);
            }
        }
                
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'performSearch'));
        return $this->form;
    }
    

    public function getSearchAreaForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setOpenTag(false);
        $form->setCloseTag(false);

        // term combination
        $radg = new ilHiddenInputGUI('search_term_combination');
        $radg->setValue((string) ilSearchSettings::getInstance()->getDefaultOperator());
        $form->addItem($radg);

        // search area
        $ti = new ilRepositorySelectorInputGUI($this->lng->txt("search_area"), "area");
        $ti->setSelectText($this->lng->txt("search_select_search_area"));
        $form->addItem($ti);
        $ti->readFromSession();
        
        // alex, 15.8.2012: Added the following lines to get the value
        // from the main menu top right input search form
        if ($this->http->wrapper()->post()->has('root_id')) {
            $ti->setValue(
                $this->http->wrapper()->post()->retrieve(
                    'root_id',
                    $this->refinery->kindlyTo()->string()
                )
            );
            $ti->writeToSession();
        }
        $form->setFormAction($this->ctrl->getFormAction($this, 'performSearch'));
        
        return $form;
    }

    
    public function handleCommand(string $a_cmd) : void
    {
        if (method_exists($this, $a_cmd)) {
            $this->$a_cmd();
        } else {
            $a_cmd .= 'Object';
            $this->$a_cmd();
        }
    }
    
    public function addToDeskObject() : void
    {
        if ($this->http->wrapper()->query()->has('item_ref_id')) {
            $this->favourites->add(
                $this->user->getId(),
                $this->http->wrapper()->query()->retrieve(
                    'item_ref_id',
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        $this->showSavedResults();
    }
     
    public function removeFromDeskObject() : void
    {
        if ($this->http->wrapper()->query()->has('item_ref_id')) {
            $this->favourites->remove(
                $this->user->getId(),
                $this->http->wrapper()->query()->retrieve(
                    'item_ref_id',
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        $this->showSavedResults();
    }
     
    public function delete() : void
    {
        $admin = new ilAdministrationCommandGUI($this);
        $admin->delete();
    }
     
    public function cancelDelete() : void
    {
        $this->showSavedResults();
    }
    
    public function cancelMoveLinkObject() : void
    {
        $this->showSavedResults();
    }
    
    public function performDelete() : void
    {
        $admin = new ilAdministrationCommandGUI($this);
        $admin->performDelete();
    }
    
    public function cut() : void
    {
        $admin = new ilAdministrationCommandGUI($this);
        $admin->cut();
    }
     

    public function link() : void
    {
        $admin = new ilAdministrationCommandGUI($this);
        $admin->link();
    }
         
    public function paste() : void
    {
        $admin = new ilAdministrationCommandGUI($this);
        $admin->paste();
    }
    
    public function showLinkIntoMultipleObjectsTree() : void
    {
        $admin = new ilAdministrationCommandGUI($this);
        $admin->showLinkIntoMultipleObjectsTree();
    }

    public function showMoveIntoObjectTree() : void
    {
        $admin = new ilAdministrationCommandGUI($this);
        $admin->showMoveIntoObjectTree();
    }
    
    public function performPasteIntoMultipleObjects() : void
    {
        $admin = new ilAdministrationCommandGUI($this);
        $admin->performPasteIntoMultipleObjects();
    }

    public function clear() : void
    {
        $this->clipboard->clear();
        $this->ctrl->redirect($this);
    }

    public function enableAdministrationPanel() : void
    {
        $this->container_view_manager->setAdminView();
        $this->ctrl->redirect($this);
    }
    
    public function disableAdministrationPanel() : void
    {
        $this->container_view_manager->setContentView();
        $this->ctrl->redirect($this);
    }

    /**
     * @inheritdoc
     */
    public function keepObjectsInClipboardObject() : void
    {
        $this->ctrl->redirect($this);
    }
    
    
    public function addLocator() : void
    {
        $this->locator->addItem($this->lng->txt('search'), $this->ctrl->getLinkTarget($this));
        $this->tpl->setLocator();
    }

    /**
     * @todo check wether result is ilSearchResult or ilLuceneSearchResult and add interface or base class.
     */
    protected function addPager($result, string $a_session_key) : bool
    {
        $max_page = max(ilSession::get($a_session_key), $this->search_cache->getResultPageNumber());
        ilSession::set($a_session_key, $max_page);

        if ($max_page == 1 and
            (count($result->getResults()) < $result->getMaxHits())) {
            return true;
        }
        
        if ($this->search_cache->getResultPageNumber() > 1) {
            $this->ctrl->setParameter($this, 'page_number', $this->search_cache->getResultPageNumber() - 1);
            $this->prev_link = $this->ctrl->getLinkTarget($this, 'performSearch');
        }
        for ($i = 1;$i <= $max_page;$i++) {
            if ($i == $this->search_cache->getResultPageNumber()) {
                continue;
            }
            
            $this->ctrl->setParameter($this, 'page_number', $i);
            $link = '<a href="' . $this->ctrl->getLinkTarget($this, 'performSearch') . '" /a>' . $i . '</a> ';
        }
        if (count($result->getResults()) >= $result->getMaxHits()) {
            $this->ctrl->setParameter($this, 'page_number', $this->search_cache->getResultPageNumber() + 1);
            $this->next_link = $this->ctrl->getLinkTarget($this, 'performSearch');
        }
        $this->ctrl->clearParameters($this);
        return false;
    }
    
    protected function buildSearchAreaPath(int $a_root_node) : string
    {
        $path_arr = $this->tree->getPathFull($a_root_node, ROOT_FOLDER_ID);
        $counter = 0;
        $path = '';
        foreach ($path_arr as $data) {
            if ($counter++) {
                $path .= " > ";
                $path .= $data['title'];
            } else {
                $path .= $this->lng->txt('repository');
            }
        }
        return $path;
    }
    
    public function autoComplete() : void
    {
        $query = '';
        if ($this->http->wrapper()->post()->has('term')) {
            $query = $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
        }
        $list = ilSearchAutoComplete::getList($query);
        echo $list;
        exit;
    }
    
    protected function getCreationDateForm() : ilPropertyFormGUI
    {
        $options = $this->search_cache->getCreationFilter();
        
        $form = new ilPropertyFormGUI();
        $form->setOpenTag(false);
        $form->setCloseTag(false);
        
        $enabled = new ilCheckboxInputGUI($this->lng->txt('search_filter_cd'), 'screation');
        $enabled->setValue('1');
        $enabled->setChecked((bool) ($options['enabled'] ?? false));
        $form->addItem($enabled);
        
        
        $limit_sel = new ilSelectInputGUI('', 'screation_ontype');
        $limit_sel->setValue($options['ontype'] ?? '');
        $limit_sel->setOptions(
            array(
                    1 => $this->lng->txt('search_created_after'),
                    2 => $this->lng->txt('search_created_before'),
                    3 => $this->lng->txt('search_created_on')
            )
        );
        $enabled->addSubItem($limit_sel);
        
        
        if ($options['date'] ?? false) {
            $now = new ilDate($options['date'] ?? 0, IL_CAL_UNIX);
        } else {
            $now = new ilDate(time(), IL_CAL_UNIX);
        }
        $ds = new ilDateTimeInputGUI('', 'screation_date');
        $ds->setRequired(true);
        $ds->setDate($now);
        $enabled->addSubItem($ds);
        
        $form->setFormAction($this->ctrl->getFormAction($this, 'performSearch'));
        
        return $form;
    }
    
    protected function getSearchCache() : ilUserSearchCache
    {
        return $this->search_cache;
    }
    
    /**
     * @return array<{enabled: bool, type: string, ontype: int, date: ilDate, duration: int}>
     */
    protected function loadCreationFilter() : array
    {
        if (!$this->settings->isDateFilterEnabled()) {
            return array();
        }
        
        
        $form = $this->getCreationDateForm();
        $options = array();
        if ($form->checkInput()) {
            $options['enabled'] = $form->getInput('screation');
            $options['type'] = $form->getInput('screation_type');
            $options['ontype'] = $form->getInput('screation_ontype');
            $options['date'] = $form->getItemByPostVar('screation_date')->getDate()->get(IL_CAL_UNIX);
            $options['duration'] = $form->getInput('screation_duration');
        }
        return $options;
    }
}
