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

use ILIAS\HTTP\Services as HTTPService;
use ILIAS\UI\Component\Component;

/**
 * Class ilObjLinkResourceGUI
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls ilObjLinkResourceGUI: ilObjectMetaDataGUI, ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjLinkResourceGUI: ilExportGUI, ilWorkspaceAccessGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjLinkResourceGUI: ilPropertyFormGUI, ilInternalLinkGUI
 * @ingroup      ModulesWebResource
 */
class ilObjLinkResourceGUI extends ilObject2GUI
{
    protected const VIEW_MODE_VIEW = 1;
    protected const VIEW_MODE_MANAGE = 2;
    protected const VIEW_MODE_SORT = 3;

    protected const LINK_MOD_CREATE = 1;
    protected const LINK_MOD_EDIT = 2;
    protected const LINK_MOD_ADD = 3;
    protected const LINK_MOD_SET_LIST = 4;
    protected const LINK_MOD_EDIT_LIST = 5;

    protected HTTPService $http;
    protected ilNavigationHistory $navigationHistory;

    private int $view_mode = self::VIEW_MODE_VIEW;

    private ?ilPropertyFormGUI $form = null;
    private ?ilLinkResourceItems $link = null;
    private ?ilLinkResourceList $list = null;
    private ?ilParameterAppender $dynamic = null;

    public function __construct(
        int $id = 0,
        int $id_type = self::REPOSITORY_NODE_ID,
        int $parent_node_id = 0
    ) {
        global $DIC;

        parent::__construct($id, $id_type, $parent_node_id);

        $this->lng->loadLanguageModule("webr");
        $this->http = $DIC->http();
        $this->navigationHistory = $DIC['ilNavigationHistory'];
        $this->settings = $DIC->settings();
    }

    public function getType() : string
    {
        return "webr";
    }

    /**
     * @todo no view mode for workspace?
     */
    protected function initViewMode(?int $new_view_mode = null) : void
    {
        if ($new_view_mode !== null) {
            ilSession::set('webr_view_mode', $new_view_mode);
        }
        if (ilSession::has('webr_view_mode')) {
            $this->view_mode = (int) ilSession::get('webr_view_mode');
        }
    }

    public function executeCommand() : void
    {
        $this->initViewMode();

        $base_class = $this->http->wrapper()->query()->retrieve(
            'baseClass',
            $this->refinery->kindlyTo()->string()
        );
        if ($base_class === ilLinkResourceHandlerGUI::class) {
            $this->__prepareOutput();
        }
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        switch ($next_class) {
            case "ilinfoscreengui":
                $this->prepareOutput();
                $this->infoScreenForward();    // forwards command
                break;

            case 'ilobjectmetadatagui':
                $this->checkPermission('write'); // #18563
                $this->prepareOutput();
                $this->tabs_gui->activateTab('id_meta_data');
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'ilpermissiongui':
                $this->prepareOutput();
                $this->tabs_gui->activateTab('id_permissions');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilobjectcopygui':
                $this->prepareOutput();
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('webr');
                $this->ctrl->forwardCommand($cp);
                break;

            case 'ilexportgui':
                $this->prepareOutput();
                $this->tabs_gui->setTabActive('export');
                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;

            case "ilcommonactiondispatchergui":
                $this->prepareOutput();
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case "ilpropertyformgui":
                $this->initFormLink(self::LINK_MOD_EDIT);
                $this->ctrl->forwardCommand($this->form);
                break;

            case "ilinternallinkgui":
                $this->lng->loadLanguageModule("content");
                $link_gui = new ilInternalLinkGUI("RepositoryItem", 0);
                $link_gui->filterLinkType("PageObject");
                $link_gui->filterLinkType("GlossaryItem");
                $link_gui->filterLinkType("RepositoryItem");
                $link_gui->setFilterWhiteList(true);
                $this->ctrl->forwardCommand($link_gui);
                break;

            default:
                if (!$cmd) {
                    $this->ctrl->setCmd("view");
                }
                parent::executeCommand();
        }

        if (!$this->getCreationMode()) {
            ilMDUtils::_fillHTMLMetaTags(
                $this->object->getId(),
                $this->object->getId(),
                'webr'
            );
            $this->addHeaderAction();
        }
    }

    protected function initCreateForm(string $new_type) : ilPropertyFormGUI
    {
        $this->initFormLink(self::LINK_MOD_CREATE);
        return $this->form;
    }

