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

use ILIAS\Repository\Clipboard\ClipboardManager;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Style\Content\Container\ContainerDBRepository;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\Object\ImplementsCreationCallback;

/**
 * GUI class for the workflow of copying objects
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @ilCtrl_Calls ilObjectCopyGUI:
 */
class ilObjectCopyGUI
{
    public const SOURCE_SELECTION = 1;
    public const TARGET_SELECTION = 2;
    public const SEARCH_SOURCE = 3;

    public const SUBMODE_COMPLETE = 1;
    public const SUBMODE_CONTENT_ONLY = 2;

    // tabs
    public const TAB_SELECTION_TARGET_TREE = 1;
    public const TAB_SELECTION_SOURCE_TREE = 2;
    public const TAB_SELECTION_MEMBERSHIP = 3;

    // group selection of source or target
    public const TAB_GROUP_SC_SELECTION = 1;

    protected ilCtrl $ctrl;
    protected ilTree $tree;
    protected ilTabsGUI $tabs;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjectDefinition $obj_definition;
    protected ilObjectDataCache $obj_data_cache;
    protected ilAccessHandler $access;
    protected ilErrorHandling $error;
    protected ilRbacSystem $rbacsystem;
    protected ilObjUser $user;
    protected ilRbacReview $rbacreview;
    protected ilLogger $log;
    protected ilLanguage $lng;
    protected RequestWrapper $request_wrapper;
    protected ArrayBasedRequestWrapper $post_wrapper;
    protected Refinery $refinery;
    protected ServerRequestInterface $request;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;

    protected ContainerDBRepository $container_repo;

    protected ?ImplementsCreationCallback $parent_obj = null;
    protected ClipboardManager $clipboard;

    protected int $mode = 0;
    protected int $sub_mode = self::SUBMODE_COMPLETE;
    protected string $type = '';
    protected array $sources = [];
    protected array $targets = [];
    protected array $targets_copy_id = [];
    protected ilPropertyFormGUI $form;
    private ilObjectRequestRetriever $retriever;

    public function __construct(ImplementsCreationCallback $parent_gui)
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->ctrl = $DIC['ilCtrl'];
        $this->tree = $DIC['tree'];
        $this->tabs = $DIC['ilTabs'];
        $this->tpl = $DIC["tpl"];
        $this->obj_definition = $DIC["objDefinition"];
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->access = $DIC->access();
        $this->error = $DIC["ilErr"];
        $this->user = $DIC['ilUser'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->rbacreview = $DIC['rbacreview'];
        $this->log = ilLoggerFactory::getLogger('obj');
        $this->lng = $DIC['lng'];
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->post_wrapper = $DIC->http()->wrapper()->post();
        $this->refinery = $DIC['refinery'];
        $this->request = $DIC->http()->request();
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->retriever = new ilObjectRequestRetriever($DIC->http()->wrapper(), $this->refinery);

        $this->container_repo = new ContainerDBRepository($DIC['ilDB']);


        $this->parent_obj = $parent_gui;

        $this->lng->loadLanguageModule('search');
        $this->lng->loadLanguageModule('obj');
        $this->ctrl->saveParameter($this, "crtcb");

        $this->clipboard = $DIC
            ->repository()
            ->internal()
            ->domain()
            ->clipboard()
        ;
    }

    public function executeCommand(): void
    {
        $this->init();
        $this->initTabs();

        $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->$cmd();
    }

