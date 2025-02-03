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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;

/**
 * Global orgunit settings GUI
 * @author            Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_IsCalledBy ilOrgUnitGlobalSettingsGUI: ilObjOrgUnitGUI
 */
class ilOrgUnitGlobalSettingsGUI
{
    protected const CMD_EDIT = 'edit';
    protected const CMD_SAVE = 'save';

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;

    protected ilSetting $settings;
    protected ilObjectDefinition $object_definition;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected Refinery $refinery;
    protected ServerRequestInterface $request;

    public function __construct()
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('orgu');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->request = $DIC->http()->request();
        $to_int = $DIC['refinery']->kindlyTo()->int();
        $ref_id = $DIC['http']->wrapper()->query()->retrieve('ref_id', $to_int);

        if (!ilObjOrgUnitAccess::_checkAccessSettings($ref_id)) {
            $main_tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilObjOrgUnitGUI::class);
        }

        $this->settings = $DIC->settings();
        $this->object_definition = $DIC["objDefinition"];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->refinery = $DIC['refinery'];
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd(self::CMD_EDIT);
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            default:
                switch ($cmd) {
                    case self::CMD_SAVE:
                        $this->save();
                        break;
                    case self::CMD_EDIT:
                    default:
                        $this->edit();
                }
        }
    }

    private function edit(): void
    {
        $form = $this->getSettingsForm();
        $this->tpl->setContent(
            $this->ui_renderer->render($form)
        );
    }

    private function save(): void
    {
        $form = $this->getSettingsForm()->withRequest($this->request);
        $data = $form->getData();

        if (!$data) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), false);
            $this->tpl->setContent($this->ui_renderer->render($form));
        }
        $enable_my_staff = current(array_shift($data));
        $obj_settings = array_shift($data);

        $this->settings->set("enable_my_staff", $enable_my_staff);

        $available_types = $this->object_definition->getOrgUnitPermissionTypes();
        foreach ($available_types as $object_type) {

            $active = false;
            $changeable = false;
            $default = false;
            if (!is_null($obj_settings[$object_type])) {
                list($active, $changeable, $default) = array_shift($obj_settings[$object_type]);
            }
            $obj_setting = new ilOrgUnitObjectTypePositionSetting($object_type);
            $obj_setting->setActive($active);
            $obj_setting->setChangeableForObject($changeable);
            $obj_setting->setActivationDefault((int) $default);
            $obj_setting->update();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::CMD_EDIT);
    }

    protected function getSettingsForm(): StandardForm
    {
        $sections = [];
        $sections[] = $this->ui_factory->input()->field()->section(
            [
                $this->ui_factory->input()->field()->checkbox(
                    $this->lng->txt("orgu_enable_my_staff"),
                    $this->lng->txt("orgu_enable_my_staff_info")
                )
                ->withValue($this->settings->get("enable_my_staff") ? true : false)
            ],
            $this->lng->txt("orgu_enable_my_staff")
        );

        $groups = [];
        $values = [];
        $available_types = $this->object_definition->getOrgUnitPermissionTypes();
        foreach ($available_types as $object_type) {

            $setting = new ilOrgUnitObjectTypePositionSetting($object_type);
            $is_multi = false;

            if ($this->object_definition->isPlugin($object_type)) {
                $label = ilObjectPlugin::lookupTxtById($object_type, 'objs_' . $object_type);
            } else {
                $is_multi = !$this->object_definition->isSystemObject($object_type)
                    && $object_type != ilOrgUnitOperationContext::CONTEXT_ETAL;
                $lang_prefix = $is_multi ? 'objs_' : 'obj_';
                $label = $this->lng->txt($lang_prefix . $object_type);
            }

            $changeable = [];
            if ($is_multi) {
                $changeable[] = $this->ui_factory->input()->field()->switchableGroup(
                    [
                        $this->ui_factory->input()->field()->group(
                            [
                                $this->ui_factory->input()->field()->checkbox(
                                    $this->lng->txt('orgu_global_set_type_default'),
                                    $this->lng->txt('orgu_global_set_type_default_info'),
                                )
                                ->withValue((bool) $setting->getActivationDefault())
                            ],
                            $this->lng->txt('orgu_global_set_type_changeable_object'),
                        )
                        ,
                        $this->ui_factory->input()->field()->group(
                            [

                            ],
                            $this->lng->txt('orgu_global_set_type_changeable_no'),
                        )
                    ],
                    $this->lng->txt('orgu_global_set_type_changeable')
                )
                ->withValue(
                    $setting->isChangeableForObject() ? 0 : 1
                )
                ->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(
                        function ($v) {
                            $active = true;
                            $changeable = !(bool) array_shift($v);
                            $default = false;
                            if ($changeable) {
                                $default = (bool) current(array_shift($v));
                            }
                            return [$active, $changeable, $default];
                        }
                    )
                );
            } else {
                $changeable[] = $this->ui_factory->input()->field()->hidden()->withValue('true')
                    ->withAdditionalTransformation(
                        $this->refinery->custom()->transformation(
                            fn($v) => [true, false, false]
                        )
                    );
            }

            $groups[$object_type] = $this->ui_factory->input()->field()->optionalGroup(
                $changeable,
                $this->lng->txt('orgu_global_set_positions_type_active') . ' ' . $label
            );

            if (!$setting->isActive()) {
                $groups[$object_type] = $groups[$object_type]->withValue(null);
            }
        }

        $sections[] = $this->ui_factory->input()->field()->section(
            $groups,
            $this->lng->txt("orgu_global_set_positions")
        );

        $form_action = $this->ctrl->getFormAction($this, self::CMD_SAVE);
        return $this->ui_factory->input()->container()->form()->standard(
            $form_action,
            $sections
        );
    }

}
