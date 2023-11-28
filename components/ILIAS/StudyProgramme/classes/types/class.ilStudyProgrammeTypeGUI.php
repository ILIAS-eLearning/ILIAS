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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input;
use ILIAS\UI\Component\Input\Container\Form\FormInput as InputField;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Renderer;
use ILIAS\Refinery;
use ILIAS\Filesystem\Filesystem;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilStudyProgrammeTypeGUI
{
    /*ilObjStudyProgrammeGUI|ilObjStudyProgrammeAdminGUI*/
    protected $parent_gui;
    protected ?array $installed_languages = null;
    protected Input\Factory $input_factory;
    protected ?ilStudyProgrammeType $type = null;
    protected URLBuilder $url_builder;
    protected URLBuilderToken $action_token;
    protected URLBuilderToken $id_token;

    public function __construct(
        public ilGlobalTemplateInterface $tpl,
        public ilCtrl $ctrl,
        protected ilAccess $access,
        protected ilToolbarGUI $toolbar,
        protected ilLanguage $lng,
        protected ILIAS $ilias,
        protected ilTabsGUI $tabs,
        protected ilObjUser $user,
        protected ilStudyProgrammeTypeRepository $type_repository,
        protected UIFactory $ui_factory,
        protected Renderer $renderer,
        DataFactory $data_factory,
        protected Psr\Http\Message\ServerRequestInterface $request,
        protected Refinery\Factory $refinery,
        protected RequestWrapper $request_wrapper
    ) {
        $this->lng->loadLanguageModule('prg');
        $this->lng->loadLanguageModule('meta');
        $this->input_factory = $ui_factory->input();

        $here_uri = $data_factory->uri($this->request->getUri()->__toString());
        $url_builder = new URLBuilder($here_uri);
        $namespace = ['prgtypes'];
        list($url_builder, $action_token, $id_token) =
        $url_builder->acquireParameters(
            $namespace,
            "table_action",
            "type_ids"
        );

        $this->url_builder = $url_builder;
        $this->action_token = $action_token;
        $this->id_token = $id_token;
    }


    protected function getCommandFromQueryToken(string $default): string
    {
        if($this->request_wrapper->has($this->action_token->getName())) {
            return $this->request_wrapper->retrieve($this->action_token->getName(), $this->refinery->to()->string());
        }
        return $default;
    }

    protected function getTypeIdFromQueryToken(): int
    {
        if($this->request_wrapper->has($this->id_token->getName())) {
            $type_id = $this->request_wrapper->retrieve(
                $this->id_token->getName(),
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->int(),
                    $this->refinery->custom()->transformation(static fn($v): int => (int)current($v)),
                ])
            );
            return $type_id;
        }
        throw new \Exception('No type id found in query.');
    }

    protected function getUrl(string $action, int $type_id = null): string
    {
        $url_builder = $this->url_builder->withParameter($this->action_token, $action);
        if($type_id) {
            $url_builder = $url_builder->withParameter($this->id_token, [$type_id]);
        }
        return $url_builder->buildURI()->__toString();
    }


    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case strtolower(ilStudyProgrammeTypeUploadHandlerGUI::class):
                $type_id = $this->getTypeIdFromQueryToken();
                $type = $this->type_repository->getType($type_id);
                $this->ctrl->forwardCommand(
                    new ilStudyProgrammeTypeUploadHandlerGUI($type->getIconIdentifier())
                );
                break;
        }

        $this->checkAccess();
        $cmd = $this->getCommandFromQueryToken('view');

        switch ($cmd) {
            case 'view':
            case 'listTypes':
                $this->listTypes();
                break;
            case 'add':
                $this->add();
                break;
            case 'edit':
                $this->setSubTabsEdit('general');
                $this->edit($this->getTypeIdFromQueryToken());
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

    public function setParentGUI($parent_gui): void
    {
        $this->parent_gui = $parent_gui;
    }

    protected function checkAccess(): void
    {
        if (!$this->access->checkAccess("read", "", $this->parent_gui->getObject()->getRefId())) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this->parent_gui);
        }
    }

    protected function setSubTabsEdit(string $active_tab_id): void
    {
        $type_id = $this->getTypeIdFromQueryToken();
        $this->tabs->addSubTab(
            'general',
            $this->lng->txt('meta_general'),
            $this->getUrl('edit', $type_id)
        );

        if ($this->ilias->getSetting('custom_icons')) {
            $this->tabs->addSubTab(
                'custom_icons',
                $this->lng->txt('icon_settings'),
                $this->getUrl('editCustomIcons', $type_id)
            );
        }

        if (count($this->type_repository->getAllAMDRecordIds()) > 0) {
            $this->tabs->addSubTab(
                'amd',
                $this->lng->txt('md_advanced'),
                $this->getUrl('editAMD', $type_id)
            );
        }

        $this->tabs->activateSubTab($active_tab_id);
    }

    protected function getIconForm(
        string $section_title = null,
        string $current_identifier = null
    ): StandardForm {
        $handler_gui = new ilStudyProgrammeTypeUploadHandlerGUI();

        $input = $this->ui_factory->input()->field()->file(
            $handler_gui,
            $this->lng->txt('icon'),
            $this->lng->txt('file_allowed_suffixes') . ' .svg'
        );

        if($current_identifier) {
            $input = $input->withValue([$current_identifier]);
        }

        $section = $this->ui_factory->input()->field()->section(
            ['iconfile' => $input],
            $section_title . $this->lng->txt('prg_type_custom_icon'),
            $this->lng->txt('prg_type_custom_icon_info')
        );

        $this->ctrl->setParameter($this, $this->id_token->getName(), $this->getTypeIdFromQueryToken());
        $this->ctrl->setParameter($this, $this->action_token->getName(), 'updateCustomIcons');
        $form_action = $this->ctrl->getFormAction($this, 'updateCustomIcons');
        $form = $this->ui_factory->input()->container()->form()->standard(
            $form_action,
            [$section]
        );

        $form = $form->withAdditionalTransformation(
            $this->refinery->custom()->transformation(
                function ($values) {
                    return array_shift($values)['iconfile'];
                }
            )
        );
        return $form;
    }

    protected function editCustomIcons(): void
    {
        $type = $this->type_repository->getType($this->getTypeIdFromQueryToken());
        $form = $this->getIconForm(
            $type->getTitle() . ': ',
            $type->getIconIdentifier()
        );
        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function updateCustomIcons(): void
    {
        $type_id = $this->getTypeIdFromQueryToken();
        $type = $this->type_repository->getType($type_id);

        $data = $this->getIconForm()
            ->withRequest($this->request)
            ->getData();

        if($data) {
            $type = $type->withIconIdentifier(current($data));
            $this->tpl->setOnScreenMessage("success", $this->lng->txt('msg_obj_modified'), true);
        } else {
            if($identifier = $type->getIconIdentifier()) {
                $this->type_repository->removeIconFromIrss($identifier);
            }
            $type = $type->withIconIdentifier(null);
            $this->tpl->setOnScreenMessage("success", $this->lng->txt('icon_removed'), true);
        }
        $this->type_repository->updateType($type);
        $type->updateAssignedStudyProgrammesIcons();
        $this->ctrl->redirectToURL($this->getUrl('editCustomIcons', $type_id));
    }

    protected function editAMD(): void
    {
        $type_id = $this->getTypeIdFromQueryToken();
        $type = $this->type_repository->getType($type_id);
        $form = new ilStudyProgrammeTypeAdvancedMetaDataFormGUI(
            $this->getUrl('updateAMD', $type_id),
            $this->type_repository,
            $this->tpl,
            $this->lng
        );
        $form->fillForm($type);
        $this->tpl->setContent($form->getHTML());
    }

    protected function updateAMD(): void
    {
        $type_id = $this->getTypeIdFromQueryToken();
        $type = $this->type_repository->getType($type_id);
        $form = new ilStudyProgrammeTypeAdvancedMetaDataFormGUI(
            $this->getUrl('updateAMD', $type_id),
            $this->type_repository,
            $this->tpl,
            $this->lng
        );

        if ($form->saveObject($type)) {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirectToURL($this->getUrl('editAMD', $type_id));
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }

    protected function listTypes(): void
    {
        $table = $this->type_repository->getTable();

        if ($this->access->checkAccess("write", "", $this->parent_gui->getObject()->getRefId())) {
            $link = $this->ui_factory->link()->standard(
                $this->lng->txt('prg_subtype_add'),
                $this->getUrl('add')
            );
            $this->toolbar->addComponent($link);

            $actions = [
                'edit' => $this->ui_factory->table()->action()->single(
                    $this->lng->txt('edit'),
                    $this->url_builder->withParameter($this->action_token, "edit"),
                    $this->id_token
                ),
                'delete' => $this->ui_factory->table()->action()->single(
                    $this->lng->txt('delete'),
                    $this->url_builder->withParameter($this->action_token, "delete"),
                    $this->id_token
                )
            ];
            $table = $table->withActions($actions);
        }

        $this->tpl->setContent(
            $this->renderer->render($table->withRequest($this->request))
        );
    }

    protected function buildForm(
        string $submit_action,
        string $type_action
    ): Input\Container\Form\Form {
        $default_lng = $this->type ? $this->type->getDefaultLang() : "";
        return $this->input_factory->container()->form()->standard(
            $submit_action,
            [
                "default_lang" => $this->buildModalHeading($type_action, $default_lng),
                "info" => $this->buildLanguagesForms($this->type)
            ]
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(
                function ($values) {
                    return [
                        'default_lang' => $values['default_lang']['default_lang'],
                        'info' => $values['info']
                    ];
                }
            )
        );
    }

    protected function add(): void
    {
        $form = $this->buildForm(
            $this->getUrl('create'),
            $this->lng->txt('prg_type_add')
        );

        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function edit(int $type_id): void
    {
        $this->type = $this->type_repository->getType($type_id);
        $form = $this->buildForm(
            $this->getUrl('update', $type_id),
            $this->lng->txt('prg_type_edit')
        );

        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function create(): void
    {
        $form = $this->buildForm(
            $this->getUrl('create'),
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

    protected function update(): void
    {
        $type_id = $this->getTypeIdFromQueryToken();
        $form = $this->buildForm(
            $this->getUrl('update', $type_id),
            $this->lng->txt('prg_type_edit')
        )->withRequest($this->request);

        $result = $form->getData();
        if (!is_null($result)) {
            $type = $this->type_repository->getType($type_id);
            $this->updateTypeFromFormResult($type, $result);
            $this->tpl->setOnScreenMessage("success", $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, 'view');
        } else {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("msg_fill_required"), true);
            $this->tpl->setContent($this->renderer->render($form));
        }
    }

    protected function updateTypeFromFormResult(ilStudyProgrammeType $type, array $result): void
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

    protected function delete(): void
    {
        $type_id = $this->getTypeIdFromQueryToken();
        try {
            $type = $this->type_repository->getType($type_id);
            $this->type_repository->deleteType($type);
            $this->tpl->setOnScreenMessage("success", $this->lng->txt('prg_type_msg_deleted'), true);
            $this->ctrl->redirect($this);
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage("failure", $e->getMessage(), true);
            $this->ctrl->redirect($this);
        }
    }

    protected function buildModalHeading(string $title, string $default_lng): InputField
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

    protected function buildLanguagesForms(ilStudyProgrammeType $type = null): InputField
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
                $this->refinery
            );
        }

        return $this->input_factory->field()->group($return);
    }

    protected function getInstalledLanguages(): array
    {
        if (is_null($this->installed_languages)) {
            $this->installed_languages = $this->lng->getInstalledLanguages();
        }

        return $this->installed_languages;
    }
}
