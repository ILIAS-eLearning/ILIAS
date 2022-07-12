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

use ILIAS\UI\Component\Input;
use ILIAS\UI\Component\Input\Field\Input as InputField;
use ILIAS\UI\Renderer;
use ILIAS\Refinery;
use ILIAS\Filesystem\Filesystem;
use ILIAS\HTTP\Wrapper\RequestWrapper;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilStudyProgrammeTypeGUI
{
    public ilGlobalTemplateInterface $tpl;
    public ilCtrl $ctrl;
    protected ilAccess $access;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ILIAS $ilias;
    protected ilTabsGUI $tabs;
    protected ilObjUser $user;
    protected ilStudyProgrammeTypeRepository $type_repository;
    protected Input\Factory $input_factory;
    protected Renderer $renderer;
    protected Psr\Http\Message\ServerRequestInterface $request;
    protected Refinery\Factory $refinery_factory;
    protected /*ilObjStudyProgrammeGUI|ilObjStudyProgrammeAdminGUI*/ $parent_gui;
    protected ?array $installed_languages = null;
    protected Filesystem $web_dir;
    protected RequestWrapper $request_wrapper;

    public function __construct(
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilAccess $ilAccess,
        ilToolbarGUI $ilToolbar,
        ilLanguage $lng,
        ILIAS $ilias,
        ilTabsGUI $ilTabs,
        ilObjUser $user,
        ilStudyProgrammeTypeRepository $type_repository,
        Input\Factory $input_factory,
        Renderer $renderer,
        Psr\Http\Message\ServerRequestInterface $request,
        Refinery\Factory $refinery_factory,
        Filesystem $web_dir,
        RequestWrapper $request_wrapper
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->access = $ilAccess;
        $this->toolbar = $ilToolbar;
        $this->tabs = $ilTabs;
        $this->user = $user;
        $this->lng = $lng;
        $this->ilias = $ilias;
        $this->type_repository = $type_repository;
        $this->input_factory = $input_factory;
        $this->renderer = $renderer;
        $this->request = $request;
        $this->refinery_factory = $refinery_factory;
        $this->web_dir = $web_dir;
        $this->request_wrapper = $request_wrapper;

        $this->lng->loadLanguageModule('prg');
        $this->ctrl->saveParameter($this, 'type_id');
        $this->lng->loadLanguageModule('meta');
    }

    public function executeCommand() : void
    {
        $this->checkAccess();
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case '':
            case 'view':
            case 'listTypes':
                $this->listTypes();
                break;
            case 'add':
                $this->add();
                break;
            case 'edit':
                $this->setSubTabsEdit('general');
                $this->edit();
                break;
            case 'editCustomIcons':
                $this->setSubTabsEdit('custom_icons');
                $this->editCustomIcons();
                break;
            case 'editAMD':
                $this->setSubTabsEdit('amd');
                $this->editAMD();
                break;
            case 'updateAMD':
                $this->setSubTabsEdit('amd');
                $this->updateAMD();
                break;
            case 'updateCustomIcons':
                $this->setSubTabsEdit('custom_icons');
                $this->updateCustomIcons();
                break;
            case 'create':
                $this->create();
                break;
            case 'update':
                $this->setSubTabsEdit('general');
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'cancel':
                $this->ctrl->redirect($this->parent_gui);
                break;
            default:
                throw new LogicException("Unknown command: $cmd");
        }
    }

    public function setParentGUI($parent_gui) : void
    {
        $this->parent_gui = $parent_gui;
    }

    protected function checkAccess() : void
    {
        if (!$this->access->checkAccess("read", "", $this->parent_gui->getObject()->getRefId())) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this->parent_gui);
        }
    }

    protected function setSubTabsEdit(string $active_tab_id) : void
    {
        $this->tabs->addSubTab(
            'general',
            $this->lng->txt('meta_general'),
            $this->ctrl->getLinkTarget($this, 'edit')
        );

        if ($this->ilias->getSetting('custom_icons')) {
            $this->tabs->addSubTab(
                'custom_icons',
                $this->lng->txt('icon_settings'),
                $this->ctrl->getLinkTarget($this, 'editCustomIcons')
            );
        }

        if (count($this->type_repository->getAllAMDRecordIds()) > 0) {
            $this->tabs->addSubTab(
                'amd',
                $this->lng->txt('md_advanced'),
                $this->ctrl->getLinkTarget($this, 'editAMD')
            );
        }

        $this->tabs->activateSubTab($active_tab_id);
    }

    protected function editCustomIcons() : void
    {
        $form = new ilStudyProgrammeTypeCustomIconsFormGUI(
            $this,
            $this->type_repository,
            $this->ctrl,
            $this->tpl,
            $this->lng,
            $this->user,
            $this->web_dir
        );
        $form->fillForm($this->type_repository->getType(
            $this->request_wrapper->retrieve("type_id", $this->refinery_factory->kindlyTo()->int())
        ));
        $this->tpl->setContent($form->getHTML());
    }

    protected function updateCustomIcons() : void
    {
        $form = new ilStudyProgrammeTypeCustomIconsFormGUI(
            $this,
            $this->type_repository,
            $this->ctrl,
            $this->tpl,
            $this->lng,
            $this->user,
            $this->web_dir
        );
        $type_id = $this->request_wrapper->retrieve("type_id", $this->refinery_factory->kindlyTo()->int());
        if ($form->saveObject($this->type_repository->getType($type_id))) {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, 'editCustomIcons');
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }

    protected function editAMD() : void
    {
        $form = new ilStudyProgrammeTypeAdvancedMetaDataFormGUI(
            $this,
            $this->type_repository,
            $this->ctrl,
            $this->tpl,
            $this->lng
        );
        $type_id = $this->request_wrapper->retrieve("type_id", $this->refinery_factory->kindlyTo()->int());
        $form->fillForm($this->type_repository->getType($type_id));
        $this->tpl->setContent($form->getHTML());
    }

    protected function updateAMD() : void
    {
        $form = new ilStudyProgrammeTypeAdvancedMetaDataFormGUI(
            $this,
            $this->type_repository,
            $this->ctrl,
            $this->tpl,
            $this->lng
        );
        $type_id = $this->request_wrapper->retrieve("type_id", $this->refinery_factory->kindlyTo()->int());
        if ($form->saveObject($this->type_repository->getType($type_id))) {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, 'editAMD');
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }

    protected function listTypes() : void
    {
        if ($this->access->checkAccess("write", "", $this->parent_gui->getObject()->getRefId())) {
            $button = ilLinkButton::getInstance();
            $button->setCaption('prg_subtype_add');
            $button->setUrl($this->ctrl->getLinkTarget($this, 'add'));
            $this->toolbar->addButtonInstance($button);
        }
        $table = new ilStudyProgrammeTypeTableGUI(
            $this,
            'listTypes',
            $this->parent_gui->getObject()->getRefId(),
            $this->type_repository,
            $this->ctrl,
            $this->tabs,
            $this->access,
            $this->lng,
            $this->web_dir
        );
        $this->tpl->setContent($table->getHTML());
    }

    protected function add() : void
    {
        $form = $this->buildForm(
            $this->ctrl->getFormActionByClass(
                __CLASS__,
                'create'
            ),
            $this->lng->txt('prg_type_add')
        );

        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function edit() : void
    {
        $type_id = $this->request_wrapper->retrieve("type_id", $this->refinery_factory->kindlyTo()->int());
        $type = $this->type_repository->getType($type_id);

        $form = $this->buildForm(
            $this->ctrl->getFormActionByClass(
                __CLASS__,
                'update'
            ),
            $this->lng->txt('prg_type_edit'),
            $type
        );

        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function create() : void
    {
        $form = $this->buildForm(
            $this->ctrl->getFormActionByClass(
                __CLASS__,
                'create'
            ),
            $this->lng->txt('prg_type_add')
        )->withRequest($this->request);

        $result = $form->getData();
        if (!is_null($result)) {
            $type = $this->type_repository->createType($this->lng->getDefaultLanguage());
            $this->updateTypeFromFormResult($type, $result);
            $this->tpl->setOnScreenMessage("success", $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, 'view');
        } else {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("msg_fill_required"), true);
            $this->tpl->setContent($this->renderer->render($form));
        }
    }

    protected function update() : void
    {
        $type_id = $this->request_wrapper->retrieve("type_id", $this->refinery_factory->kindlyTo()->int());
        $type = $this->type_repository->getType($type_id);
        $form = $this->buildForm(
            $this->ctrl->getFormActionByClass(
                __CLASS__,
                'update'
            ),
            $this->lng->txt('prg_type_edit'),
            $type
        )->withRequest($this->request);

        $result = $form->getData();
        if (!is_null($result)) {
            $this->updateTypeFromFormResult($type, $result);
            $this->tpl->setOnScreenMessage("success", $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, 'view');
        } else {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("msg_fill_required"), true);
            $this->tpl->setContent($this->renderer->render($form));
        }
    }

    protected function updateTypeFromFormResult(ilStudyProgrammeType $type, array $result) : void
    {
        if (isset($result['default_lang'])) {
            $type->setDefaultLang($result['default_lang']);
        }

        if (isset($result['info'])) {
            /** @var ilStudyProgrammeTypeInfo $info */
            foreach ($result['info'] as $info) {
                $type->setTitle($info->getTitle(), $info->getLanguageCode());
                $type->setDescription($info->getDescription(), $info->getLanguageCode());
            }
        }
        $this->type_repository->updateType($type);
    }

    protected function delete() : void
    {
        $type_id = $this->request_wrapper->retrieve("type_id", $this->refinery_factory->kindlyTo()->int());
        $type = $this->type_repository->getType($type_id);
        try {
            $this->type_repository->deleteType($type);
            $this->tpl->setOnScreenMessage("success", $this->lng->txt('prg_type_msg_deleted'), true);
            $this->ctrl->redirect($this);
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage("failure", $e->getMessage(), true);
            $this->ctrl->redirect($this);
        }
    }

    protected function buildForm(
        string $submit_action,
        string $type_action,
        ilStudyProgrammeType $type = null
    ) : Input\Container\Form\Form {
        $default_lng = "";
        if (!is_null($type)) {
            $default_lng = $type->getDefaultLang();
        }

        return $this->input_factory->container()->form()->standard(
            $submit_action,
            [
                "default_lang" => $this->buildModalHeading($type_action, $default_lng),
                "info" => $this->buildLanguagesForms($type)
            ]
        )->withAdditionalTransformation(
            $this->refinery_factory->custom()->transformation(
                function ($values) {
                    return [
                        'default_lang' => $values['default_lang']['default_lang'],
                        'info' => $values['info']
                    ];
                }
            )
        );
    }

    protected function buildModalHeading(string $title, string $default_lng) : InputField
    {
        $options = [];
        foreach ($this->getInstalledLanguages() as $lang_code) {
            $options[$lang_code] = $this->lng->txt("meta_l_$lang_code");
        }

        $select = $this->input_factory->field()->select(
            $this->lng->txt('default_language'),
            $options,
            ''
        )->withValue($default_lng)
         ->withRequired(true);

        return $this->input_factory->field()->section(['default_lang' => $select], $title);
    }

    protected function buildLanguagesForms(ilStudyProgrammeType $type = null) : InputField
    {
        $return = [];
        foreach ($this->getInstalledLanguages() as $lng_code) {
            $title = null;
            $description = null;
            if (!is_null($type)) {
                $title = $type->getTitle($lng_code);
                $description = $type->getDescription($lng_code);
            }
            $lng_field = new ilStudyProgrammeTypeInfo($title, $description, $lng_code);
            $return[] = $lng_field->toFormInput(
                $this->input_factory->field(),
                $this->lng,
                $this->refinery_factory
            );
        }

        return $this->input_factory->field()->group($return);
    }

    protected function getInstalledLanguages() : array
    {
        if (is_null($this->installed_languages)) {
            $this->installed_languages = $this->lng->getInstalledLanguages();
        }

        return $this->installed_languages;
    }
}