    public function save() : void
    {
        $this->initFormLink(self::LINK_MOD_CREATE);
        $valid = $this->form->checkInput();
        if (
            $this->checkLinkInput(self::LINK_MOD_CREATE, $valid, 0, 0) &&
            $this->form->getInput('tar_mode_type') === 'single'
        ) {
            parent::save();
        } elseif ($valid && $this->form->getInput('tar_mode_type') == 'list') {
            $this->initList(self::LINK_MOD_CREATE, 0);
            parent::save();
        } else {
            // Data incomplete or invalid
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('err_check_input')
            );
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
        }
    }

    protected function afterSave(ilObject $new_object) : void
    {
        if ($this->form->getInput('tar_mode_type') === 'single') {
            // Save link
            $this->link->setLinkResourceId($new_object->getId());
            $this->link->add();
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt('webr_link_added')
            );
        }
        if ($this->form->getInput('tar_mode_type') === 'list') {
            // Save list
            $this->list->setListResourceId($new_object->getId());
            $this->list->add();
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt('webr_list_added')
            );
        }

        // personal workspace
        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            $this->ctrl->redirect($this, "editLinks");
        } // repository
        else {
            ilUtil::redirect(
                "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" .
                $new_object->getRefId() . "&cmd=switchViewMode&switch_mode=2"
            );
        }
    }

    protected function settings() : void
    {
        $this->checkPermission('write');
        $this->tabs_gui->activateTab('id_settings');

        $form = $this->initFormSettings();
        $this->tpl->setContent($form->getHTML());
    }

    protected function saveSettings() : void
    {
        $obj_service = $this->object_service;

        $this->checkPermission('write');
        $this->tabs_gui->activateTab('id_settings');

        $form = $this->initFormSettings();
        $valid = $form->checkInput();
        if ($valid) {
            // update list
            $this->initList(self::LINK_MOD_EDIT_LIST, $this->object->getId());
            $this->list->update();

            // update object
            $this->object->setTitle($form->getInput('title'));
            $this->object->setDescription((string) $form->getInput('desc'));
            $this->object->update();

            // update sorting
            $sort = new ilContainerSortingSettings($this->object->getId());
            $sort->setSortMode((int) $form->getInput('sor'));
            $sort->update();

            // tile image
            $obj_service->commonSettings()->legacyForm(
                $form,
                $this->object
            )->saveTileImage();
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt('settings_saved'),
                true
            );
            $this->ctrl->redirect($this, 'settings');
        }

        $form->setValuesByPost();
        $this->tpl->setOnScreenMessage(
            'failure',
            $this->lng->txt('err_check_input')
        );
        $this->tpl->setContent($form->getHTML());
    }

    protected function initFormSettings() : ilPropertyFormGUI
    {
        $obj_service = $this->object_service;

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction(
            $this->ctrl->getFormAction($this, 'saveSettings')
        );

        if (ilLinkResourceList::checkListStatus($this->object->getId())) {
            $this->form->setTitle($this->lng->txt('webr_edit_settings'));

            // Title
            $tit = new ilTextInputGUI(
                $this->lng->txt('webr_list_title'),
                'title'
            );
            $tit->setValue($this->object->getTitle());
            $tit->setRequired(true);
            $tit->setSize(40);
            $tit->setMaxLength(127);
            $this->form->addItem($tit);

            // Description
            $des = new ilTextAreaInputGUI(
                $this->lng->txt('webr_list_desc'),
                'desc'
            );
            $des->setValue($this->object->getDescription());
            $des->setCols(40);
            $des->setRows(3);
            $this->form->addItem($des);

            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($this->lng->txt('obj_presentation'));
            $this->form->addItem($section);

            // tile image
            $obj_service->commonSettings()->legacyForm(
                $this->form,
                $this->object
            )->addTileImage();

            // Sorting
            $sor = new ilRadioGroupInputGUI(
                $this->lng->txt('webr_sorting'),
                'sor'
            );
            $sor->setRequired(true);
            $sor->setValue(
                (string) ilContainerSortingSettings::_lookupSortMode(
                    $this->object->getId()
                )
            );

            $opt = new ilRadioOption(
                $this->lng->txt('webr_sort_title'),
                (string) ilContainer::SORT_TITLE
            );
            $sor->addOption($opt);

            $opm = new ilRadioOption(
                $this->lng->txt('webr_sort_manual'),
                (string) ilContainer::SORT_MANUAL
            );
            $sor->addOption($opm);
            $this->form->addItem($sor);
        } else {
            $this->form->setTitle($this->lng->txt('obj_presentation'));

            // hidden title
            $tit = new ilHiddenInputGUI('title');
            $tit->setValue($this->object->getTitle());
            $this->form->addItem($tit);

            // hidden description
            $des = new ilHiddenInputGUI('desc');
            $des->setValue($this->object->getDescription());
            $this->form->addItem($des);

            // tile image
            $obj_service->commonSettings()->legacyForm(
                $this->form,
                $this->object
            )->addTileImage();
        }

        $this->form->addCommandButton('saveSettings', $this->lng->txt('save'));
        $this->form->addCommandButton('view', $this->lng->txt('cancel'));
        return $this->form;
    }

    public function editLink() : void
    {
        $this->checkPermission('write');
        $this->activateTabs('content', 'id_content_view');

        $link_id = 0;
        if ($this->http->wrapper()->query()->has('link_id')) {
            $link_id = $this->http->wrapper()->query()->retrieve(
                'link_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        if (!$link_id) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('select_one'),
                true
            );
            $this->ctrl->redirect($this, 'view');
        }
        $form = $this->initFormLink(self::LINK_MOD_EDIT);
        $this->setValuesFromLink($link_id);
        $this->tpl->setContent($form->getHTML());
    }

    public function updateLink() : void
    {
        $form = $this->initFormLink(self::LINK_MOD_EDIT);
        $valid = $form->checkInput();
        $link_id = 0;
        if ($this->http->wrapper()->query()->has('link_id')) {
            $link_id = $this->http->wrapper()->query()->retrieve(
                'link_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        if ($this->checkLinkInput(
            self::LINK_MOD_EDIT,
            $valid,
            $this->object->getId(),
            $link_id
        )) {
            $this->link->setLinkId($link_id);
            $this->link->update();
            if (
                ilParameterAppender::_isEnabled() &&
                $this->dynamic !== null
            ) {
                $this->dynamic->add($link_id);
            }
            if (!ilLinkResourceList::checkListStatus($this->object->getId())) {
                $this->object->setTitle($form->getInput('title'));
                $this->object->setDescription($form->getInput('desc'));
                $this->object->update();
            }
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt('settings_saved'),
                true
            );
            $this->ctrl->redirect($this, 'view');
        }
        $this->tpl->setOnScreenMessage(
            'failure',
            $this->lng->txt('err_check_input')
        );
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Get form to transform a single weblink to a weblink list
     */
    public function getLinkToListModal() : Component
    {
        global $DIC;

        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();

        // check if form was already set
        if ($this->form == null) {
            $this->initFormLink(self::LINK_MOD_SET_LIST);
        }

        $form_id = 'form_' . $this->form->getId();

        $submit = $f->button()->primary($this->lng->txt('save'), '#')
                    ->withOnLoadCode(
                        function ($id) use ($form_id) {
                            return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
                        }
                    );
        $info = $f->messageBox()->info($this->lng->txt('webr_new_list_info'));

        $modal = $f->modal()->roundtrip(
            $this->lng->txt('webr_new_list'),
            $f->legacy($r->render($info) . $this->form->getHTML())
        )
                   ->withActionButtons([$submit]);

        $submit = '';
        if ($this->http->wrapper()->post()->has('sbmt')) {
            $submit = $this->http->wrapper()->post()->retrieve(
                'sbmt',
                $this->refinery->kindlyTo()->string()
            );
        }
        // modal triggers its show signal on load if form validation failed
        if ($submit === 'submit') {
            $modal = $modal->withOnLoad($modal->getShowSignal());
        }
        return $modal;
    }

    public function saveLinkList() : void
    {
        $this->checkPermission('write');
        $form = $this->initFormLink(self::LINK_MOD_SET_LIST);
        $valid = $form->checkInput();
        if ($valid) {
            $this->object->setTitle($form->getInput('lti'));
            $this->object->setDescription($form->getInput('tde'));
            $this->object->update();

            $this->initList(self::LINK_MOD_SET_LIST, $this->object->getId());
            $this->list->add();
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt('webr_list_set'),
                true
            );
            $this->ctrl->redirect($this, 'view');
        }
        $this->tpl->setOnScreenMessage(
            'failure',
            $this->lng->txt('err_check_input'),
            true
        );
        $form->setValuesByPost();
        $this->view();
    }

    public function addLink() : void
    {
        $this->checkPermission('write');
        $this->activateTabs('content', 'id_content_view');

        $form = $this->initFormLink(self::LINK_MOD_ADD);
        $this->tpl->setContent($form->getHTML());
    }

    public function saveAddLink() : void
    {
        $this->checkPermission('write');

        $form = $this->initFormLink(self::LINK_MOD_ADD);
        $valid = $form->checkInput();
        if ($this->checkLinkInput(
            self::LINK_MOD_ADD,
            $valid,
            $this->object->getId(),
            0
        )
        ) {
            $link_id = $this->link->add();

            if (
                ilParameterAppender::_isEnabled() &&
                $this->dynamic !== null
            ) {
                $this->dynamic->add($link_id);
            }
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt('webr_link_added'),
                true
            );
            $this->ctrl->redirect($this, 'view');
        }
        $this->tpl->setOnScreenMessage(
            'failure',
            $this->lng->txt('err_check_input')
        );
        $this->form->setValuesByPost();
        $this->activateTabs('content', 'id_content_view');
        $this->tpl->setContent($form->getHTML());
    }

    protected function deleteParameter() : void
    {
        $this->checkPermission('write');

        $link_id = $this->http->wrapper()->query()->retrieve(
            'link_id',
            $this->refinery->kindlyTo()->int()
        );
        $this->ctrl->setParameter($this, 'link_id', $link_id);

        $param_id = $this->http->wrapper()->query()->retrieve(
            'param_id',
            $this->refinery->kindlyTo()->int()
        );

        if (!$param_id) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('select_one'),
                true
            );
            $this->ctrl->redirect($this, 'view');
        }
        $param = new ilParameterAppender($this->object->getId());
        $param->delete($param_id);
        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt(
                'links_parameter_deleted'
            ),
            true
        );
        $this->ctrl->redirect($this, 'editLinks');
    }

    protected function deleteParameterForm() : void
    {
        $this->checkPermission('write');

        $param_id = $this->http->wrapper()->query()->retrieve(
            'param_id',
            $this->refinery->kindlyTo()->int()
        );
        if (!$param_id) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('select_one'),
                true
            );
            $this->ctrl->redirect($this, 'view');
        }
        $param = new ilParameterAppender($this->object->getId());
        $param->delete($param_id);
        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt(
                'links_parameter_deleted'
            ),
            true
        );
        $this->ctrl->redirect($this, 'view');
    }

    protected function updateLinks() : void
    {
        $this->checkPermission('write');
        $this->activateTabs('content', '');

        $ids = [];
        if ($this->http->wrapper()->post()->has('ids')) {
            $ids = $this->http->wrapper()->post()->retrieve(
                'ids',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }

        if ($ids === []) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('select_one'),
                true
            );
            $this->ctrl->redirect($this, 'view');
        }

        $link_post = (array) ($this->http->request()->getParsedBody(
            )['links'] ?? []);

        // Validate
        $invalid = [];
        foreach ($ids as $link_id) {
            $data = $link_post[$link_id];

            if (
                $this->http->wrapper()->post()->has(
                    'tar_' . $link_id . '_ajax_type'
                ) &&
                $this->http->wrapper()->post()->has(
                    'tar_' . $link_id . '_ajax_id'
                )
            ) {
                $data['tar'] =
                    $this->http->wrapper()->post()->retrieve(
                        'tar_' . $link_id . '_ajax_type',
                        $this->refinery->kindlyTo()->string()
                    ) . '|' .
                    $this->http->wrapper()->post()->retrieve(
                        'tar_' . $link_id . '_ajax_id',
                        $this->refinery->kindlyTo()->string()
                    );
            }
            if (!strlen($data['title'])) {
                $invalid[] = $link_id;
                continue;
            }
            if (!strlen($data['tar'])) {
                $invalid[] = $link_id;
                continue;
            }
            if ($data['nam'] && !$data['val']) {
                $invalid[] = $link_id;
                continue;
            }
            if (!$data['nam'] && $data['val']) {
                $invalid[] = $link_id;
            }
        }

        if ($invalid !== []) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('err_check_input')
            );
            $this->tpl->addBlockFile(
                'ADM_CONTENT',
                'adm_content',
                'tpl.webr_manage.html',
                'Modules/WebResource'
            );
            $table = new ilWebResourceEditableLinkTableGUI($this, 'view');
            $table->setInvalidLinks($invalid);
            $table->parseSelectedLinks($ids);
            $table->updateFromPost();
            $this->tpl->setVariable('TABLE_LINKS', $table->getHTML());
            return;
        }
        $links = new ilLinkResourceItems($this->object->getId());

        // Save Settings
        foreach ($ids as $link_id) {
            $data = $link_post[$link_id];

            if (
                $this->http->wrapper()->post()->has(
                    'tar_' . $link_id . '_ajax_type'
                ) &&
                $this->http->wrapper()->post()->has(
                    'tar_' . $link_id . '_ajax_id'
                )
            ) {
                $data['tar'] =
                    $this->http->wrapper()->post()->retrieve(
                        'tar_' . $link_id . '_ajax_type',
                        $this->refinery->kindlyTo()->string()
                    ) . '|' .
                    $this->http->wrapper()->post()->retrieve(
                        'tar_' . $link_id . '_ajax_id',
                        $this->refinery->kindlyTo()->string()
                    );
            }
            $links->setLinkId($link_id);
            $links->setTitle(ilUtil::stripSlashes($data['title'] ?? ''));
            $links->setDescription(ilUtil::stripSlashes($data['desc'] ?? ''));
            $links->setTarget(
                str_replace('"', '', ilUtil::stripSlashes($data['tar'] ?? ''))
            );
            $links->setActiveStatus((bool) ($data['act'] ?? false));
            $links->setInternal(ilLinkInputGUI::isInternalLink($data['tar'] ?? ''));
            $links->update();

            if (strlen($data['nam'] ?? '') && $data['val'] ?? '') {
                $param = new ilParameterAppender($this->object->getId());
                $param->setName(ilUtil::stripSlashes($data['nam'] ?? ''));
                $param->setValue((int) ($data['val'] ?? 0));
                $param->add($link_id);
            }
            if (!ilLinkResourceList::checkListStatus($this->object->getId())) {
                $this->object->setTitle(ilUtil::stripSlashes($data['title'] ?? ''));
                $this->object->setDescription(
                    ilUtil::stripSlashes($data['desc'] ?? '')
                );
                $this->object->update();
            }
        }
        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt('settings_saved'),
            true
        );
        $this->ctrl->redirect($this, 'view');
    }

    protected function setValuesFromLink(int $a_link_id) : void
    {
        $link = new ilLinkResourceItems($this->object->getId());
        $values = $link->getItem($a_link_id);
        $this->form->setValuesByArray(
            array(
                'title' => $values['title'],
                'tar' => $values['target'],
                'desc' => $values['description'],
                'act' => (int) $values['active']
            )
        );
    }

    protected function initList(int $a_mode, int $a_webr_id = 0) : void
    {
        if ($a_mode == self::LINK_MOD_CREATE || $a_mode == self::LINK_MOD_EDIT_LIST) {
            $this->list = new ilLinkResourceList($a_webr_id);
            $this->list->setTitle($this->form->getInput('title'));
            $this->list->setDescription($this->form->getInput('desc'));
        }

        if ($a_mode == self::LINK_MOD_SET_LIST) {
            $this->list = new ilLinkResourceList($a_webr_id);
            $this->list->setTitle($this->form->getInput('lti'));
            $this->list->setDescription($this->form->getInput('tde'));
        }
    }

    protected function checkLinkInput(
        int $a_mode,
        bool $a_valid,
        int $a_webr_id = 0,
        int $a_link_id = 0
    ) : bool {
        $valid = $a_valid;

        $link_input = $this->form->getInput('tar');
        $this->link = new ilLinkResourceItems($a_webr_id);
        $this->link->setTarget(str_replace('"', '', $link_input));
        $this->link->setTitle($this->form->getInput('title'));
        $this->link->setDescription($this->form->getInput('desc'));
        $this->link->setInternal(ilLinkInputGUI::isInternalLink($link_input));

        if ($a_mode == self::LINK_MOD_CREATE) {
            $this->link->setActiveStatus(true);
        } else {
            $this->link->setActiveStatus((bool) $this->form->getInput('act'));
        }

        if (!ilParameterAppender::_isEnabled()) {
            return $valid;
        }

        $this->dynamic = new ilParameterAppender($a_webr_id);
        $this->dynamic->setName($this->form->getInput('nam'));
        $this->dynamic->setValue((int) $this->form->getInput('val'));
        if (!$this->dynamic->validate()) {
            switch ($this->dynamic->getErrorCode()) {
                case ilParameterAppender::LINKS_ERR_NO_NAME:
                    $this->form->getItemByPostVar('nam')->setAlert(
                        $this->lng->txt('links_no_name_given')
                    );
                    return false;

                case ilParameterAppender::LINKS_ERR_NO_VALUE:
                    $this->form->getItemByPostVar('val')->setAlert(
                        $this->lng->txt('links_no_value_given')
                    );
                    return false;

                case ilParameterAppender::LINKS_ERR_NO_NAME_VALUE:
                    // Nothing entered => no error
                    return $valid;
            }
            $this->dynamic = null;
        }
        return $valid;
    }

    protected function initFormLink(int $a_mode) : ilPropertyFormGUI
    {
        $this->tabs_gui->activateTab("id_content");

        $this->form = new ilPropertyFormGUI();
        switch ($a_mode) {
            case self::LINK_MOD_CREATE:
                // Header
                $this->ctrl->setParameter($this, 'new_type', 'webr');
                $this->form->setTitle($this->lng->txt('webr_new_link'));
                $this->form->setTableWidth('600px');

                // Buttons
                $this->form->addCommandButton(
                    'save',
                    $this->lng->txt('webr_add')
                );
                $this->form->addCommandButton(
                    'cancel',
                    $this->lng->txt('cancel')
                );
                break;

            case self::LINK_MOD_ADD:
                // Header
                $this->form->setTitle($this->lng->txt('webr_new_link'));

                // Buttons
                $this->form->addCommandButton(
                    'saveAddLink',
                    $this->lng->txt('webr_add')
                );
                $this->form->addCommandButton(
                    'view',
                    $this->lng->txt('cancel')
                );
                break;

            case self::LINK_MOD_EDIT:
                // Header
                $this->ctrl->setParameter(
                    $this,
                    'link_id',
                    // TODO PHP8 Review: Remove/Replace SuperGlobals
                    (int) $_REQUEST['link_id']
                );
                $this->form->setTitle($this->lng->txt('webr_edit'));

                // Buttons
                $this->form->addCommandButton(
                    'updateLink',
                    $this->lng->txt('save')
                );
                $this->form->addCommandButton(
                    'view',
                    $this->lng->txt('cancel')
                );
                break;
        }

        if ($a_mode == self::LINK_MOD_SET_LIST) {
            $this->form->setValuesByPost();
            $this->form->setFormAction(
                $this->ctrl->getFormAction($this, 'saveLinkList')
            );
            $this->form->setId(uniqid('form'));

            // List Title
            $title = new ilTextInputGUI(
                $this->lng->txt('webr_list_title'),
                'lti'
            );
            $title->setRequired(true);
            $title->setSize(40);
            $title->setMaxLength(127);
            $this->form->addItem($title);

            // List Description
            $desc = new ilTextAreaInputGUI(
                $this->lng->txt('webr_list_desc'),
                'tde'
            );
            $desc->setRows(3);
            $desc->setCols(40);
            $this->form->addItem($desc);

            $item = new ilHiddenInputGUI('sbmt');
            $item->setValue('submit');
            $this->form->addItem($item);
        } else {
            $this->form->setFormAction($this->ctrl->getFormAction($this));

            $tar = new ilLinkInputGUI(
                $this->lng->txt('type'),
                'tar'
            ); // lng var
            if ($a_mode == self::LINK_MOD_CREATE) {
                $tar->setAllowedLinkTypes(ilLinkInputGUI::LIST);
            }
            $tar->setInternalLinkFilterTypes(
                array(
                    "PageObject",
                    "GlossaryItem",
                    "RepositoryItem",
                    'WikiPage'
                )
            );
            $tar->setExternalLinkMaxLength(1000);
            $tar->setInternalLinkFilterTypes(
                array("PageObject", "GlossaryItem", "RepositoryItem")
            );
            $tar->setRequired(true);
            $this->form->addItem($tar);

            // Title
            $tit = new ilTextInputGUI(
                $this->lng->txt('webr_link_title'),
                'title'
            );
            $tit->setRequired(true);
            $tit->setSize(40);
            $tit->setMaxLength(127);
            $this->form->addItem($tit);

            // Description
            $des = new ilTextAreaInputGUI(
                $this->lng->txt('description'),
                'desc'
            );
            $des->setRows(3);
            $des->setCols(40);
            $this->form->addItem($des);

            if ($a_mode != self::LINK_MOD_CREATE) {
                // Active
                $act = new ilCheckboxInputGUI($this->lng->txt('active'), 'act');
                $act->setChecked(true);
                $act->setValue((string) 1);
                $this->form->addItem($act);
            }

            if (ilParameterAppender::_isEnabled(
                ) && $a_mode != self::LINK_MOD_CREATE) {
                $dyn = new ilNonEditableValueGUI(
                    $this->lng->txt('links_dyn_parameter')
                );
                $dyn->setInfo($this->lng->txt('links_dynamic_info'));

                if (($links = ilParameterAppender::_getParams(
                    // TODO PHP8 Review: Remove/Replace SuperGlobals
                    (int) $_GET['link_id']
                )) !== []) {
                    $ex = new ilCustomInputGUI(
                        $this->lng->txt('links_existing_params'),
                        'ex'
                    );
                    $dyn->addSubItem($ex);

                    foreach ($links as $id => $link) {
                        $p = new ilCustomInputGUI();

                        $ptpl = new ilTemplate(
                            'tpl.link_dyn_param_edit.html',
                            true,
                            true,
                            'Modules/WebResource'
                        );
                        $ptpl->setVariable(
                            'INFO_TXT',
                            ilParameterAppender::parameterToInfo(
                                $link['name'],
                                $link['value']
                            )
                        );
                        $this->ctrl->setParameter($this, 'param_id', $id);
                        $ptpl->setVariable(
                            'LINK_DEL',
                            $this->ctrl->getLinkTarget(
                                $this,
                                'deleteParameterForm'
                            )
                        );
                        $ptpl->setVariable(
                            'LINK_TXT',
                            $this->lng->txt('delete')
                        );
                        $p->setHtml($ptpl->get());
                        $dyn->addSubItem($p);
                    }
                }

                // Dynyamic name
                $nam = new ilTextInputGUI($this->lng->txt('links_name'), 'nam');
                $nam->setSize(12);
                $nam->setMaxLength(128);
                $dyn->addSubItem($nam);

                // Dynamic value
                $val = new ilSelectInputGUI(
                    $this->lng->txt('links_value'),
                    'val'
                );
                $val->setOptions(ilParameterAppender::_getOptionSelect());
                $val->setValue(0);
                $dyn->addSubItem($val);

                $this->form->addItem($dyn);
            }
        }
        return $this->form;
    }

    /**
     * Switch between "View" "Manage" and "Sort"
     */
    protected function switchViewMode(?int $force_view_mode = null) : void
    {
        $new_view_mode = $this->view_mode;
        if ($force_view_mode !== null) {
            $new_view_mode = $force_view_mode;
        } else {
            if ($this->http->wrapper()->query()->has('switch_mode')) {
                $new_view_mode = $this->http->wrapper()->query()->retrieve(
                    'switch_mode',
                    $this->refinery->kindlyTo()->int()
                );
            }
        }
        $this->initViewMode($new_view_mode);
        $this->view();
    }

    /**
     * Start with manage mode
     */
    protected function editLinks() : void
    {
        $this->switchViewMode(self::VIEW_MODE_MANAGE);
    }

    public function view() : void
    {
        $this->tabs_gui->activateTab("id_content");
        $this->checkPermission('read');

        $base_class = $this->http->wrapper()->query()->retrieve(
            'baseClass',
            $this->refinery->kindlyTo()->string()
        );
        if (strcasecmp($base_class, ilAdministrationGUI::class) === 0) {
            parent::view();
            return;
        } else {
            switch ($this->view_mode) {
                case self::VIEW_MODE_MANAGE:
                    $this->manage();
                    break;

                case self::VIEW_MODE_SORT:
                    // #14638
                    if (ilContainerSortingSettings::_lookupSortMode(
                        $this->object->getId()
                    ) == ilContainer::SORT_MANUAL) {
                        $this->sort();
                        break;
                    }
                    $this->showLinks();
                    break;

                default:
                    $this->showLinks();
                    break;
            }
        }
        $this->tpl->setPermanentLink(
            $this->object->getType(),
            $this->object->getRefId()
        );
    }

    protected function manage() : void
    {
        $this->checkPermission('write');
        $this->activateTabs('content', 'id_content_manage');

        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.webr_manage.html',
            'Modules/WebResource'
        );
        $this->showToolbar('ACTION_BUTTONS');

        $table = new ilWebResourceEditableLinkTableGUI($this, 'view');
        $table->parse();

        $js = ilInternalLinkGUI::getInitHTML("");

        $this->tpl->addJavaScript("Modules/WebResource/js/intLink.js");
        $this->tpl->addJavascript("Services/Form/js/Form.js");

        $this->tpl->setVariable('TABLE_LINKS', $table->getHTML() . $js);
    }

    protected function showLinks() : void
    {
        $this->checkPermission('read');
        $this->activateTabs('content', 'id_content_view');

        $table = new ilWebResourceLinkTableGUI($this, 'showLinks');
        $table->parse();

        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.webr_view.html',
            'Modules/WebResource'
        );
        $this->showToolbar('ACTION_BUTTONS');
        $this->tpl->setVariable('LINK_TABLE', $table->getHTML());
    }

    protected function sort() : void
    {
        $this->checkPermission('write');
        $this->activateTabs('content', 'id_content_ordering');

        $table = new ilWebResourceLinkTableGUI($this, 'sort', true);
        $table->parse();

        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.webr_view.html',
            'Modules/WebResource'
        );
        $this->showToolbar('ACTION_BUTTONS');
        $this->tpl->setVariable('LINK_TABLE', $table->getHTML());
    }

    protected function saveSorting() : void
    {
        $this->checkPermission('write');
        $sort = ilContainerSorting::_getInstance($this->object->getId());

        $position = [];
        if ($this->http->wrapper()->post()->has('position')) {
            $position = $this->http->wrapper()->post()->retrieve(
                'position',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        $sort->savePost($position);
        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt('settings_saved'),
            true
        );
        $this->view();
    }

    protected function showToolbar(string $a_tpl_var) : void
    {
        global $DIC;

        $tool = new ilToolbarGUI();
        $tool->setFormAction($this->ctrl->getFormAction($this));

        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();

        if (
            ilLinkResourceList::checkListStatus($this->object->getId()) &&
            $this->checkPermissionBool('write')
        ) {
            $tool->addButton(
                $this->lng->txt('webr_add'),
                $this->ctrl->getLinkTarget($this, 'addLink')
            );
        } elseif ($this->checkPermissionBool('write')) {
            $modal = $this->getLinkToListModal();
            $button = $f->button()->standard(
                $this->lng->txt('webr_set_to_list'),
                '#'
            )
                        ->withOnClick($modal->getShowSignal());

            $this->tpl->setVariable("MODAL", $r->render([$modal]));

            $tool->addComponent($button);
        }

        $download_button = $f->button()->standard(
            $this->lng->txt('export_html'),
            $this->ctrl->getLinkTarget($this, 'exportHTML')
        );
        $tool->addComponent($download_button);
        $this->tpl->setVariable($a_tpl_var, $tool->getHTML());
    }

    protected function confirmDeleteLink() : void
    {
        $this->checkPermission('write');
        $this->activateTabs('content', 'id_content_view');

        $link_ids = [];
        if ($this->http->wrapper()->query()->has('link_id')) {
            $link_ids = (array) $this->http->wrapper()->query()->retrieve(
                'link_id',
                $this->refinery->kindlyTo()->int()
            );
        } else {
            if ($this->http->wrapper()->post()->has('link_ids')) {
                $link_ids = $this->http->wrapper()->post()->retrieve(
                    'link_ids',
                    $this->refinery->kindlyTo()->dictOf(
                        $this->refinery->kindlyTo()->int()
                    )
                );
            }
        }
        if ($link_ids === []) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('select_one')
            );
            $this->view();
            return;
        }

        $links = new ilLinkResourceItems($this->object->getId());
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this, 'view'));
        $confirm->setHeaderText($this->lng->txt('webr_sure_delete_items'));
        $confirm->setConfirm($this->lng->txt('delete'), 'deleteLinks');
        $confirm->setCancel($this->lng->txt('cancel'), 'view');

        foreach ($link_ids as $link_id) {
            $link = $links->getItem($link_id);
            $confirm->addItem('link_ids[]', (string) $link_id, $link['title']);
        }
        $this->tpl->setContent($confirm->getHTML());
    }

    protected function deleteLinks() : void
    {
        $this->checkPermission('write');

        $links = new ilLinkResourceItems($this->object->getId());

        $link_ids = [];
        if ($this->http->wrapper()->post()->has('link_ids')) {
            $link_ids = $this->http->wrapper()->post()->retrieve(
                'link_ids',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        foreach ($link_ids as $link_id) {
            $links->delete($link_id);
        }
        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt('webr_deleted_items'),
            true
        );
        $this->ctrl->redirect($this, 'view');
    }

    protected function deactivateLink() : void
    {
        $this->checkPermission('write');

        $links = new ilLinkResourceItems($this->object->getId());

        $link_id = 0;
        if ($this->http->wrapper()->query()->has('link_id')) {
            $link_id = $this->http->wrapper()->query()->retrieve(
                'link_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        if (!$link_id) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('select_one'),
                true
            );
            $this->ctrl->redirect($this, 'view');
        }
        $links->setLinkId($link_id);
        $links->updateActive(false);
        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt('webr_inactive_success'),
            true
        );
        $this->ctrl->redirect($this, 'view');
    }

    /**
     * this one is called from the info button in the repository
     * not very nice to set cmdClass/Cmd manually, if everything
     * works through ilCtrl in the future this may be changed
     */
    public function infoScreen() : void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreenForward();
    }

    /**
     * show information screen
     */
    public function infoScreenForward() : void
    {
        if (!$this->checkPermissionBool('visible')) {
            $this->checkPermission('read');
        }
        $this->tabs_gui->activateTab('id_info');

        $info = new ilInfoScreenGUI($this);

        $info->enablePrivateNotes();

        // standard meta data
        $info->addMetaDataSections(
            $this->object->getId(),
            0,
            $this->object->getType()
        );

        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            $info->addProperty(
                $this->lng->txt("perma_link"),
                $this->getPermanentLinkWidget()
            );
        }

        // forward the command
        $this->ctrl->forwardCommand($info);
    }

    public function history() : void
    {
        $this->checkPermission('write');
        $this->tabs_gui->activateTab('id_history');

        $hist_gui = new ilHistoryTableGUI(
            $this,
            "history",
            $this->object->getId(),
            $this->object->getType()
        );
        $hist_gui->initTable();
        $this->tpl->setContent($hist_gui->getHTML());
    }

    /**
     * Activate tab and subtabs
     */
    protected function activateTabs(
        string $a_active_tab,
        string $a_active_subtab = ''
    ) : void {
        switch ($a_active_tab) {
            case 'content':
                if ($this->checkPermissionBool('write')) {
                    $this->lng->loadLanguageModule('cntr');

                    $this->ctrl->setParameter(
                        $this,
                        'switch_mode',
                        self::VIEW_MODE_VIEW
                    );
                    $this->tabs_gui->addSubTab(
                        'id_content_view',
                        $this->lng->txt('view'),
                        $this->ctrl->getLinkTarget($this, 'switchViewMode')
                    );
                    $this->ctrl->setParameter(
                        $this,
                        'switch_mode',
                        self::VIEW_MODE_MANAGE
                    );
                    $this->tabs_gui->addSubTab(
                        'id_content_manage',
                        $this->lng->txt('cntr_manage'),
                        $this->ctrl->getLinkTarget($this, 'switchViewMode')
                    );
                    if ((ilLinkResourceItems::lookupNumberOfLinks(
                        $this->object->getId()
                    ) > 1)
                        and ilContainerSortingSettings::_lookupSortMode(
                            $this->object->getId()
                        ) == ilContainer::SORT_MANUAL) {
                        $this->ctrl->setParameter(
                            $this,
                            'switch_mode',
                            self::VIEW_MODE_SORT
                        );
                        $this->tabs_gui->addSubTab(
                            'id_content_ordering',
                            $this->lng->txt('cntr_ordering'),
                            $this->ctrl->getLinkTarget($this, 'switchViewMode')
                        );
                    }

                    $this->ctrl->clearParameters($this);
                    $this->tabs_gui->activateSubTab($a_active_subtab);
                }
        }

        $this->tabs_gui->activateTab('id_content');
    }

    protected function setTabs() : void
    {
        global $DIC;

        $ilHelp = $DIC['ilHelp'];
        $ilHelp->setScreenIdComponent("webr");

        if ($this->checkPermissionBool('read')) {
            $this->tabs_gui->addTab(
                "id_content",
                $this->lng->txt("content"),
                $this->ctrl->getLinkTarget($this, "view")
            );
        }

        if (
            $this->checkPermissionBool('visible') ||
            $this->checkPermissionBool('read')
        ) {
            $this->tabs_gui->addTab(
                "id_info",
                $this->lng->txt("info_short"),
                $this->ctrl->getLinkTarget($this, "infoScreen")
            );
        }

        if ($this->checkPermissionBool('write') and !$this->getCreationMode()) {
            $this->tabs_gui->addTab(
                "id_settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "settings")
            );
        }

        if ($this->checkPermissionBool('write')) {
            $this->tabs_gui->addTab(
                "id_history",
                $this->lng->txt("history"),
                $this->ctrl->getLinkTarget($this, "history")
            );
        }

        if ($this->checkPermissionBool('write')) {
            $mdgui = new ilObjectMetaDataGUI($this->object);
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTab(
                    "id_meta_data",
                    $this->lng->txt("meta_data"),
                    $mdtab
                );
            }
        }

        if ($this->checkPermissionBool('write')) {
            $this->tabs_gui->addTab(
                'export',
                $this->lng->txt('export'),
                $this->ctrl->getLinkTargetByClass('ilexportgui', '')
            );
        }

        // will add permission tab if needed
        parent::setTabs();
    }

    private function __prepareOutput() : void
    {
        $this->tpl->setLocator();
    }

    /**
     * @todo is this required?
     */
    protected function addLocatorItems() : void
    {
        global $DIC;

        $ilLocator = $DIC['ilLocator'];
        if (is_object($this->object)) {
            $ilLocator->addItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTarget($this),
                "",
                $this->object->getRefId(),
                "webr"
            );
        }
    }

    protected function handleSubItemLinks(string $a_target) : string
    {
        // #15647 - handle internal links
        if (ilLinkInputGUI::isInternalLink($a_target)) {

            // #10612
            $parts = explode("|", $a_target);
            $type = (string) ($parts[0] ?? '');
            $ref_id = (int) ($parts[1] ?? 0);
            
            if ($type == 'wpage') {
                return ilLink::_getStaticLink(
                    0,
                    'wiki',
                    true,
                    '&target=wiki_wpage_' . $ref_id
                );
            }
            if ($type == "term") {
                // #16894
                return ilLink::_getStaticLink(
                    0,
                    "git",
                    true,
                    "&target=git_" . $ref_id
                );
            }
            if ($type == "page") {
                $type = "pg";
            }
            $a_target = ilLink::_getStaticLink($ref_id, $type);
        }
        return $a_target;
    }

    public function callDirectLink() : void
    {
        $obj_id = $this->object->getId();

        if (ilLinkResourceItems::_isSingular($obj_id)) {
            $url = ilLinkResourceItems::_getFirstLink($obj_id);
            if ($url["target"]) {
                $url["target"] = $this->handleSubItemLinks($url["target"]);

                if (ilParameterAppender::_isEnabled()) {
                    $url = ilParameterAppender::_append($url);
                }
                $this->redirectToLink($this->ref_id, $obj_id, $url["target"]);
            }
        }
    }

    public function callLink() : void
    {
        if ($this->http->wrapper()->query()->has('link_id')) {
            $link_id = $this->http->wrapper()->query()->retrieve(
                'link_id',
                $this->refinery->kindlyTo()->int()
            );
            $obj_id = $this->object->getId();

            $items = new ilLinkResourceItems($obj_id);
            $item = $items->getItem($link_id);
            if ($item["target"]) {
                $item["target"] = $this->handleSubItemLinks($item["target"]);

                if (ilParameterAppender::_isEnabled()) {
                    $item = ilParameterAppender::_append($item);
                }
                $this->redirectToLink($this->ref_id, $obj_id, $item["target"]);
            }
        }
    }

    protected function redirectToLink(
        int $a_ref_id,
        int $a_obj_id,
        string $a_url
    ) : void {
        if ($a_url) {
            ilChangeEvent::_recordReadEvent(
                "webr",
                $a_ref_id,
                $a_obj_id,
                $this->user->getId()
            );
            ilUtil::redirect($a_url);
        }
    }

    public function exportHTML() : void
    {
        $tpl = new ilTemplate(
            "tpl.export_html.html",
            true,
            true,
            "Modules/WebResource"
        );

        $items = new ilLinkResourceItems($this->object->getId());
        foreach ($items->getAllItems() as $item) {
            if (!$item["active"]) {
                continue;
            }

            $target = $this->handleSubItemLinks($item["target"]);

            $tpl->setCurrentBlock("link_bl");
            $tpl->setVariable("LINK_URL", $target);
            $tpl->setVariable("LINK_TITLE", $item["title"]);
            $tpl->setVariable("LINK_DESC", $item["description"]);
            $tpl->setVariable("LINK_CREATE", $item["create_date"]);
            $tpl->setVariable("LINK_UPDATE", $item["last_update"]);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("CREATE_DATE", $this->object->getCreateDate());
        $tpl->setVariable("LAST_UPDATE", $this->object->getLastUpdateDate());
        $tpl->setVariable("TXT_TITLE", $this->object->getTitle());
        $tpl->setVariable("TXT_DESC", $this->object->getLongDescription());

        $tpl->setVariable(
            "INST_ID",
            ($this->settings->get('short_inst_name') != "")
            ? $this->settings->get('short_inst_name')
            : "ILIAS"
        );

        ilUtil::deliverData($tpl->get(), "bookmarks.html");
    }

    public static function _goto(string $a_target, $a_additional = null) : void
    {
        global $DIC;

        $main_tpl = $DIC->ui()->mainTemplate();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $ctrl = $DIC->ctrl();
        $ilErr = $DIC['ilErr'];

        if ($a_additional && substr($a_additional, -3) == "wsp") {
            $ctrl->setTargetScript('ilias.php');
            $ctrl->setParameterByClass(
                ilSharedResourceGUI::class,
                'wsp_id',
                $a_target
            );
            $ctrl->redirectByClass(
                [
                    ilSharedResourceGUI::class
                ],
                'edit'
            );
            return;
        }

        // Will be replaced in future releases by ilAccess::checkAccess()
        if ($ilAccess->checkAccess("read", "", (int) $a_target)) {
            ilUtil::redirect(
                "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" . $a_target
            );
        } else {
            // to do: force flat view
            if ($ilAccess->checkAccess("visible", "", (int) $a_target)) {
                ilUtil::redirect(
                    "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" . $a_target . "&cmd=infoScreen"
                );
            } else {
                if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
                    $main_tpl->setOnScreenMessage(
                        'failure',
                        sprintf(
                            $lng->txt("msg_no_perm_read_item"),
                            ilObject::_lookupTitle(
                                ilObject::_lookupObjId(
                                    (int) $a_target
                                )
                            )
                        ),
                        true
                    );
                    ilObjectGUI::_gotoRepositoryRoot();
                }
            }
        }

        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }
} // END class.ilObjLinkResource
