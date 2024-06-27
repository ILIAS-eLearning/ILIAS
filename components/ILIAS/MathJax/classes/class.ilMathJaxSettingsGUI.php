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

/**
 * GUI for MathJax Settings
 * This GUI maintains the MathJax config stored in the ILIAS settings
 * Since ILIAS 8 these settings can also be written by the ILIAS setup
 * @ilCtrl_Calls ilMathJaxSettingsGUI:
 */
class ilMathJaxSettingsGUI
{
    protected \ILIAS\DI\Container $dic;
    protected \ilCtrl $ctrl;
    protected \ilTabsGUI $tabs;
    protected \ilLanguage $lng;
    protected \ilGlobalTemplateInterface $tpl;
    protected \ilToolbarGUI $toolbar;
    protected \Psr\Http\Message\ServerRequestInterface $request;
    protected ILIAS\Refinery\Factory $refinery;
    protected ILIAS\UI\Factory $factory;
    protected ILIAS\UI\Renderer $renderer;

    protected ilMathJaxConfigRespository $repository;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        // ILIAS dependencies
        $this->dic = $DIC;
        $this->ctrl = $this->dic->ctrl();
        $this->tabs = $this->dic->tabs();
        $this->toolbar = $this->dic->toolbar();
        $this->lng = $this->dic->language();
        $this->tpl = $this->dic->ui()->mainTemplate();
        $this->request = $this->dic->http()->request();
        $this->refinery = $this->dic->refinery();
        $this->factory = $this->dic->ui()->factory();
        $this->renderer = $this->dic->ui()->renderer();

        $factory = new ilSettingsFactory($DIC->database());
        $this->repository = new ilMathJaxConfigSettingsRepository($factory->settingsFor('MathJax'));
    }

    /**
     * Execute a command
     * This should be overridden in the child classes
     * note: permissions are already checked in the object gui
     */
    public function executeCommand(): void
    {
        $this->lng->loadLanguageModule('mathjax');

        $cmd = $this->ctrl->getCmd('editSettings');
        switch ($cmd) {
            case 'editSettings':
                $this->$cmd();
                break;

            default:
                $this->tpl->setContent('unknown command: ' . $cmd);
        }
    }

    /**
     * Edit the MathJax settings
     */
    protected function editSettings(): void
    {
        $testcode = 'f(x)=\int_{-\infty}^x e^{-t^2}dt';

        $config = $this->repository->getConfig();
        $factory = $this->dic->ui()->factory()->input()->field();

        // needed for the optional groups
        $checkbox_transformation = $this->refinery->custom()->transformation(static function ($v) {
            if (is_array($v) || is_bool($v)) {
                return $v;
            }
            return ($v === 'checked');
        });

        // client-side rendering settings
        $client_enabled = $factory->optionalGroup(
            [
                'client_test' => $this->factory->input()->field()->text(
                    $this->lng->txt('mathjax_test_expression'),
                    $this->lng->txt('mathjax_test_expression_info_client')
                )->withDisabled(true)->withValue($testcode)
            ],
            $this->lng->txt('mathjax_enable_client'),
            $this->lng->txt('mathjax_enable_client_info') . ' ' .
            $this->renderLink('mathjax_home_link', 'https://www.mathjax.org')
        )->withAdditionalTransformation($checkbox_transformation);

        $components = [];

        // build the settings form
        // uncheck optional groups, if not enabled, see https://mantis.ilias.de/view.php?id=26476
        $form = $this->dic->ui()->factory()->input()->container()->form()->standard($this->ctrl->getFormAction($this), [
            'mathjax' => $factory->section([], $this->lng->txt('mathjax_settings')),
            'client_enabled' => $config->isClientEnabled() ? $client_enabled : $client_enabled->withValue(null),
        ]);

        // Testing panel
        $panel = $this->factory->panel()->standard(
            $this->lng->txt('mathjax_test_expression'),
            $this->factory->legacy('[tex]' . $testcode . '[/tex]')->withLatexEnabled(),
        );

        // apply posted inputs if form is saved
        if ($this->request->getMethod() === "POST") {
            $form = $form->withRequest($this->request);
            $data = $form->getData();
        }

        // posted inputs exist and are ok => save data
        if (isset($data)) {
            if (is_array($data['client_enabled'])) {
                $client_data = $data['client_enabled'];
                $config = $config->withClientEnabled(true);
            } else {
                $config = $config->withClientEnabled(false);
            }
            $this->repository->updateConfig($config);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this);
        }

        // form is not posted or has validation errors
        $this->tpl->setContent($this->renderer->render([$form, $panel]));
    }

    /**
     * Render an html link
     */
    protected function renderLink(string $langvar, string $url, bool $new_tab = true): string
    {
        $link = $this->dic->ui()->factory()->link()->standard(
            $this->lng->txt($langvar),
            $url
        )->withOpenInNewViewport($new_tab);
        return $this->dic->ui()->renderer()->render($link);
    }
}
