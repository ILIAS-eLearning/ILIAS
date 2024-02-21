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

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\URI;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Input\Field\Radio;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\Object\ImplementsCreationCallback;
use ILIAS\Object\CreationCallbackTrait;
use ILIAS\Object\ilObjectDIC;
use ILIAS\Object\Properties\MultiObjectPropertiesManipulator;
use ILIAS\ILIASObject\Creation\AddNewItemElement;
use ILIAS\ILIASObject\Creation\AddNewItemElementTypes;
use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\MimeType;

/**
 * Class ilObjectGUI
 * Basic methods of all Output classes
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjectGUI implements ImplementsCreationCallback
{
    use CreationCallbackTrait;

    public const ALLOWED_TAGS_IN_TITLE_AND_DESCRIPTION = [
        '<b>',
        '<i>',
        '<strong>',
        '<em>',
        '<sub>',
        '<sup>',
        '<pre>',
        '<strike>',
        '<bdo>'
    ];
    public const ADMIN_MODE_NONE = "";
    public const ADMIN_MODE_SETTINGS = "settings";
    public const ADMIN_MODE_REPOSITORY = "repository";
    public const UPLOAD_TYPE_LOCAL = 1;
    public const UPLOAD_TYPE_UPLOAD_DIRECTORY = 2;
    public const CFORM_NEW = 1;
    public const CFORM_IMPORT = 2;
    public const CFORM_CLONE = 3;
    public const SUPPORTED_IMPORT_MIME_TYPES = [MimeType::APPLICATION__ZIP];
    protected \ILIAS\Notes\Service $notes_service;

    protected ServerRequestInterface $request;
    protected ilLocatorGUI $locator;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected ilSetting $settings;
    protected ilToolbarGUI $toolbar;
    protected ilRbacAdmin $rbac_admin;
    protected ilRbacSystem $rbac_system;
    protected ilRbacReview $rbac_review;
    protected ilObjectService $object_service;
    protected ilObjectDefinition $obj_definition;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTree $tree;
    protected ilCtrl $ctrl;
    protected ilErrorHandling $error;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs_gui;
    protected ILIAS $ilias;
    protected ArrayBasedRequestWrapper $post_wrapper;
    protected RequestWrapper $request_wrapper;
    protected Refinery $refinery;
    protected ilFavouritesManager $favourites;
    protected ilObjectCustomIconFactory $custom_icon_factory;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    private ilObjectRequestRetriever $retriever;
    private MultiObjectPropertiesManipulator $multi_object_manipulator;
    private Filesystem $temp_file_system;

    protected ?ilObject $object = null;
    protected bool $creation_mode = false;
    protected $data;
    protected int $id;
    protected bool $call_by_reference = false;
    protected bool $prepare_output;
    protected int $ref_id;
    protected int $obj_id;
    protected int $maxcount;			// contains number of child objects
    protected array $form_action = [];		// special formation (array "cmd" => "formaction")
    protected array $return_location = [];	// special return location (array "cmd" => "location")
    protected array $target_frame = [];	// special target frame (array "cmd" => "location")
    protected string $tmp_import_dir;	// directory used during import$this->ui_factory = $DIC['ui.factory'];
    protected string $sub_objects = "";
    protected bool $omit_locator = false;
    protected string $type = "";
    protected string $admin_mode = self::ADMIN_MODE_NONE;
    protected int $requested_ref_id = 0;
    protected int $requested_crtptrefid = 0;
    protected int $requested_crtcb = 0;
    protected string $requested_new_type = "";
    protected string $link_params;
    protected string $html = "";

    /**
     * @param mixed $data
     * @param int $id
     * @param bool $call_by_reference
     * @param bool $prepare_output
     * @throws ilCtrlException
     */
    public function __construct($data, int $id = 0, bool $call_by_reference = true, bool $prepare_output = true)
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->request = $DIC->http()->request();
        $this->locator = $DIC["ilLocator"];
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $this->toolbar = $DIC->toolbar();
        $this->rbac_admin = $DIC->rbac()->admin();
        $this->rbac_system = $DIC->rbac()->system();
        $this->rbac_review = $DIC->rbac()->review();
        $this->object_service = $DIC->object();
        $this->obj_definition = $DIC["objDefinition"];
        $this->tpl = $DIC["tpl"];
        $this->tree = $DIC->repositoryTree();
        $this->ctrl = $DIC->ctrl();
        $this->error = $DIC["ilErr"];
        $this->lng = $DIC->language();
        $this->tabs_gui = $DIC->tabs();
        $this->ilias = $DIC["ilias"];
        $this->post_wrapper = $DIC->http()->wrapper()->post();
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();
        $this->retriever = new ilObjectRequestRetriever($DIC->http()->wrapper(), $this->refinery);
        $this->favourites = new ilFavouritesManager();
        $this->custom_icon_factory = $DIC['object.customicons.factory'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->temp_file_system = $DIC->filesystem()->temp();

        $this->data = $data;
        $this->id = $id;
        $this->call_by_reference = $call_by_reference;
        $this->prepare_output = $prepare_output;

        $params = ["ref_id"];
        if (!$call_by_reference) {
            $params = ["ref_id","obj_id"];
        }
        $this->ctrl->saveParameter($this, $params);

        if ($this->request_wrapper->has("ref_id")) {
            $this->requested_ref_id = $this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int());
        }

        $this->obj_id = $this->id;
        $this->ref_id = $this->requested_ref_id;

        if ($call_by_reference) {
            $this->ref_id = $this->id;
            $this->obj_id = 0;
            if ($this->request_wrapper->has("obj_id")) {
                $this->obj_id = $this->request_wrapper->retrieve("obj_id", $this->refinery->kindlyTo()->int());
            }
        }

        // TODO: refactor this with post_wrapper or request_wrapper
        // callback after creation
        $this->requested_crtptrefid = $this->retriever->getMaybeInt('crtptrefid', 0);
        $this->requested_crtcb = $this->retriever->getMaybeInt('crtcb', 0);
        $this->requested_new_type = $this->retriever->getMaybeString('new_type', '');


        if ($this->id != 0) {
            $this->link_params = "ref_id=" . $this->ref_id;
        }

        $this->assignObject();

        if (is_object($this->object)) {
            if ($this->call_by_reference && $this->ref_id == $this->requested_ref_id) {
                $this->ctrl->setContextObject(
                    $this->object->getId(),
                    $this->object->getType()
                );
            }
        }

        if ($prepare_output) {
            $this->prepareOutput();
        }

        $this->notes_service = $DIC->notes();
    }

    private function getMultiObjectPropertiesManipulator(): MultiObjectPropertiesManipulator
    {
        if (!isset($this->multi_object_manipulator)) {
            $this->multi_object_manipulator = ilObjectDIC::dic()['multi_object_properties_manipulator'];
        }
        return $this->multi_object_manipulator;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function setAdminMode(string $mode): void
    {
        if (!in_array($mode, [
            self::ADMIN_MODE_NONE,
            self::ADMIN_MODE_REPOSITORY,
            self::ADMIN_MODE_SETTINGS
        ])) {
            throw new ilObjectException("Unknown Admin Mode $mode.");
        }
        $this->admin_mode = $mode;
    }

    public function getAdminMode(): string
    {
        return $this->admin_mode;
    }

    protected function getObjectService(): ilObjectService
    {
        return $this->object_service;
    }

    public function getObject(): ?ilObject
    {
        return $this->object;
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();
        if (!$cmd) {
            $cmd = "view";
        }
        $cmd .= "Object";
        $this->$cmd();
    }

    /**
    * determines whether objects are referenced or not (got ref ids or not)
    */
    public function withReferences(): bool
    {
        return $this->call_by_reference;
    }

    /**
    * if true, a creation screen is displayed
    * the current [ref_id] don't belong
    * to the current class!
    * The mode is determined in ilRepositoryGUI
    */
    public function setCreationMode(bool $mode = true): void
    {
        $this->creation_mode = $mode;
    }

    public function getCreationMode(): bool
    {
        return $this->creation_mode;
    }

    protected function assignObject(): void
    {
        // TODO: it seems that we always have to pass only the ref_id
        if ($this->id != 0) {
            if ($this->call_by_reference) {
                $this->object = ilObjectFactory::getInstanceByRefId($this->id);
            } else {
                $this->object = ilObjectFactory::getInstanceByObjId($this->id);
            }
        }
    }

    public function prepareOutput(bool $show_sub_objects = true): bool
    {
        $this->tpl->loadStandardTemplate();
        // administration prepare output
        $base_class = $this->request_wrapper->retrieve("baseClass", $this->refinery->kindlyTo()->string());
        if (strtolower($base_class) == "iladministrationgui") {
            $this->addAdminLocatorItems();
            $this->tpl->setLocator();

            $this->setTitleAndDescription();

            if ($this->getCreationMode() != true) {
                $this->setAdminTabs();
            }

            return false;
        }
        $this->setLocator();

        // in creation mode (parent) object and gui object do not fit
        if ($this->getCreationMode() === true) {
            // repository vs. workspace
            if ($this->call_by_reference) {
                // get gui class of parent and call their title and description method
                $obj_type = ilObject::_lookupType($this->requested_ref_id, true);
                $class_name = $this->obj_definition->getClassName($obj_type);
                $class = strtolower("ilObj" . $class_name . "GUI");
                $class_path = $this->ctrl->lookupClassPath($class);
                $class_name = $this->ctrl->getClassForClasspath($class_path);
            }
        } else {
            $this->setTitleAndDescription();

            // set tabs
            $this->setTabs();

            $file_upload_dropzone = new ilObjFileUploadDropzone($this->ref_id);
            if ($file_upload_dropzone->isUploadAllowed($this->object->getType())) {
                $this->enableDragDropFileUpload();
            }
        }

        return true;
    }

    protected function setTitleAndDescription(): void
    {
        if (!is_object($this->object)) {
            if ($this->requested_crtptrefid > 0) {
                $cr_obj_id = ilObject::_lookupObjId($this->requested_crtcb);
                $this->tpl->setTitle(
                    strip_tags(
                        ilObject::_lookupTitle($cr_obj_id),
                        self::ALLOWED_TAGS_IN_TITLE_AND_DESCRIPTION
                    )
                );
                $this->tpl->setTitleIcon(ilObject::_getIcon($cr_obj_id));
            }
            return;
        }
        $this->tpl->setTitle(
            strip_tags(
                $this->object->getPresentationTitle(),
                self::ALLOWED_TAGS_IN_TITLE_AND_DESCRIPTION
            )
        );
        $this->tpl->setDescription(
            strip_tags(
                $this->object->getLongDescription(),
                self::ALLOWED_TAGS_IN_TITLE_AND_DESCRIPTION
            )
        );

        $base_class = $this->request_wrapper->retrieve("baseClass", $this->refinery->kindlyTo()->string());
        if (strtolower($base_class) === "iladministrationgui") {
            // alt text would be same as heading -> empty alt text
            $this->tpl->setTitleIcon(ilObject::_getIcon(0, "big", $this->object->getType()));
        } else {
            $this->tpl->setTitleIcon(
                ilObject::_getIcon($this->object->getId(), "big", $this->object->getType()),
                $this->lng->txt("obj_" . $this->object->getType())
            );
        }
        if (!$this->obj_definition->isAdministrationObject($this->object->getType())) {
            $lgui = ilObjectListGUIFactory::_getListGUIByType($this->object->getType());
            $lgui->initItem($this->object->getRefId(), $this->object->getId(), $this->object->getType());
            $this->tpl->setAlertProperties($lgui->getAlertProperties());
        }
    }

    protected function createActionDispatcherGUI(): ilCommonActionDispatcherGUI
    {
        return new ilCommonActionDispatcherGUI(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            $this->access,
            $this->object->getType(),
            $this->ref_id,
            $this->object->getId()
        );
    }

    /**
     * Add header action menu
     */
    protected function initHeaderAction(?string $sub_type = null, ?int $sub_id = null): ?ilObjectListGUI
    {
        if (!$this->creation_mode && $this->object) {
            $dispatcher = $this->createActionDispatcherGUI();

            $dispatcher->setSubObject($sub_type, $sub_id);

            ilObjectListGUI::prepareJsLinks(
                $this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true),
                "",
                $this->ctrl->getLinkTargetByClass(["ilcommonactiondispatchergui", "iltagginggui"], "", "", true)
            );

            $lg = $dispatcher->initHeaderAction();

            if (is_object($lg)) {
                // to enable add to desktop / remove from desktop
                if ($this instanceof ilDesktopItemHandling) {
                    $lg->setContainerObject($this);
                }
                // enable multi download
                $lg->enableMultiDownload(true);

                // comments settings are always on (for the repository)
                // should only be shown if active or permission to toggle
                if (
                    $this->access->checkAccess("write", "", $this->ref_id) ||
                    $this->access->checkAccess("edit_permissions", "", $this->ref_id) ||
                    $this->notes_service->domain()->commentsActive($this->object->getId())
                ) {
                    $lg->enableComments(true);
                }

                $lg->enableNotes(true);
                $lg->enableTags(true);
            }

            return $lg;
        }
        return null;
    }

    /**
     * Insert header action into main template
     */
    protected function insertHeaderAction(?ilObjectListGUI $list_gui = null): void
    {
        if (
            !is_object($this->object) ||
            ilContainer::_lookupContainerSetting($this->object->getId(), "hide_top_actions")
        ) {
            return;
        }

        if (is_object($list_gui)) {
            $this->tpl->setHeaderActionMenu($list_gui->getHeaderAction());
        }
    }

    /**
     * Add header action menu
     */
    protected function addHeaderAction(): void
    {
        $this->insertHeaderAction($this->initHeaderAction());
    }

    /**
     * Ajax call: redraw action header only
     */
    protected function redrawHeaderActionObject(): void
    {
        $lg = $this->initHeaderAction();
        echo $lg->getHeaderAction();

        // we need to add onload code manually (rating, comments, etc.)
        echo $this->tpl->getOnLoadCodeForAsynch();
        exit;
    }

    /**
    * set admin tabs
    */
    protected function setTabs(): void
    {
        $this->getTabs();
    }

    /**
    * set admin tabs
    */
    final protected function setAdminTabs(): void
    {
        $this->getAdminTabs();
    }

    /**
    * administration tabs show only permissions and trash folder
    */
    public function getAdminTabs(): void
    {
        if ($this->checkPermissionBool("visible,read")) {
            $this->tabs_gui->addTarget(
                "view",
                $this->ctrl->getLinkTarget($this, "view"),
                ["", "view"],
                get_class($this)
            );
        }

        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass([get_class($this), 'ilpermissiongui'], "perm"),
                "",
                "ilpermissiongui"
            );
        }
    }

    public function getHTML(): string
    {
        return $this->html;
    }

    protected function setLocator(): void
    {
        $ilLocator = $this->locator;
        $tpl = $this->tpl;

        if ($this->omit_locator) {
            return;
        }

        // repository vs. workspace
        if ($this->call_by_reference) {
            // todo: admin workaround
            // in the future, object gui classes should not be called in
            // admin section anymore (rbac/trash handling in own classes)
            $ref_id = $this->requested_ref_id;
            if ($this->requested_ref_id === 0) {
                $ref_id = $this->object->getRefId();
            }
            $ilLocator->addRepositoryItems($ref_id);
        }

        if (!$this->creation_mode) {
            $this->addLocatorItems();
        }

        $tpl->setLocator();
    }

    /**
    * should be overwritten to add object specific items
    * (repository items are preloaded)
    */
    protected function addLocatorItems(): void
    {
    }

    protected function omitLocator(bool $omit = true): void
    {
        $this->omit_locator = $omit;
    }

    /**
     * should be overwritten to add object specific items
     * (repository items are preloaded)
     */
    protected function addAdminLocatorItems(bool $do_not_add_object = false): void
    {
        if ($this->admin_mode == self::ADMIN_MODE_SETTINGS) {
            $this->ctrl->setParameterByClass(
                "ilobjsystemfoldergui",
                "ref_id",
                SYSTEM_FOLDER_ID
            );
            $this->locator->addItem(
                $this->lng->txt("administration"),
                $this->ctrl->getLinkTargetByClass(["iladministrationgui", "ilobjsystemfoldergui"], "")
            );
            if ($this->object && ($this->object->getRefId() != SYSTEM_FOLDER_ID && !$do_not_add_object)) {
                $this->locator->addItem(
                    $this->object->getTitle(),
                    $this->ctrl->getLinkTarget($this, "view")
                );
            }
        } else {
            $this->ctrl->setParameterByClass(
                "iladministrationgui",
                "ref_id",
                ""
            );
            $this->ctrl->setParameterByClass(
                "iladministrationgui",
                "admin_mode",
                "settings"
            );
            $this->ctrl->clearParametersByClass("iladministrationgui");
            $this->locator->addAdministrationItems();
        }
    }

    /**
    * confirmed deletion of object -> objects are moved to trash or deleted
    * immediately, if trash is disabled
    */
    public function confirmedDeleteObject(): void
    {
        if ($this->post_wrapper->has('interruptive_items')) {
            $ref_ids_to_be_deleted = $this->post_wrapper->retrieve(
                'interruptive_items',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        $ru = new ilRepositoryTrashGUI($this);
        $ru->deleteObjects($this->requested_ref_id, $ref_ids_to_be_deleted);

        $this->ctrl->redirect($this);
    }

    /**
     * cancel action and go back to previous page
     */
    public function cancelObject(): void
    {
        ilSession::clear("saved_post");
        $this->ctrl->returnToParent($this);
    }

    /**
     * create new object form
     */
    public function createObject(): void
    {
        $new_type = $this->requested_new_type;

        // add new object to custom parent container
        $this->ctrl->saveParameter($this, "crtptrefid");
        // use forced callback after object creation
        $this->ctrl->saveParameter($this, "crtcb");

        if (!$this->checkPermissionBool('create', '', $new_type)) {
            $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
        }

        $this->lng->loadLanguageModule($new_type);
        $this->ctrl->setParameter($this, 'new_type', $new_type);

        $create_form = $this->initCreateForm($new_type);
        $this->tpl->setContent($this->getCreationFormsHTML($create_form));
    }

    protected function getCreationFormsHTML(StandardForm|ilPropertyFormGUI $form): string
    {
        $title = $this->getCreationFormTitle();

        $content = $form;
        if ($form instanceof ilPropertyFormGUI) {
            $form->setTitle('');
            $form->setTitleIcon('');
            $form->setTableWidth("100%");
            $content = $this->ui_factory->legacy($form->getHTML());
        }

        $panel = $this->ui_factory->panel()->standard($title, $content);

        return $this->ui_renderer->render($panel);
    }

    protected function getCreationFormTitle(): string
    {
        return $this->lng->txt($this->requested_new_type . '_new');
    }

    protected function initCreateForm(string $new_type): StandardForm|ilPropertyFormGUI
    {
        $form_fields['title_and_description'] = (new ilObject())->getObjectProperties()->getPropertyTitleAndDescription()->toForm(
            $this->lng,
            $this->ui_factory->input()->field(),
            $this->refinery
        );

        $didactic_templates = $this->didacticTemplatesToForm();

        if ($didactic_templates !== null) {
            $form_fields['didactic_templates'] = $didactic_templates;
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'save'),
            $form_fields
        )->withSubmitLabel($this->lng->txt($new_type . '_add'));
    }

    protected function didacticTemplatesToForm(): ?Radio
    {
        $this->lng->loadLanguageModule('didactic');

        list($existing_exclusive, $options) = $this->buildDidacticTemplateOptions();

        if (sizeof($options) < 2) {
            return null;
        }

        $didactic_templates_radio = $this->ui_factory->input()->field()->radio($this->lng->txt('type'));

        foreach ($options as $value => $option) {
            if ($existing_exclusive && $value == 'dtpl_0') {
                //Skip default disabled if an exclusive template exists - Whatever the f*** that means!
                continue;
            }
            $didactic_templates_radio = $didactic_templates_radio->withOption($value, $option[0], $option[1] ?? '');
        }

        if (!$this->getCreationMode()) {
            $value = 'dtpl_' . ilDidacticTemplateObjSettings::lookupTemplateId($this->object->getRefId());

            $didactic_templates_radio = $didactic_templates_radio->withValue($value);

            if (!in_array($value, array_keys($options)) || ($existing_exclusive && $value == "dtpl_0")) {
                //add or rename actual value to not available
                $options[$value] = [$this->lng->txt('not_available')];
            }
        } else {
            if ($existing_exclusive) {
                //if an exclusive template exists use the second template as default value
                $keys = array_keys($options);
                $didactic_templates_radio = $didactic_templates_radio->withValue($keys[1]);
            } else {
                $didactic_templates_radio = $didactic_templates_radio->withValue('dtpl_0');
            }
        }

        return $didactic_templates_radio;
    }

    /**
     * @deprecated ILIAS 11 This will be removed with the next version
     */
    protected function initDidacticTemplate(ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        list($existing_exclusive, $options) = $this->buildDidacticTemplateOptions();

        if (sizeof($options) < 2) {
            return $form;
        }

        $type = new ilRadioGroupInputGUI(
            $this->lng->txt('type'),
            'didactic_type'
        );
        // workaround for containers in edit mode
        if (!$this->getCreationMode()) {
            $value = 'dtpl_' . ilDidacticTemplateObjSettings::lookupTemplateId($this->object->getRefId());

            $type->setValue($value);

            if (!in_array($value, array_keys($options)) || ($existing_exclusive && $value == "dtpl_0")) {
                //add or rename actual value to not available
                $options[$value] = [$this->lng->txt('not_available')];
            }
        } else {
            if ($existing_exclusive) {
                //if an exclusive template exists use the second template as default value - Whatever the f*** that means!
                $keys = array_keys($options);
                $type->setValue($keys[1]);
            } else {
                $type->setValue('dtpl_0');
            }
        }
        $form->addItem($type);

        foreach ($options as $id => $data) {
            $option = new ilRadioOption($data[0] ?? '', (string) $id, $data[1] ?? '');
            if ($existing_exclusive && $id == 'dtpl_0') {
                //set default disabled if an exclusive template exists
                $option->setDisabled(true);
            }

            $type->addOption($option);
        }

        return $form;
    }

    private function buildDidacticTemplateOptions(): array
    {
        $this->lng->loadLanguageModule('didactic');
        $existing_exclusive = false;
        $options = [];
        $options['dtpl_0'] = [
            $this->lng->txt('didactic_default_type'),
            sprintf(
                $this->lng->txt('didactic_default_type_info'),
                $this->lng->txt('objs_' . $this->type)
            )
        ];

        $templates = ilDidacticTemplateSettings::getInstanceByObjectType($this->type)->getTemplates();
        if ($templates) {
            foreach ($templates as $template) {
                if ($template->isEffective((int) $this->requested_ref_id)) {
                    $options["dtpl_" . $template->getId()] = [
                        $template->getPresentationTitle(),
                        $template->getPresentationDescription()
                    ];

                    if ($template->isExclusive()) {
                        $existing_exclusive = true;
                    }
                }
            }
        }

        return [$existing_exclusive, array_merge($options, $this->retrieveAdditionalDidacticTemplateOptions())];
    }

    /**
     * @return array<string>
     */
    protected function retrieveAdditionalDidacticTemplateOptions(): array
    {
        return [];
    }

    protected function addAdoptContentLinkToToolbar(): void
    {
        $this->toolbar->addComponent(
            $this->ui_factory->link()->standard(
                $this->lng->txt('cntr_adopt_content'),
                $this->ctrl->getLinkTargetByClass(
                    'ilObjectCopyGUI',
                    'adoptContent'
                )
            )
        );
    }

    protected function addImportButtonToToolbar(): void
    {
        $modal = $this->buildImportModal();
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('import'),
                $modal->getShowSignal()
            )
        );

        $this->tpl->setVariable(
            'IL_OBJECT_IMPORT_MODAL',
            $this->ui_renderer->render(
                $modal
            )
        );
    }

    private function buildImportModal(): RoundTrip
    {
        return $this->ui_factory->modal()
            ->roundtrip(
                $this->lng->txt('import'),
                [],
                $this->buildImportFormInputs(),
                $this->ctrl->getFormAction($this, 'routeImportCmd')
            )->withSubmitLabel($this->lng->txt('import'));
    }

    protected function addAvailabilityPeriodButtonToToolbar(ilToolbarGUI $toolbar): ilToolbarGUI
    {
        $toolbar->addSeparator();

        $toolbar->addComponent(
            $this->getMultiObjectPropertiesManipulator()->getAvailabilityPeriodButton()
        );
        return $toolbar;
    }

    public function editAvailabilityPeriodObject(): void
    {
        if (!$this->checkPermissionBool('write')) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_write'));
            return;
        }
        $item_ref_ids = $this->retriever->getSelectedIdsFromObjectList();
        $availability_period_modal = $this->getMultiObjectPropertiesManipulator()->getEditAvailabilityPeriodPropertiesModal(
            $item_ref_ids,
            $this
        );
        if ($availability_period_modal !== null) {
            $this->tpl->setVariable(
                'IL_OBJECT_EPHEMRAL_MODALS',
                $this->ui_renderer->render(
                    $availability_period_modal->withOnLoad(
                        $availability_period_modal->getShowSignal()
                    )
                )
            );
        }
        $this->renderObject();
    }

    public function saveAvailabilityPeriodObject(): void
    {
        if (!$this->checkPermissionBool('write')) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_write'));
            return;
        }
        $availability_period_modal = $this->getMultiObjectPropertiesManipulator()->saveEditAvailabilityPeriodPropertiesModal($this, $this->request);
        if ($availability_period_modal === null) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('availability_period_changed'));
        } else {
            $this->tpl->setVariable(
                'IL_OBJECT_EPHEMRAL_MODALS',
                $this->ui_renderer->render(
                    $availability_period_modal->withOnLoad(
                        $availability_period_modal->getShowSignal()
                    )
                )
            );
        }
        $this->renderObject();
    }

    /**
     * cancel create action and go back to repository parent
     */
    public function cancelCreation(): void
    {
        $this->ctrl->redirectByClass("ilrepositorygui", "");
    }

    public function saveObject(): void
    {
        // create permission is already checked in createObject. This check here is done to prevent hacking attempts
        if (!$this->checkPermissionBool("create", "", $this->requested_new_type)) {
            $this->error->raiseError($this->lng->txt("no_create_permission"), $this->error->MESSAGE);
        }

        $this->lng->loadLanguageModule($this->requested_new_type);
        $this->ctrl->setParameter($this, "new_type", $this->requested_new_type);

        $form = $this->initCreateForm($this->requested_new_type)
            ->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->tpl->setContent($this->getCreationFormsHTML($form));
            return;
        }

        $this->ctrl->setParameter($this, 'new_type', '');

        $class_name = 'ilObj' . $this->obj_definition->getClassName($this->requested_new_type);

        $new_obj = new $class_name();
        $new_obj->setType($this->requested_new_type);
        $new_obj->processAutoRating();
        $new_obj->create();

        $new_obj->getObjectProperties()->storePropertyTitleAndDescription(
            $data['title_and_description']
        );
        $new_obj->setTitle($new_obj->getObjectProperties()->getPropertyTitleAndDescription()->getTitle());
        $new_obj->setDescription($new_obj->getObjectProperties()->getPropertyTitleAndDescription()->getDescription());

        $this->putObjectInTree($new_obj);

        $dtpl = $data['didactic_templates'] ?? null;
        if ($dtpl !== null) {
            $dtpl_id = $this->parseDidacticTemplateVar($dtpl, 'dtpl');
            $new_obj->applyDidacticTemplate($dtpl_id);
        }

        $this->afterSave($new_obj);
    }

    /**
     * Get didactic template setting from creation screen
     */
    public function getDidacticTemplateVar(string $type): int
    {
        if (!$this->post_wrapper->has("didactic_type")) {
            return 0;
        }

        $tpl = $this->post_wrapper->retrieve("didactic_type", $this->refinery->kindlyTo()->string());
        return $this->parseDidacticTemplateVar($tpl, $type);
    }

    private function parseDidacticTemplateVar(string $var, string $type): int
    {
        if (substr($var, 0, strlen($type) + 1) != $type . "_") {
            return 0;
        }

        return (int) substr($var, strlen($type) + 1);
    }

    /**
     * Add object to tree at given position
     */
    public function putObjectInTree(ilObject $obj, int $parent_node_id = null): void
    {
        if (!$parent_node_id) {
            $parent_node_id = $this->requested_ref_id;
        }

        // add new object to custom parent container
        if ($this->requested_crtptrefid > 0) {
            $parent_node_id = $this->requested_crtptrefid;
        }

        $obj->createReference();
        $obj->putInTree($parent_node_id);
        $obj->setPermissions($parent_node_id);

        $this->obj_id = $obj->getId();
        $this->ref_id = $obj->getRefId();

        // BEGIN ChangeEvent: Record save object.
        ilChangeEvent::_recordWriteEvent($this->obj_id, $this->user->getId(), 'create');
        // END ChangeEvent: Record save object.

        // rbac log
        $rbac_log_roles = $this->rbac_review->getParentRoleIds($this->ref_id, false);
        $rbac_log = ilRbacLog::gatherFaPa($this->ref_id, array_keys($rbac_log_roles), true);
        ilRbacLog::add(ilRbacLog::CREATE_OBJECT, $this->ref_id, $rbac_log);

        // use forced callback after object creation
        $this->callCreationCallback($obj, $this->obj_definition, $this->requested_crtcb);
    }

    /**
     * Post (successful) object creation hook
     */
    protected function afterSave(ilObject $new_object): void
    {
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("object_added"), true);
        $this->ctrl->returnToParent($this);
    }

    public function editObject(): void
    {
        if (!$this->checkPermissionBool("write")) {
            $this->error->raiseError($this->lng->txt("msg_no_perm_write"), $this->error->MESSAGE);
        }

        $this->tabs_gui->activateTab("settings");

        $form = $this->initEditForm();
        $values = $this->getEditFormValues();
        if ($values) {
            $form->setValuesByArray($values);
        }

        $this->addExternalEditFormCustom($form);

        $this->tpl->setContent($form->getHTML());
    }

    public function addExternalEditFormCustom(ilPropertyFormGUI $form): void
    {
        // has to be done AFTER setValuesByArray() ...
    }

    protected function initEditForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;

        $lng->loadLanguageModule($this->object->getType());

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "update"));
        $form->setTitle($this->lng->txt($this->object->getType() . "_edit"));

        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $ta->setMaxNumOfChars(ilObject::LONG_DESC_LENGTH);
        $form->addItem($ta);

        $this->initEditCustomForm($form);

        $form->addCommandButton("update", $this->lng->txt("save"));

        return $form;
    }

    /**
     * Add custom fields to update form
     */
    protected function initEditCustomForm(ilPropertyFormGUI $a_form): void
    {
    }

    protected function getEditFormValues(): array
    {
        $values["title"] = $this->object->getTitle();
        $values["desc"] = $this->object->getLongDescription();
        $this->getEditFormCustomValues($values);
        return $values;
    }

    /**
     * Add values to custom edit fields
     */
    protected function getEditFormCustomValues(array &$a_values): void
    {
    }

    /**
     * updates object entry in object_data
     */
    public function updateObject(): void
    {
        if (!$this->checkPermissionBool("write")) {
            $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
        }

        $form = $this->initEditForm();
        if ($form->checkInput() && $this->validateCustom($form)) {
            $this->object->setTitle($form->getInput("title"));
            $this->object->setDescription($form->getInput("desc"));
            $this->updateCustom($form);
            $this->object->update();

            $this->afterUpdate();
            return;
        }

        // display form again to correct errors
        $this->tabs_gui->activateTab("settings");
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Validate custom values (if not possible with checkInput())
     */
    protected function validateCustom(ilPropertyFormGUI $form): bool
    {
        return true;
    }

    /**
     * Insert custom update form values into object
     */
    protected function updateCustom(ilPropertyFormGUI $form): void
    {
    }

    /**
     * Post (successful) object update hook
     */
    protected function afterUpdate(): void
    {
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "edit");
    }

    protected function buildImportFormInputs(): array
    {
        $trafo = $this->refinery->custom()->transformation(
            function ($vs): array {
                if ($vs === null) {
                    return null;
                }
                if (!isset($vs[1])) {
                    return [self::UPLOAD_TYPE_LOCAL => $vs[0][0]];
                } elseif ((int) $vs[1][0] === self::UPLOAD_TYPE_LOCAL) {
                    return [self::UPLOAD_TYPE_LOCAL => $vs[1][0][0]];
                } else {
                    $upload_factory = new ilImportDirectoryFactory();
                    $export_upload = $upload_factory->getInstanceForComponent(ilImportDirectoryFactory::TYPE_EXPORT);
                    $type = $this->extractFileTypeFromImportFilename($vs[1][0]) ?? '';
                    $file = $export_upload->getAbsolutePathForHash($this->user->getId(), $type, $vs[1][0]);
                    return [
                        self::UPLOAD_TYPE_UPLOAD_DIRECTORY => $file
                    ];
                }
            }
        );

        $constraint = $this->refinery->custom()->constraint(
            function ($vs): bool {
                $filename = $vs[self::UPLOAD_TYPE_LOCAL] ?? null;
                if ($filename === null) {
                    return $this->importFileOfValidType(basename($vs[self::UPLOAD_TYPE_UPLOAD_DIRECTORY]));
                }
                return $filename !== ''
                    && $this->temp_file_system->hasDir($filename)
                    && ($files = $this->temp_file_system->listContents($filename))
                    && $this->importFileOfValidType(basename($files[0]->getPath()));
            },
            $this->lng->txt('import_file_not_valid')
        );

        $import_directory_factory = new ilImportDirectoryFactory();
        $upload_files = $import_directory_factory->getInstanceForComponent(ilImportDirectoryFactory::TYPE_EXPORT)
            ->getFilesFor($this->user->getId());

        $field_factory = $this->ui_factory->input()->field();

        $file_upload_input = $field_factory->file(new \ImportUploadHandlerGUI(), $this->lng->txt('import_file'))
            ->withAcceptedMimeTypes(self::SUPPORTED_IMPORT_MIME_TYPES)
            ->withMaxFiles(1);

        if ($upload_files !== []) {
            $this->lng->loadLanguageModule('content');

            $file_upload_input = $field_factory->switchableGroup(
                [
                    self::UPLOAD_TYPE_LOCAL => $field_factory->group(
                        [$file_upload_input],
                        $this->lng->txt('cont_choose_local')
                    ),
                    self::UPLOAD_TYPE_UPLOAD_DIRECTORY => $field_factory->group(
                        [$field_factory->select($this->lng->txt('cont_uploaded_file'), $upload_files)->withRequired(true)],
                        $this->lng->txt('cont_choose_upload_dir'),
                    )
                ],
                $this->lng->txt('cont_choose_file_source')
            );
        }

        return [
            'upload' => $file_upload_input->withAdditionalTransformation($trafo)
                ->withAdditionalTransformation($constraint)
        ];
    }

    protected function routeImportCmdObject(): void
    {
        $modal = $this->buildImportModal()->withRequest($this->request);
        $data = $modal->getData();

        if ($data === null) {
            $this->tpl->setVariable(
                'IL_OBJECT_IMPORT_MODAL',
                $this->ui_renderer->render(
                    $modal->withOnLoad($modal->getShowSignal())
                )
            );
            $this->viewObject();
            return;
        }

        $file_to_import = $this->getFileToImportFromImportFormData($data);
        $new_type = $this->extractFileTypeFromImportFilename(basename($file_to_import));
        $path_to_uploaded_file_in_temp_dir = '';
        if (array_key_first($data['upload']) === self::UPLOAD_TYPE_LOCAL) {
            $path_to_uploaded_file_in_temp_dir = $data['upload'][self::UPLOAD_TYPE_LOCAL];
        }

        // create permission is already checked in createObject. This check here is done to prevent hacking attempts
        if (!$this->checkPermissionBool('create', '', $new_type)) {
            $this->deleteUploadedImportFile($path_to_uploaded_file_in_temp_dir);
            $this->error->raiseError($this->lng->txt('no_create_permission'));
        }

        $this->lng->loadLanguageModule($new_type);
        $this->ctrl->setParameter($this, 'new_type', $new_type);

        $target_class = 'ilObj' . $this->obj_definition->getClassName($new_type) . 'GUI';
        $target = new $target_class('', 0);
        $target->importFile($file_to_import, $path_to_uploaded_file_in_temp_dir);
        $this->viewObject();
    }

    protected function importFile(string $file_to_import, string $path_to_uploaded_file_in_temp_dir): void
    {
        if ($this instanceof ilContainerGUI) {
            $imp = new ilImportContainer($this->requested_ref_id);
        } else {
            $imp = new ilImport($this->requested_ref_id);
        }

        try {
            $new_id = $imp->importObject(
                null,
                $file_to_import,
                basename($file_to_import),
                $this->type,
                '',
                true
            );
        } catch (ilException $e) {
            $this->tmp_import_dir = $imp->getTemporaryImportDir();
            $this->lng->loadLanguageModule('obj');
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('obj_import_file_error') . ' <br />' . $e->getMessage()
            );
            $this->deleteUploadedImportFile($path_to_uploaded_file_in_temp_dir);
            return;
        }

        if ($new_id === null
            || $new_id === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('import_file_not_valid'));
            $this->deleteUploadedImportFile($path_to_uploaded_file_in_temp_dir);
            return;
        }

        $this->ctrl->setParameter($this, 'new_type', '');

        $new_obj = ilObjectFactory::getInstanceByObjId($new_id);
        // put new object id into tree - already done in import for containers
        if (!$this->obj_definition->isContainer($this->typ)) {
            $this->putObjectInTree($new_obj);
        } else {
            $ref_ids = ilObject::_getAllReferences($new_obj->getId());
            if (count($ref_ids) === 1) {
                $new_obj->setRefId((int) current($ref_ids));
            }
            $this->callCreationCallback($new_obj, $this->obj_definition, $this->requested_crtcb);   // see #24244
        }

        if ($path_to_uploaded_file_in_temp_dir !== ''
            && $this->temp_file_system->hasDir($path_to_uploaded_file_in_temp_dir)) {
            $this->temp_file_system->deleteDir($path_to_uploaded_file_in_temp_dir);
        }

        $this->afterImport($new_obj);
        $this->ctrl->setParameterByClass(get_class($new_obj), 'ref_id', $new_obj->getRefId());
        $this->ctrl->redirectByClass(get_class($new_obj));
    }

    protected function deleteUploadedImportFile(string $path_to_uploaded_file_in_temp_dir): void
    {
        if ($path_to_uploaded_file_in_temp_dir !== ''
            && $this->temp_file_system->hasDir($path_to_uploaded_file_in_temp_dir)) {
            $this->temp_file_system->deleteDir($path_to_uploaded_file_in_temp_dir);
        }
    }

    /**
     * Check if filename matches a given type
     */
    private function importFileOfValidType(string $filename): bool
    {
        $file_type = $this->extractFileTypeFromImportFilename($filename);
        if ($file_type === null
            || !in_array($file_type, $this->obj_definition->getAllObjects())) {
            return false;
        }
        return true;
    }

    protected function extractFileTypeFromImportFilename(string $filename): ?string
    {
        $matches = [];
        $result = preg_match('/[0-9]{10}__[0-9]{1,6}__([a-z]{1,4})_[0-9]{2,9}.zip/', $filename, $matches);
        if ($result === false
            || $result === 0
            || !isset($matches[1])) {
            return null;
        }
        return $matches[1];
    }

    protected function getFileToImportFromImportFormData(array $data)
    {
        $upload_data = $data['upload'];
        if (array_key_first($upload_data) === self::UPLOAD_TYPE_LOCAL
            && $this->temp_file_system->hasDir($upload_data[self::UPLOAD_TYPE_LOCAL])) {
            $files = $this->temp_file_system->listContents($upload_data[self::UPLOAD_TYPE_LOCAL]);
            return CLIENT_DATA_DIR . DIRECTORY_SEPARATOR
                . 'temp' . DIRECTORY_SEPARATOR
                . $files[0]->getPath();
        }
        return $upload_data[self::UPLOAD_TYPE_UPLOAD_DIRECTORY];
    }

    /**
     * Post (successful) object import hook
     */
    protected function afterImport(ilObject $new_object): void
    {
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("object_added"), true);
        $this->ctrl->returnToParent($this);
    }

    /**
     * Get form action for command (command is method name without "Object", e.g. "perm").
     */
    public function getFormAction(string $cmd, string $default_form_action = ""): string
    {
        if ($this->form_action[$cmd] != "") {
            return $this->form_action[$cmd];
        }

        return $default_form_action;
    }

    protected function setFormAction(string $cmd, string $form_action): void
    {
        $this->form_action[$cmd] = $form_action;
    }

    /**
     * Get return location for command (command is method name without "Object", e.g. "perm")
     */
    protected function getReturnLocation(string $cmd, string $default_location = ""): string
    {
        if (($this->return_location[$cmd] ?? "") !== "") {
            return $this->return_location[$cmd];
        } else {
            return $default_location;
        }
    }

    /**
     * set specific return location for command
     */
    protected function setReturnLocation(string $cmd, string $location): void
    {
        $this->return_location[$cmd] = $location;
    }

    /**
     * get target frame for command (command is method name without "Object", e.g. "perm")
     */
    protected function getTargetFrame(string $cmd, string $default_target_frame = ""): string
    {
        if (isset($this->target_frame[$cmd]) && $this->target_frame[$cmd] != "") {
            return $this->target_frame[$cmd];
        }

        if (!empty($default_target_frame)) {
            return "target=\"" . $default_target_frame . "\"";
        }

        return "";
    }

    /**
     * Set specific target frame for command
     */
    protected function setTargetFrame(string $cmd, string $target_frame): void
    {
        $this->target_frame[$cmd] = "target=\"" . $target_frame . "\"";
    }

    public function isVisible(int $ref_id, string $type): bool
    {
        $visible = $this->checkPermissionBool("visible,read", "", "", $ref_id);

        if ($visible && $type == 'crs') {
            $tree = $this->tree;
            if ($crs_id = $tree->checkForParentType($ref_id, 'crs')) {
                if (!$this->checkPermissionBool("write", "", "", $crs_id)) {
                    // Show only activated courses
                    $tmp_obj = ilObjectFactory::getInstanceByRefId($crs_id, false);

                    if (!$tmp_obj->isActivated()) {
                        unset($tmp_obj);
                        $visible = false;
                    }
                }
            }
        }

        return $visible;
    }

    /**
     * viewObject container presentation for "administration -> repository, trash, permissions"
     */
    public function viewObject(): void
    {
        $this->checkPermission('visible') && $this->checkPermission('read');

        $this->tabs_gui->activateTab('view');

        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $this->user->getId()
        );

        if (!$this->withReferences()) {
            $this->ctrl->setParameter($this, 'obj_id', $this->obj_id);
        }

        $itab = new ilAdminSubItemsTableGUI(
            $this,
            "view",
            $this->requested_ref_id,
            $this->checkPermissionBool('write')
        );

        $this->tpl->setContent($itab->getHTML());
    }

    /**
    * Display deletion confirmation screen.
    * Only for referenced objects. For user,role & rolt overwrite this function in the appropriate
    * Object folders classes (ilObjUserFolderGUI,ilObjRoleFolderGUI)
    */
    public function deleteObject(bool $error = false): void
    {
        $request_ids = [];
        if ($this->post_wrapper->has("id")) {
            $request_ids = $this->post_wrapper->retrieve(
                "id",
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        if (
            $this->request_wrapper->has("item_ref_id")
            && $this->request_wrapper->retrieve("item_ref_id", $this->refinery->kindlyTo()->string()) !== ""
        ) {
            $request_ids = [$this->request_wrapper->retrieve("item_ref_id", $this->refinery->kindlyTo()->int())];
        }

        if ($request_ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->returnToParent($this);
        }

        $modal_factory = $this->ui_factory->modal();
        $items = [];
        foreach (array_unique($request_ids) as $ref_id) {
            $items[] = $modal_factory->interruptiveItem()->standard(
                (string) $ref_id,
                ilObject::_lookupTitle(
                    ilObject::_lookupObjId($ref_id)
                )
            );
        }

        $msg = $this->lng->txt("info_delete_sure");
        if (!$this->settings->get('enable_trash')) {
            $msg .= "<br/>" . $this->lng->txt("info_delete_warning_no_trash");
        }

        $modal = $modal_factory->interruptive(
            $this->lng->txt('confirm'),
            $msg,
            $this->ctrl->getFormAction($this, 'confirmedDelete')
        )->withAffectedItems($items);
        $this->tpl->setVariable(
            'IL_OBJECT_EPHEMRAL_MODALS',
            $this->ui_renderer->render(
                $modal->withOnLoad($modal->getShowSignal())
            )
        );
        $this->renderObject();
    }

    /**
    * show possible sub objects (pull down menu)
    */
    protected function showPossibleSubObjects(): void
    {
        if ($this->sub_objects == "") {
            $sub_objects = $this->obj_definition->getCreatableSubObjects(
                $this->object->getType(),
                ilObjectDefinition::MODE_REPOSITORY,
                $this->ref_id
            );
        } else {
            $sub_objects = $this->sub_objects;
        }

        $subobj = [];
        if (count($sub_objects) > 0) {
            foreach ($sub_objects as $row) {
                $count = 0;
                if ($row["max"] > 0) {
                    //how many elements are present?
                    for ($i = 0; $i < count($this->data["ctrl"]); $i++) {
                        if ($this->data["ctrl"][$i]["type"] == $row["name"]) {
                            $count++;
                        }
                    }
                }

                if ($row["max"] == "" || $count < $row["max"]) {
                    $subobj[] = $row["name"];
                }
            }
        }

        if (count($subobj) > 0) {
            $opts = ilLegacyFormElementsUtil::formSelect(12, "new_type", $subobj);
            $this->tpl->setCurrentBlock("add_object");
            $this->tpl->setVariable("SELECT_OBJTYPE", $opts);
            $this->tpl->setVariable("BTN_NAME", "create");
            $this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
    * @abstract	overwrite in derived GUI class of your object type
    */
    protected function getTabs(): void
    {
    }

    /**
    * redirects to (repository) view per ref id
    * usually to a container and usually used at
    * the end of a save/import method where the object gui
    * type (of the new object) doesn't match with the type
    * of the current ["ref_id"] value of the request
    */
    protected function redirectToRefId(int $ref_id, string $cmd = ""): void
    {
        $obj_type = ilObject::_lookupType($ref_id, true);
        $class_name = $this->obj_definition->getClassName($obj_type);
        $class = strtolower("ilObj" . $class_name . "GUI");
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
        $this->ctrl->redirectByClass(["ilrepositorygui", $class], $cmd);
    }

    /**
    * Get center column
    */
    protected function getCenterColumnHTML(): string
    {
        $obj_id = ilObject::_lookupObjId($this->object->getRefId());
        $obj_type = ilObject::_lookupType($obj_id);

        if ($this->ctrl->getNextClass() != "ilcolumngui") {
            // normal command processing
            return $this->getContent();
        } else {
            if (!$this->ctrl->isAsynch()) {
                //if ($column_gui->getScreenMode() != IL_SCREEN_SIDE)
                if (ilColumnGUI::getScreenMode() != IL_SCREEN_SIDE) {
                    // right column wants center
                    if (ilColumnGUI::getCmdSide() == IL_COL_RIGHT) {
                        $column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);
                        $this->setColumnSettings($column_gui);
                        $this->html = $this->ctrl->forwardCommand($column_gui);
                    }
                    // left column wants center
                    if (ilColumnGUI::getCmdSide() == IL_COL_LEFT) {
                        $column_gui = new ilColumnGUI($obj_type, IL_COL_LEFT);
                        $this->setColumnSettings($column_gui);
                        $this->html = $this->ctrl->forwardCommand($column_gui);
                    }
                } else {
                    // normal command processing
                    return $this->getContent();
                }
            }
        }
        return "";
    }

    /**
    * Display right column
    */
    protected function getRightColumnHTML(): string
    {
        $obj_id = ilObject::_lookupObjId($this->object->getRefId());
        $obj_type = ilObject::_lookupType($obj_id);

        $column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);

        if ($column_gui->getScreenMode() == IL_SCREEN_FULL) {
            return "";
        }

        $this->setColumnSettings($column_gui);

        $html = "";
        if (
            $this->ctrl->getNextClass() == "ilcolumngui" &&
            $column_gui->getCmdSide() == IL_COL_RIGHT &&
            $column_gui->getScreenMode() == IL_SCREEN_SIDE
        ) {
            return $this->ctrl->forwardCommand($column_gui);
        }

        if (!$this->ctrl->isAsynch()) {
            return $this->ctrl->getHTML($column_gui);
        }

        return $html;
    }

    public function setColumnSettings(ilColumnGUI $column_gui): void
    {
        $column_gui->setRepositoryMode(true);
        $column_gui->setEnableEdit(false);
        if ($this->checkPermissionBool("write")) {
            $column_gui->setEnableEdit(true);
        }
    }

    protected function checkPermission(string $perm, string $cmd = "", string $type = "", ?int $ref_id = null): void
    {
        if (!$this->checkPermissionBool($perm, $cmd, $type, $ref_id)) {
            if (!is_int(strpos($_SERVER["PHP_SELF"], "goto.php"))) {
                if ($perm != "create" && !is_object($this->object)) {
                    return;
                }

                ilSession::clear("il_rep_ref_id");

                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_read'), true);
                $parent_ref_id = (int) $this->tree->getParentNodeData($this->object->getRefId())['ref_id'];
                $this->ctrl->redirectToURL(ilLink::_getLink($parent_ref_id));
            }

            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_read'), true);
            self::_gotoRepositoryRoot();
        }
    }

    protected function checkPermissionBool(string $perm, string $cmd = "", string $type = "", ?int $ref_id = null): bool
    {
        if ($perm == "create") {
            if (!$ref_id) {
                $ref_id = $this->requested_ref_id;
            }
            return $this->access->checkAccess($perm . "_" . $type, $cmd, $ref_id);
        }

        if (!is_object($this->object)) {
            return false;
        }

        if (!$ref_id) {
            $ref_id = $this->object->getRefId();
        }

        return $this->access->checkAccess($perm, $cmd, $ref_id);
    }

    /**
     * Goto repository root
     *
     * @param
     * @return
     */
    public static function _gotoRepositoryRoot(bool $raise_error = false): void
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();
        $ctrl = $DIC->ctrl();

        if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $ctrl->setParameterByClass("ilRepositoryGUI", "ref_id", ROOT_FOLDER_ID);
            $ctrl->redirectByClass("ilRepositoryGUI");
        }

        if ($raise_error) {
            $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
        }
    }

    public static function _gotoRepositoryNode(int $ref_id, string $cmd = ""): void
    {
        global $DIC;

        $ctrl = $DIC->ctrl();
        $ctrl->setParameterByClass("ilRepositoryGUI", "ref_id", $ref_id);
        $ctrl->redirectByClass("ilRepositoryGUI", $cmd);
    }

    public static function _gotoSharedWorkspaceNode(int $wsp_id): void
    {
        global $DIC;

        $ctrl = $DIC->ctrl();
        $ctrl->setParameterByClass(ilSharedResourceGUI::class, "wsp_id", $wsp_id);
        $ctrl->redirectByClass(ilSharedResourceGUI::class, "");
    }

    /**
     * Enables the file upload into this object by dropping files.
     */
    protected function enableDragDropFileUpload(): void
    {
        $this->tpl->setFileUploadRefId($this->ref_id);
    }

    public function addToDeskObject(): void
    {
        $this->favourites->add(
            $this->user->getId(),
            $this->request_wrapper->retrieve("item_ref_id", $this->refinery->kindlyTo()->int())
        );
        $this->lng->loadLanguageModule("rep");
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("rep_added_to_favourites"), true);
        $this->ctrl->redirectToURL(ilLink::_getLink($this->requested_ref_id));
    }

    public function removeFromDeskObject(): void
    {
        $this->lng->loadLanguageModule("rep");
        $item_ref_id = $this->request_wrapper->retrieve("item_ref_id", $this->refinery->kindlyTo()->int());
        $this->favourites->remove($this->user->getId(), $item_ref_id);
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("rep_removed_from_favourites"), true);
        $this->ctrl->redirectToURL(ilLink::_getLink($this->requested_ref_id));
    }

    protected function getCreatableObjectTypes(): array
    {
        $subtypes = $this->obj_definition->getCreatableSubObjects(
            $this->object->getType(),
            ilObjectDefinition::MODE_REPOSITORY,
            $this->object->getRefId()
        );

        return array_filter(
            $subtypes,
            fn($key) => $this->access->checkAccess('create_' . $key, '', $this->ref_id, $this->type),
            ARRAY_FILTER_USE_KEY
        );
    }

    protected function buildAddNewItemElements(
        array $subtypes,
        string $create_target_class = ilRepositoryGUI::class,
        ?int $redirect_target_ref_id = null,
    ): array {
        if ($redirect_target_ref_id !== null) {
            $this->ctrl->setParameterByClass(self::class, 'crtcb', (string) $redirect_target_ref_id);
        }

        $elements = $this->initAddNewItemElementsFromNewItemGroups(
            $create_target_class,
            \ilObjRepositorySettings::getNewItemGroups(),
            \ilObjRepositorySettings::getNewItemGroupSubItems(),
            $subtypes
        );
        if ($elements === []) {
            $elements = $this->initAddnewItemElementsFromDefaultGroups(
                $create_target_class,
                \ilObjRepositorySettings::getDefaultNewItemGrouping(),
                $subtypes
            );
        }

        $this->ctrl->clearParameterByClass(self::class, 'crtcb');
        return $elements;
    }

    private function initAddNewItemElementsFromNewItemGroups(
        string $create_target_class,
        array $new_item_groups,
        array $new_item_groups_subitems,
        array $subtypes
    ): array {
        if ($new_item_groups === []) {
            return [];
        }

        $new_item_groups[0] = $this->lng->txt('rep_new_item_group_other');
        $add_new_item_elements = [];
        foreach ($new_item_groups as $group_id => $group) {
            $group_element = $this->buildGroup(
                $create_target_class,
                $new_item_groups_subitems[$group_id],
                $group['title'] ?? $group,
                $subtypes
            );

            if ($group_element !== null) {
                $add_new_item_elements[] = $group_element;
            }
        }

        return $add_new_item_elements;
    }

    private function initAddnewItemElementsFromDefaultGroups(
        string $create_target_class,
        array $default_groups,
        array $subtypes
    ): array {
        $add_new_item_elements = [];
        foreach ($default_groups['groups'] as $group_id => $group) {
            $obj_types_in_group = array_keys(
                array_filter(
                    $default_groups['items'],
                    fn($item_group_id) => $item_group_id === $group_id
                )
            );

            $group_element = $this->buildGroup(
                $create_target_class,
                $obj_types_in_group,
                $group['title'],
                $subtypes
            );

            if ($group_element !== null) {
                $add_new_item_elements[$group['pos']] = $group_element;
            }
        }

        return $add_new_item_elements;
    }

    protected function buildGroup(
        string $create_target_class,
        array $obj_types_in_group,
        string $title,
        array $subtypes
    ): ?AddNewItemElement {
        $add_new_items_content_array = $this->buildSubItemsForGroup(
            $create_target_class,
            $obj_types_in_group,
            $subtypes
        );
        if ($add_new_items_content_array === []) {
            return null;
        }
        return new AddNewItemElement(
            AddNewItemElementTypes::Group,
            $title,
            null,
            null,
            $add_new_items_content_array
        );
    }

    private function buildSubItemsForGroup(
        string $create_target_class,
        array $obj_types_in_group,
        array $subtypes
    ): array {
        $add_new_items_content_array = [];
        foreach($obj_types_in_group as $type) {
            if (!array_key_exists($type, $subtypes)) {
                continue;
            }
            $subitem = $subtypes[$type];
            $this->ctrl->setParameterByClass($create_target_class, 'new_type', $type);
            $add_new_items_content_array[$subitem['pos']] = new AddNewItemElement(
                AddNewItemElementTypes::Object,
                $this->lng->txt('obj_' . $type),
                $this->ui_factory->symbol()->icon()->standard($type, ''),
                new URI(
                    ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass($create_target_class, 'create')
                )
            );
            $this->ctrl->clearParameterByClass($create_target_class, 'new_type', $type);
        }
        ksort($add_new_items_content_array);
        return $add_new_items_content_array;
    }

    private function maskTemplateMarkers(string $string): string
    {
        return str_replace(['{', '}'], ['&#123;', '&#125;'], $string);
    }
}