    protected function init(): void
    {
        if ($this->retriever->has('smode')) {
            $this->setSubMode($this->retriever->getMaybeInt('smode') ?? 0);
            $this->ctrl->setParameter($this, 'smode', $this->getSubMode());
            ilLoggerFactory::getLogger('obj')->debug('Submode is: ' . $this->getSubMode());
        }

        // save sources
        if ($this->retriever->has('source_ids')) {
            $this->setSource(explode('_', $this->retriever->getMaybeString('source_ids')));
            $this->ctrl->setParameter($this, 'source_ids', implode('_', $this->getSources()));
            ilLoggerFactory::getLogger('obj')->debug('Multiple sources: ' . implode('_', $this->getSources()));
        }
        if ($this->retriever->has('source_id')) {
            $this->setSource([$this->retriever->getMaybeInt('source_id')]);
            $this->ctrl->setParameter($this, 'source_ids', implode('_', $this->getSources()));
            ilLoggerFactory::getLogger('obj')->debug('source_id is set: ' . implode('_', $this->getSources()));
        }
        if ($this->getFirstSource()) {
            $this->setType(
                ilObject::_lookupType(ilObject::_lookupObjId($this->getFirstSource()))
            );
        }

        // creation screen: copy section
        if ($this->retriever->has('new_type')) {
            $this->setMode(self::SEARCH_SOURCE);
            $this->setType($this->retriever->getMaybeString('new_type'));
            $this->setTarget($this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int()));

            $this->ctrl->setParameter($this, 'new_type', $this->getType());
            $this->ctrl->setParameterByClass(get_class($this->getParentObject()), 'new_type', $this->getType());
            $this->ctrl->setParameterByClass(get_class($this->getParentObject()), 'cpfl', 1);
            $this->ctrl->setReturnByClass(get_class($this->getParentObject()), 'create');

            ilLoggerFactory::getLogger('obj')->debug('Copy from object creation for type: ' . $this->getType());
            return;
        }
        // adopt content, and others?
        elseif ($this->retriever->getMaybeInt('selectMode') === self::SOURCE_SELECTION) {
            $this->setMode(self::SOURCE_SELECTION);

            $this->ctrl->setParameterByClass(get_class($this->parent_obj), 'selectMode', self::SOURCE_SELECTION);
            $this->setTarget($this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int()));
            $this->ctrl->setReturnByClass(get_class($this->parent_obj), '');

