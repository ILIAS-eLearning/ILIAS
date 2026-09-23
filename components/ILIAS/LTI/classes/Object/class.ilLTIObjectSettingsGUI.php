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

use ILIAS\ILIASObject\Properties\CoreProperties\Online;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;

/**
 * Settings of an LTI object: what it is called, whether it is online, how it launches its tool and
 * the credentials it launches with. The tool itself is not changed here, only how this object uses it.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIObjectSettingsGUI
{
    public const string CMD_SHOW = 'showSettings';
    public const string CMD_SAVE = 'saveSettings';

    private const string SUBTAB_OBJECT = 'subtab_object_settings';
    private const string SUBTAB_TOOL = 'subtab_provider_settings';

    private readonly ILIAS\DI\Container $dic;

    public function __construct(private readonly ilObjLTIConsumer $object)
    {
        global $DIC;

        $this->dic = $DIC;
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $this->initSubTabs();

        match ($this->dic->ctrl()->getCmd(self::CMD_SHOW)) {
            self::CMD_SAVE => $this->save(),
            default => $this->show(),
        };
    }

    /**
     * @throws ilCtrlException
     */
    private function show(?Form $form = null): void
    {
        $this->dic->tabs()->activateSubTab(self::SUBTAB_OBJECT);
        $this->dic->ui()->mainTemplate()->setContent(
            $this->dic->ui()->renderer()->render($form ?? $this->buildForm())
        );
    }

    /**
     * @throws ilCtrlException
     */
    private function save(): void
    {
        $form = $this->buildForm()->withRequest($this->dic->http()->request());
        $data = $form->getData();
        if ($data === null) {
            $this->show($form);
            return;
        }

        $this->object->setTitle($data['general']['title']);
        $this->object->setDescription((string) $data['general']['description']);
        $this->object->setLaunchMethod($data['appearance']['launch_method']);
        $this->object->setCustomParams((string) $data['appearance']['custom_params']);

        if (isset($data['authentication'])) {
            $this->object->getLti1p1Credentials()->setKey($data['authentication']['key']);
            $this->object->getLti1p1Credentials()->setSecret($data['authentication']['secret']);
        }

        $this->object->update();
        $this->object->getObjectProperties()->storePropertyIsOnline(new Online((bool) $data['general']['online']));

        $this->dic->ui()->mainTemplate()->setOnScreenMessage('success', $this->dic->language()->txt('settings_saved'), true);
        $this->dic->ctrl()->redirect($this, self::CMD_SHOW);
    }

    /**
     * @throws ilCtrlException
     */
    private function buildForm(): Form
    {
        $lng = $this->dic->language();
        $field = $this->dic->ui()->factory()->input()->field();
        $tool = $this->object->getTool();

        $sections = [
            'general' => $field->section([
                'tool' => $field->text($lng->txt('provider_info'), $tool->getDescription())
                    ->withValue($tool->getTitle())
                    ->withDisabled(true),
                'title' => $field->text($lng->txt('title'), $lng->txt('title_info'))
                    ->withRequired(true)
                    ->withValue($this->object->getTitle()),
                'description' => $field->textarea($lng->txt('description'), $lng->txt('description_info'))
                    ->withValue($this->object->getDescription()),
                'online' => $field->checkbox($lng->txt('online'), $lng->txt('online_info'))
                    ->withValue(!$this->object->getOfflineStatus()),
            ], $lng->txt('lti_settings_form')),
        ];

        // only an LTI 1.1 tool that leaves its credentials to the object has them here
        if ($tool->getLtiVersion() === ilLTITool::VERSION_1P1 && $tool->isKeyCustomizable()) {
            $sections['authentication'] = $field->section([
                'key' => $field->text($lng->txt('lti_con_prov_key'))
                    ->withRequired(true)
                    ->withValue($this->object->getLti1p1Credentials()->getKey()),
                'secret' => $field->text($lng->txt('lti_con_prov_secret'))
                    ->withRequired(true)
                    ->withValue($this->object->getLti1p1Credentials()->getSecret()),
            ], $lng->txt('lti_con_prov_authentication'));
        }

        $launch_method = $field->radio($lng->txt('launch_method'))->withRequired(true);
        foreach ([
            ilObjLTIConsumer::LAUNCH_METHOD_OWN_WIN => 'launch_method_own_win',
            ilObjLTIConsumer::LAUNCH_METHOD_NEW_WIN => 'launch_method_new_win',
            ilObjLTIConsumer::LAUNCH_METHOD_EMBEDDED => 'launch_method_embedded',
        ] as $method => $txt) {
            $launch_method = $launch_method->withOption($method, $lng->txt($txt), $lng->txt($txt . '_info'));
        }

        $sections['appearance'] = $field->section([
            'launch_method' => $launch_method->withValue($this->object->getLaunchMethod()),
            'custom_params' => $field->textarea(
                $lng->txt('lti_con_prov_custom_params'),
                $lng->txt('lti_con_prov_custom_params_info')
            )->withValue($this->object->getCustomParams()),
        ], $lng->txt('lti_form_section_appearance'));

        return $this->dic->ui()->factory()->input()->container()->form()->standard(
            $this->dic->ctrl()->getFormAction($this, self::CMD_SAVE),
            $sections
        );
    }

    /**
     * The tool of an object only has settings of its own when it belongs to the user reading them.
     *
     * @throws ilCtrlException
     */
    private function initSubTabs(): void
    {
        $tabs = $this->dic->tabs();
        $tabs->addSubTab(
            self::SUBTAB_OBJECT,
            $this->dic->language()->txt(self::SUBTAB_OBJECT),
            $this->dic->ctrl()->getLinkTarget($this, self::CMD_SHOW)
        );

        $tool = $this->object->getTool();
        if (!$tool->isGlobal() && $tool->getCreator() === $this->dic->user()->getId()) {
            $tabs->addSubTab(
                self::SUBTAB_TOOL,
                $this->dic->language()->txt(self::SUBTAB_TOOL),
                $this->dic->ctrl()->getLinkTargetByClass(ilLTIToolSettingsGUI::class, ilLTIToolSettingsGUI::CMD_SHOW)
            );
        }
    }
}