            ilLoggerFactory::getLogger('obj')->debug('Source selection mode. Target is: ' . $this->getFirstTarget());
        } elseif ($this->retriever->getMaybeInt('selectMode') === self::TARGET_SELECTION) {
            $this->setMode(self::TARGET_SELECTION);
            $this->ctrl->setReturnByClass(get_class($this->parent_obj), '');
            ilLoggerFactory::getLogger('obj')->debug('Target selection mode.');
        }

        // save targets
        if ($this->retriever->has('target_ids')) {
            $this->setTargets(explode('_', $this->retriever->getMaybeString('target_ids')));
            ilLoggerFactory::getLogger('obj')->debug('targets are: ' . print_r($this->getTargets(), true));
        }
    }

    protected function initTabs(): void
    {
        $this->lng->loadLanguageModule('cntr');
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('cancel'),
            (string) $this->ctrl->getParentReturn($this->parent_obj)
        );
    }

    protected function setTabs(int $tab_group, int $active_tab): void
    {
        if ($tab_group == self::TAB_GROUP_SC_SELECTION) {
            if ($this->getSubMode() == self::SUBMODE_CONTENT_ONLY) {
                if ($this->getMode() == self::SOURCE_SELECTION) {
                    $this->tabs->addTab(
                        (string) self::TAB_SELECTION_SOURCE_TREE,
                        $this->lng->txt('cntr_copy_repo_tree'),
                        $this->ctrl->getLinkTarget($this, 'initSourceSelection')
                    );
                    $this->tabs->addTab(
                        (string) self::TAB_SELECTION_MEMBERSHIP,
                        $this->lng->txt('cntr_copy_crs_grp'),
                        $this->ctrl->getLinkTarget($this, 'showSourceSelectionMembership')
                    );
                }
            }
        }
        $this->tabs->activateTab((string) $active_tab);
    }

    /**
     * Adopt content (crs in crs, grp in grp, crs in grp or grp in crs)
     */
    protected function adoptContent(): void
    {
        $this->ctrl->setParameter($this, 'smode', self::SUBMODE_CONTENT_ONLY);
        $this->ctrl->setParameter($this, 'selectMode', self::SOURCE_SELECTION);

        $this->setSubMode(self::SUBMODE_CONTENT_ONLY);
        $this->setMode(self::SOURCE_SELECTION);
        $this->setTarget($this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int()));

        $this->initSourceSelection();
    }

    /**
     * Init copy from repository/search list commands
     */
    protected function initTargetSelection(): void
    {
        $this->ctrl->setParameter($this, 'selectMode', self::TARGET_SELECTION);

        // copy opened nodes from repository explorer
        $node_ids = is_array(ilSession::get('repexpand')) ? ilSession::get('repexpand') : [];

        // begin-patch mc
        $this->setTargets([]);
        // cognos-blu-patch: end

        // open current position
        foreach ($this->getSources() as $source_id) {
            if ($source_id) {
                $path = $this->tree->getPathId($source_id);
                foreach ($path as $node_id) {
                    if (!in_array($node_id, $node_ids)) {
                        $node_ids[] = $node_id;
                    }
                }
            }
        }

        ilSession::set('paste_copy_repexpand', $node_ids);

        $this->ctrl->setReturnByClass(get_class($this->parent_obj), '');
        $this->showTargetSelectionTree();
    }

    protected function initSourceSelection(): void
    {
        // copy opened nodes from repository explorer
        $node_ids = is_array(ilSession::get('repexpand')) ? ilSession::get('repexpand') : [];

        $this->setTabs(self::TAB_GROUP_SC_SELECTION, self::TAB_SELECTION_SOURCE_TREE);

        // open current position
        // begin-patch mc
        foreach ($this->getTargets() as $target_ref_id) {
            $path = $this->tree->getPathId($target_ref_id);
            foreach ($path as $node_id) {
                if (!in_array($node_id, $node_ids)) {
                    $node_ids[] = $node_id;
                }
            }
        }
        // end-patch multi copy

        ilSession::set('paste_copy_repexpand', $node_ids);

        $this->ctrl->setReturnByClass(get_class($this->parent_obj), '');
        $this->showSourceSelectionTree();
    }


    /**
     * show target selection membership
     */
    protected function showSourceSelectionMembership(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('msg_copy_clipboard_source'));
        $this->setTabs(self::TAB_GROUP_SC_SELECTION, self::TAB_SELECTION_MEMBERSHIP);

        $cgs = new ilObjectCopyCourseGroupSelectionTableGUI(
            $this,
            'showSourceSelectionMembership',
            'copy_selection_mmbrs'
        );
        $cgs->init();
        $cgs->setObjects(
            array_merge(
                ilParticipants::_getMembershipByType($this->user->getId(), ['crs']),
                ilParticipants::_getMembershipByType($this->user->getId(), ['grp'])
            )
        );
        $cgs->parse();

        $this->tpl->setContent($cgs->getHTML());
    }

    protected function showTargetSelectionTree(): void
    {
        if ($this->obj_definition->isContainer($this->getType())) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('msg_copy_clipboard_container'));
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('msg_copy_clipboard'));
        }

        $exp = new ilRepositorySelectorExplorerGUI($this, "showTargetSelectionTree");
        $exp->setTypeWhiteList(["root", "cat", "grp", "crs", "fold", "lso", "prg"]);
        $exp->setSelectMode("target", true);
        if ($exp->handleCommand()) {
            return;
        }
        $output = $exp->getHTML();

        $t = new ilToolbarGUI();
        $t->setFormAction($this->ctrl->getFormAction($this, "saveTarget"));
        $primary_button = $this->ui_factory->button()->primary(
            $this->getPrimaryButtonLabel(),
            ''
        )->withOnLoadCode($this->getOnLoadCode('saveTarget'));
        $t->addComponent($primary_button);
        $t->addSeparator();

        $clipboard_btn = $this->ui_factory->button()->standard(
            $this->lng->txt('obj_insert_into_clipboard'),
            ''
        )->withOnLoadCode($this->getOnLoadCode('keepObjectsInClipboard'));
        $t->addComponent($clipboard_btn);

        $cancel_btn = $this->ui_factory->button()->standard(
            $this->lng->txt('cancel'),
            ''
        )->withOnLoadCode($this->getOnLoadCode('cancel'));
        $t->addComponent($cancel_btn);

        $t->setCloseFormTag(false);
        $t->setLeadingImage(ilUtil::getImagePath("nav/arrow_upright.svg"), " ");
        $output = $t->getHTML() . $output;
        $t->setLeadingImage(ilUtil::getImagePath("nav/arrow_downright.svg"), " ");
        $t->setCloseFormTag(true);
        $t->setOpenFormTag(false);
        $output .= "<br />" . $t->getHTML();

        $this->tpl->setContent($output);
    }

    private function getPrimaryButtonLabel(): string
    {
        if ($this->obj_definition->isContainer($this->getType())) {
            return $this->lng->txt('btn_next');
        }

        return $this->lng->txt('paste');
    }

    private function getOnLoadCode(string $cmd): Closure
    {
        return function ($id) use ($cmd) {
            return "document.getElementById('$id')"
                . '.addEventListener("click", '
                . '(e) => {e.preventDefault();'
                . 'e.target.setAttribute("name", "cmd[' . $cmd . ']");'
                . 'e.target.form.requestSubmit(e.target);});';
        };
    }

    protected function showSourceSelectionTree(): void
    {
        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.paste_into_multiple_objects.html',
            "components/ILIAS/ILIASObject"
        );

        $this->tpl->setOnScreenMessage('info', $this->lng->txt('msg_copy_clipboard_source'));
        $exp = new ilPasteIntoMultipleItemsExplorer(
            ilPasteIntoMultipleItemsExplorer::SEL_TYPE_RADIO,
            'ilias.php?baseClass=ilRepositoryGUI&amp;cmd=goto',
            'paste_copy_repexpand'
        );
        $exp->setRequiredFormItemPermission('visible,read,copy');

        $this->ctrl->setParameter($this, 'selectMode', self::SOURCE_SELECTION);
        $exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'showSourceSelectionTree'));
        $exp->setTargetGet('ref_id');
        $exp->setPostVar('source');
        $exp->setCheckedItems($this->getSources());
        $exp->highlightNode((string) $this->getFirstTarget());

        // Filter to container
        foreach (['cat', 'root', 'fold'] as $container) {
            $exp->removeFormItemForType($container);
        }

        if (!$this->request_wrapper->has("paste_copy_repexpand")) {
            $expanded = $this->tree->readRootId();
        } else {
            $expanded = $this->request_wrapper->retrieve("paste_copy_repexpand", $this->refinery->kindlyTo()->int());
        }

        $this->tpl->setVariable('FORM_TARGET', '_self');
        $this->tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'copySelection'));

        $exp->setExpand($expanded);
        // build html-output
        $exp->setOutput(0);
        $output = $exp->getOutput();

        $this->tpl->setVariable('OBJECT_TREE', $output);
        $this->tpl->setVariable('CMD_SUBMIT', 'saveSource');
        $this->tpl->setVariable('TXT_SUBMIT', $this->lng->txt('btn_next'));
    }

    protected function saveTarget(): void
    {
        if (!$this->retriever->has('target')) {
            $this->ctrl->setParameter($this, 'selectMode', self::TARGET_SELECTION);
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showTargetSelectionTree();
            return;
        }

        try {
            $targets = $this->retriever->getArrayOfInt('target');
        } catch (ConstraintViolationException $e) {
            $possible_target = $this->retriever->getMaybeInt('target');
            $targets = $possible_target === null ? [] : [$possible_target];
        }

        if ($targets !== []) {
            $this->setTargets($targets);
            $this->ctrl->setParameter($this, 'target_ids', implode('_', $this->getTargets()));
        } elseif (($target = $this->retriever->getMaybeInt('target')) !== null) {
            $this->setTarget($target);
            $this->ctrl->setParameter($this, 'target_ids', implode('_', $this->getTargets()));
        }

        // validate allowed subtypes
        foreach ($this->getSources() as $source_ref_id) {
            foreach ($this->getTargets() as $target_ref_id) {
                $target_type = ilObject::_lookupType((int) $target_ref_id, true);
                $target_class_name = ilObjectFactory::getClassByType($target_type);
                $target_object = new $target_class_name((int) $target_ref_id);
                $possible_subtypes = $target_object->getPossibleSubObjects();

                $source_type = ilObject::_lookupType((int) $source_ref_id, true);

                if (!array_key_exists($source_type, (array) $possible_subtypes)) {
                    $this->tpl->setOnScreenMessage('failure', sprintf(
                        $this->lng->txt('msg_obj_may_not_contain_objects_of_type'),
                        $this->lng->txt('obj_' . $target_type),
                        $this->lng->txt('obj_' . $source_type)
                    ));
                    $this->showTargetSelectionTree();
                    return;
                }
            }
        }

        if (count($this->getSources()) == 1 && $this->obj_definition->isContainer($this->getType())) {
            // check, if object should be copied into itself
            // begin-patch mc
            $is_child = [];
            foreach ($this->getTargets() as $target_ref_id) {
                if ($this->tree->isGrandChild($this->getFirstSource(), (int) $target_ref_id)) {
                    $is_child[] = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getFirstSource()));
                }
                if ($this->getFirstSource() == (int) $target_ref_id) {
                    $is_child[] = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getFirstSource()));
                }
            }
            // end-patch multi copy
            if (count($is_child) > 0) {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt("msg_not_in_itself") . " " . implode(',', $is_child)
                );
                $this->showTargetSelectionTree();
                return;
            }

            $this->showItemSelection();
        } else {
            if (count($this->getSources()) == 1) {
                $this->copySingleObject();
            } else {
                $this->copyMultipleNonContainer($this->getSources());
            }
        }
    }

    public function setMode(int $mode): void
    {
        $this->mode = $mode;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function setSubMode(int $mode): void
    {
        $this->sub_mode = $mode;
    }

    public function getSubMode(): int
    {
        return $this->sub_mode;
    }

    /**
     * Get parent gui object
     */
    public function getParentObject(): ?ilObjectGUI
    {
        return $this->parent_obj;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function setSource(array $source_ids): void
    {
        $this->sources = $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())->transform(
            $source_ids
        );
    }

    public function getSources(): array
    {
        return $this->sources;
    }

    public function getFirstSource(): int
    {
        if (count($this->sources)) {
            return (int) $this->sources[0];
        }
        return 0;
    }

    // begin-patch mc
    public function setTarget(int $ref_id): void
    {
        $this->setTargets([$ref_id]);
    }

    public function setTargets(array $targets): void
    {
        $this->targets = $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())->transform(
            $targets
        );
    }

    public function getTargets(): array
    {
        return $this->targets;
    }

    public function getFirstTarget(): int
    {
        if (array_key_exists(0, $this->getTargets())) {
            $targets = $this->getTargets();
            return (int) $targets[0];
        }
        return 0;
    }
    // end-patch multi copy

    protected function cancel(): void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setReturnByClass(get_class($this->parent_obj), 'cancel');
        $ilCtrl->returnToParent($this);
    }

    public function keepObjectsInClipboard(): void
    {
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("obj_inserted_clipboard"), true);
        $ilCtrl = $this->ctrl;
        $this->clipboard->setCmd("copy");
        $this->clipboard->setRefIds($this->getSources());
        $ilCtrl->returnToParent($this);
    }

    protected function searchSource(): void
    {
        if ($this->post_wrapper->has('tit')) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('wizard_search_list'));
            ilSession::set('source_query', $this->post_wrapper->retrieve("tit", $this->refinery->kindlyTo()->string()));
        }

        $this->initFormSearch();
        $this->form->setValuesByPost();

        if (!$this->form->checkInput()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_search_string'), true);
            $this->ctrl->returnToParent($this);
            return;
        }

        $tit = $this->form->getInput('tit');
        if ($tit === "") {
            $tit = ilSession::get('source_query', '');
        }
        $query_parser = new ilQueryParser($tit);
        $query_parser->setMinWordLength(1);
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
        $query_parser->parse();
        if (!$query_parser->validate()) {
            $this->tpl->setOnScreenMessage('failure', $query_parser->getMessage(), true);
            $this->ctrl->returnToParent($this);
        }

        // only like search since fulltext does not support search with less than 3 characters
        $object_search = new ilLikeObjectSearch($query_parser);
        $object_search->setFilter([$this->retriever->getMaybeString('new_type')]);
        $res = $object_search->performSearch();
        $res->setRequiredPermission('copy');
        $res->filter(ROOT_FOLDER_ID, true);

        if (!count($results = $res->getResultsByObjId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('search_no_match'), true);
            $this->ctrl->returnToParent($this);
        }

        $table = new ilObjectCopySearchResultTableGUI($this, 'searchSource', $this->getType());
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setSelectedReference($this->getFirstSource());
        $table->parseSearchResults($results);
        $this->tpl->setContent($table->getHTML());
    }

    protected function saveSource(): void
    {
        if (!$this->post_wrapper->has("source")) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->searchSource();
            return;
        }

        $source = $this->post_wrapper->retrieve("source", $this->refinery->kindlyTo()->int());
        $this->setSource([$source]);
        $this->setType(ilObject::_lookupType($source, true));
        $this->ctrl->setParameter($this, 'source_id', $source);

        foreach ($this->getSources() as $source_ref_id) {
            if (($message = $this->getErrorMessageOnDisallowedObjectTypeForTarget($source_ref_id)) !== '') {
                $this->tpl->setOnScreenMessage('failure', $message);
                $this->searchSource();
                return;
            }
        }

        $this->executeNextStepAfterSourceSelection();
    }

    private function getErrorMessageOnDisallowedObjectTypeForTarget(int $ref_id): string
    {
        foreach ($this->getTargets() as $target_ref_id) {
            $target_type = ilObject::_lookupType($target_ref_id, true);
            $target_class_name = ilObjectFactory::getClassByType($target_type);
            $target_object = new $target_class_name($target_ref_id);
            $possible_subtypes = $target_object->getPossibleSubObjects();

            $source_type = ilObject::_lookupType($ref_id, true);

            if (!array_key_exists($source_type, $possible_subtypes)
                && $this->getSubMode() != self::SUBMODE_CONTENT_ONLY
                && ($source_type !== 'crs' || $target_type !== 'crs')
            ) {
                return sprintf(
                    $this->lng->txt('msg_obj_may_not_contain_objects_of_type'),
                    $this->lng->txt('obj_' . $target_type),
                    $this->lng->txt('obj_' . $source_type)
                );
            }
        }

        return '';
    }

    /**
     * Save selected source from membership screen
     */
    protected function saveSourceMembership(): void
    {
        $source = $this->retriever->getMaybeInt('source');
        if ($source === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->ctrl->redirect($this, 'showSourceSelectionMembership');
            return;
        }

        $this->setSource([$source]);
        $this->setType(ilObject::_lookupType($this->getFirstSource(), true));
        $this->ctrl->setParameter($this, 'source_id', $source);

        $this->executeNextStepAfterSourceSelection();
    }

    private function executeNextStepAfterSourceSelection(): void
    {
        if (!$this->obj_definition->isContainer($this->getType())) {
            $this->copySingleObject();
            return;
        }

        if (count($this->getSources()) === 1
            && ilContainerPage::_exists(
                'cont',
                ilObject::_lookupObjId($this->getFirstSource())
            )
        ) {
            $this->showCopyPageSelection();
            return;
        }

        $this->showItemSelection();
    }

    protected function showCopyPageSelection(): void
    {
        $form = $this->buildCopyPageSelectionForm();
        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    protected function saveCopyPage(): void
    {
        $form = $this->buildCopyPageSelectionForm();
        $data = $form->withRequest($this->request)->getData();

        $this->showItemSelection($data['copy_page']);
    }

    private function buildCopyPageSelectionForm(): Standard
    {
        $form_action = $this->ctrl->getFormAction($this, 'saveCopyPage');

        $input = [
            'copy_page' => $this->ui_factory->input()->field()
                ->radio(
                    $this->lng->txt('cntr_adopt_content')
                )
                ->withOption('1', $this->lng->txt('copy_container_page_yes_label'), $this->lng->txt('copy_container_page_yes_byline'))
                ->withOption('0', $this->lng->txt('copy_container_page_no_label'))
                ->withValue('1')
                ->withAdditionalTransformation($this->refinery->kindlyTo()->bool())
        ];

        return $this->ui_factory->input()->container()->form()
            ->standard($form_action, $input)
            ->withSubmitLabel($this->lng->txt('next'));
    }

    protected function showItemSelection(bool $copy_page = false): void
    {
        if (!count($this->getSources())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->searchSource();
            return;
        }

        $this->log->debug('Source(s): ' . print_r($this->getSources(), true));
        $this->log->debug('Target(s): ' . print_r($this->getTargets(), true));

        $this->tpl->setOnScreenMessage('info', $this->lng->txt($this->getType() . '_copy_threads_info'));
        $this->tpl->addJavaScript('assets/js/ilContainer.js');
        $this->tpl->setVariable('BODY_ATTRIBUTES', 'onload="ilDisableChilds(\'cmd\');"');

        $table = new ilObjectCopySelectionTableGUI($this, 'showItemSelection', $this->getType(), $copy_page);
        $table->parseSource($this->getFirstSource());

        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Start cloning a single (not container) object
     */
    protected function copySingleObject(): void
    {
        // Source defined
        if ($this->getSources() === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->copyMultipleNonContainer($this->getSources());
    }

    /**
     * Copy multiple non container
     *
     * @param array $sources array of source ref ids
     */
    public function copyMultipleNonContainer(array $sources): void
    {
        // check permissions
        foreach ($sources as $source_ref_id) {
            $source_type = ilObject::_lookupType($source_ref_id, true);

            // Create permission
            // begin-patch mc
            foreach ($this->getTargets() as $target_ref_id) {
                if (!$this->rbacsystem->checkAccess('create', $target_ref_id, $source_type)) {
                    $this->log->notice(
                        'Permission denied for target_id: ' .
                        $target_ref_id .
                        ' source_type: ' .
                        $source_type .
                        ' CREATE'
                    );
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
                    $this->ctrl->returnToParent($this);
                }
            }

            // Copy permission
            if (!$this->access->checkAccess('copy', '', $source_ref_id)) {
                $this->log->notice('Permission denied for source_ref_id: ' . $source_ref_id . ' COPY');
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
                $this->ctrl->returnToParent($this);
            }

            // check that these objects are really not containers
            if ($this->obj_definition->isContainer($source_type) and $this->getSubMode() != self::SUBMODE_CONTENT_ONLY) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('cntr_container_only_on_their_own'), true);
                $this->ctrl->returnToParent($this);
            }
        }

        reset($sources);


        ilLoggerFactory::getLogger('obj')->debug('Copy multiple non containers. Sources: ' . print_r($sources, true));

        $new_obj = null;
        // clone
        foreach ($sources as $source_ref_id) {
            ilLoggerFactory::getLogger('obj')->debug('Copying source ref_id : ' . $source_ref_id);

            // begin-patch mc
            foreach ($this->getTargets() as $target_ref_id) {
                // Save wizard options
                $copy_id = ilCopyWizardOptions::_allocateCopyId();
                $wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
                $wizard_options->saveOwner($this->user->getId());
                $wizard_options->saveRoot((int) $source_ref_id);
                $wizard_options->read();

                $orig = ilObjectFactory::getInstanceByRefId((int) $source_ref_id);
                $new_obj = $orig->cloneObject($target_ref_id, $copy_id);

                // Delete wizard options
                $wizard_options->deleteAll();
                $this->parent_obj->callCreationCallback(
                    $new_obj,
                    $this->obj_definition,
                    $this->retriever->getMaybeInt('crtcb', 0)
                );

                // rbac log
                if (ilRbacLog::isActive()) {
                    $rbac_log_roles = $this->rbacreview->getParentRoleIds($new_obj->getRefId());
                    $rbac_log = ilRbacLog::gatherFaPa($new_obj->getRefId(), array_keys($rbac_log_roles), true);
                    ilRbacLog::add(ilRbacLog::COPY_OBJECT, $new_obj->getRefId(), $rbac_log, (bool) $source_ref_id);
                }
            }
        }

        $this->clipboard->clear();
        $this->log->info('Object copy completed.');
        if (count($sources) == 1) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_duplicated"), true);
            $ref_id = $new_obj->getRefId();
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("objects_duplicated"), true);
            $ref_id = $this->getFirstTarget();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("objects_duplicated"), true);
        ilUtil::redirect(ilLink::_getLink($ref_id));
    }

    protected function copyContainerToTargets(): void
    {
        $this->log->debug('Copy container to targets: ' . print_r($_REQUEST, true));
        $this->log->debug('Source(s): ' . print_r($this->getSources(), true));
        $this->log->debug('Target(s): ' . print_r($this->getTargets(), true));

        if ($this->isCopyingParentPageNeeded()) {
            $this->copyParentPage();
        }

        $result = 1;
        foreach ($this->getTargets() as $target_ref_id) {
            $result = $this->copyContainer((int) $target_ref_id);
        }

        $this->clipboard->clear();

        if (ilCopyWizardOptions::_isFinished($result['copy_id'])) {
            $this->log->info('Object copy completed.');
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_duplicated"), true);
            if ($this->getSubMode() == self::SUBMODE_CONTENT_ONLY) {
                $this->ctrl->returnToParent($this);
            }
            $link = ilLink::_getLink($result['ref_id']);
            $this->ctrl->redirectToURL($link);
        } else {
            $this->log->debug('Object copy in progress.');
            $this->showCopyProgress();
        }
    }

    private function isCopyingParentPageNeeded(): bool
    {
        return $this->post_wrapper->has('copy_page')
            && $this->post_wrapper->retrieve('copy_page', $this->refinery->kindlyTo()->bool());
    }

    private function copyParentPage(): void
    {
        $source_object = ilObjectFactory::getInstanceByRefId($this->getFirstSource());
        $target_object = $this->getParentObject()->getObject();
        if (ilContainerPage::_exists(
            "cont",
            $source_object->getId()
        )) {
            $orig_page = new ilContainerPage($source_object->getId());
            $orig_page->copy($target_object->getId(), "cont", $target_object->getId());
        }

        $style_id = ilObjStyleSheet::lookupObjectStyle($source_object->getId());
        if ($style_id > 0 && !ilObjStyleSheet::_lookupStandard($style_id)) {
            $style_obj = ilObjectFactory::getInstanceByObjId($style_id);
            $new_id = $style_obj->ilClone();
            ilObjStyleSheet::writeStyleUsage($target_object->getId(), $new_id);
            ilObjStyleSheet::writeOwner($target_object->getId(), $new_id);
            $reuse = $this->container_repo->readReuse($source_object->getRefId());
            $this->container_repo->updateReuse($target_object->getRefId(), $reuse);
        }
    }

    protected function showCopyProgress(): void
    {
        $ref_id = ROOT_FOLDER_ID;
        if ($this->request_wrapper->has('ref_id')) {
            $ref_id = $this->request_wrapper->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        $this->tabs->setBackTarget(
            $this->lng->txt('tab_back_to_repository'),
            (string) $this->ctrl->getParentReturn($this->parent_obj)
        );

        $progress = new ilObjectCopyProgressTableGUI(
            $this,
            'showCopyProgress',
            $ref_id
        );
        $progress->setObjectInfo($this->targets_copy_id);
        $progress->parse();
        $progress->init();
        $link = ilLink::_getLink($ref_id);
        $progress->setRedirectionUrl($link);

        $this->tpl->setContent($progress->getHTML());
    }

    protected function updateProgress(): void
    {
        $json = new stdClass();
        $json->percentage = null;
        $json->performed_steps = null;

        $copy_id = $this->retriever->getMaybeInt('_copy_id');
        $options = ilCopyWizardOptions::_getInstance($copy_id);
        $node = $options->fetchFirstNode();
        $json->current_node_id = 0;
        $json->current_node_title = "";
        $json->in_dependencies = false;
        if (is_array($node)) {
            $json->current_node_id = $node['obj_id'];
            $json->current_node_title = $node['title'];
        } else {
            $node = $options->fetchFirstDependenciesNode();
            if (is_array($node)) {
                $json->current_node_id = $node['obj_id'];
                $json->current_node_title = $node['title'];
                $json->in_dependencies = true;
            }
        }
        $json->required_steps = $options->getRequiredSteps();
        $json->id = $copy_id;

        $this->log->debug('Update copy progress: ' . json_encode($json));

        echo json_encode($json);
        exit;
    }

    protected function copyContainer(int $target_ref_id): array
    {
        if ($this->getSubMode() != self::SUBMODE_CONTENT_ONLY) {
            if (!$this->rbacsystem->checkAccess('create', $target_ref_id, $this->getType())) {
                $this->log->notice(
                    'Permission denied for target: ' .
                    $target_ref_id .
                    ' type: ' .
                    $this->getType() .
                    ' CREATE'
                );
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
                $this->ctrl->returnToParent($this);
            }
        }

        if (!$this->getFirstSource()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }

        $options = [];
        if ($this->post_wrapper->has("cp_options")) {
            $options = $this->post_wrapper->retrieve(
                "cp_options",
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
                )
            );
        }

        $this->log->debug('Copy container (sources): ' . print_r($this->getSources(), true));

        $orig = ilObjectFactory::getInstanceByRefId($this->getFirstSource());
        $result = $orig->cloneAllObject(
            $_COOKIE[session_name()],
            CLIENT_ID,
            $this->getType(),
            $target_ref_id,
            $this->getFirstSource(),
            $options,
            false,
            $this->getSubMode()
        );

        $this->targets_copy_id[$target_ref_id] = $result['copy_id'];

        $new_ref_id = (int) $result['ref_id'];
        if ($new_ref_id > 0) {
            $new_obj = ilObjectFactory::getInstanceByRefId((int) $result['ref_id'], false);
            if ($new_obj instanceof ilObject) {
                $this->parent_obj->callCreationCallback(
                    $new_obj,
                    $this->obj_definition,
                    $this->retriever->getMaybeInt('crtcb', 0)
                );
            }
        }
        return $result;
    }

    /**
     * Show init screen
     * Normally shown below the create and import form when creating a new object
     *
     * @param ?string $tpl_var The tpl variable to fill
     */
    public function showSourceSearch(?string $tpl_var): ?ilPropertyFormGUI
    {
        $this->unsetSession();
        $this->initFormSearch();

        if ($tpl_var) {
            $this->tpl->setVariable($tpl_var, $this->form->getHTML());
            return null;
        }

        return $this->form;
    }

    /**
     * Check if there is any source object
     */
    protected function sourceExists(): bool
    {
        return (bool) ilUtil::_getObjectsByOperations($this->getType(), 'copy', $this->user->getId(), 1);
    }

    protected function initFormSearch(): void
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setTableWidth('600px');
        $this->ctrl->setParameter($this, 'new_type', $this->getType());
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setTitle($this->lng->txt($this->getType() . '_copy'));
        $this->form->addCommandButton('searchSource', $this->lng->txt('search_for'));
        $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));

        $tit = new ilTextInputGUI($this->lng->txt('title'), 'tit');
        $tit->setSize(40);
        $tit->setMaxLength(70);
        $tit->setRequired(true);
        $tit->setInfo($this->lng->txt('wizard_title_info'));
        $this->form->addItem($tit);
    }

    /**
     * Unset session variables
     */
    protected function unsetSession(): void
    {
        ilSession::clear('source_query');
        $this->setSource([]);
    }
}
