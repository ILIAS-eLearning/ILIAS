<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

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

        $factory = new ilSettingsFactory($DIC->database());
        $this->repository = new ilMathJaxConfigSettingsRepository($factory);
    }

    /**
     * Execute a command
     * This should be overridden in the child classes
     * note: permissions are already checked in the object gui
     */
    public function executeCommand() : void
    {
        $this->lng->loadLanguageModule('mathjax');

        $cmd = $this->ctrl->getCmd('editSettings');
        switch ($cmd) {
            case 'editSettings':
            case 'clearCache':
                $this->$cmd();
                break;

            default:
                $this->tpl->setContent('unknown command: ' . $cmd);
        }
    }

    /**
     * Edit the MathJax settings
     */
    protected function editSettings() : void
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

            'client_polyfill_url' => $factory->url(
                $this->lng->txt('mathjax_polyfill_url'),
                implode('<br />', [
                    $this->lng->txt('mathjax_polyfill_url_desc_line1'),
                    $this->lng->txt('mathjax_polyfill_url_desc_line2')
                ])
            )
                                             ->withValue($config->getClintPolyfillUrl()),

            'client_script_url' => $factory->url(
                $this->lng->txt('mathjax_script_url'),
                implode('<br />', [
                    $this->lng->txt('mathjax_script_url_desc_line1'),
                    $this->lng->txt('mathjax_script_url_desc_line2')
                ])
            )->withRequired(true) // mantis #31645
                                           ->withValue($config->getClientScriptUrl()),

            'client_limiter' => $factory->select(
                $this->lng->txt('mathjax_limiter'),
                $config->getClientLimiterOptions(),
                $this->lng->txt('mathjax_limiter_info')
            )->withRequired(true)
                                        ->withValue($config->getClientLimiter()),

            'client_test' => $factory->text(
                $this->lng->txt('mathjax_test_expression'),
                $this->lng->txt('mathjax_test_expression_info_client')
                . ilMathJax::getIndependent(
                    $config->withClientEnabled(true)
                           ->withServerEnabled(false),
                    new ilMathJaxFactory()
                )
                           ->init(ilMathJax::PURPOSE_BROWSER)
                           ->insertLatexImages('<p>[tex]' . $testcode . '[/tex]</p>')
            )->withDisabled(true)->withValue($testcode)

        ],
            $this->lng->txt('mathjax_enable_client'),
            $this->lng->txt('mathjax_enable_client_info') . ' ' .
            $this->renderLink('mathjax_home_link', 'https://www.mathjax.org')
        )->withAdditionalTransformation($checkbox_transformation);

        // server-side rendering settings
        $server_enabled = $factory->optionalGroup(
            [
            'server_address' => $factory->url(
                $this->lng->txt('mathjax_server_address'),
                $this->lng->txt('mathjax_server_address_info')
            )->withRequired(true)
                                        ->withValue($config->getServerAddress()),

            'server_timeout' => $factory->numeric(
                $this->lng->txt('mathjax_server_timeout'),
                $this->lng->txt('mathjax_server_timeout_info')
            )//->withRequired(true) // mantis #31645
                                        ->withValue($config->getServerTimeout()),

            'server_for_browser' => $factory->checkbox(
                $this->lng->txt('mathjax_server_for_browser'),
                $this->lng->txt('mathjax_server_for_browser_info')
            )->withValue($config->isServerForBrowser()),

            'server_for_export' => $factory->checkbox(
                $this->lng->txt('mathjax_server_for_export'),
                $this->lng->txt('mathjax_server_for_export_info')
            )->withValue($config->isServerForExport()),

            'server_for_pdf' => $factory->checkbox(
                $this->lng->txt('mathjax_server_for_pdf'),
                $this->lng->txt('mathjax_server_for_pdf_info')
            )->withValue($config->isServerForPdf()),

            'cache_size' => $factory->text(
                $this->lng->txt('mathjax_server_cache_size'),
                $this->lng->txt('mathjax_server_cache_size_info') . ' ' .
                $this->renderLink('mathjax_server_clear_cache', $this->ctrl->getLinkTarget($this, 'clearCache'), false)
            )->withDisabled(true)->withValue(ilMathJax::getInstance()->getCacheSize()),

            'server_test' => $factory->text(
                $this->lng->txt('mathjax_test_expression'),
                $this->lng->txt('mathjax_test_expression_info_server')
                . ilMathJax::getIndependent(
                    $config->withClientEnabled(false)
                           ->withServerEnabled(true)
                           ->withServerForBrowser(true),
                    new ilMathJaxFactory()
                )
                           ->init(ilMathJax::PURPOSE_BROWSER)
                           ->insertLatexImages('<p>[tex]' . $testcode . '[/tex]</p>')
            )->withDisabled(true)->withValue($testcode)
        ],
            $this->lng->txt('mathjax_enable_server'),
            $this->lng->txt('mathjax_enable_server_info') . ' ' .
            $this->renderLink('mathjax_server_installation', './Services/MathJax/docs/install-server.md')
        )->withAdditionalTransformation($checkbox_transformation);


        // build the settings form
        // uncheck optional groups, if not enabled, see https://mantis.ilias.de/view.php?id=26476
        $form = $this->dic->ui()->factory()->input()->container()->form()->standard($this->ctrl->getFormAction($this), [
            'mathjax' => $factory->section([], $this->lng->txt('mathjax_settings')),
            'client_enabled' => $config->isClientEnabled() ? $client_enabled : $client_enabled->withValue(null),
            'server_enabled' => $config->isServerEnabled() ? $server_enabled : $server_enabled->withValue(null)
        ]);

        // apply posted inputs if form is saved
        if ($this->request->getMethod() === "POST") {
            $form = $form->withRequest($this->request);
            $data = $form->getData();
        }

        // posted inputs exist and are ok => save data
        if (isset($data)) {
            if (is_array($data['client_enabled'])) {
                $client_data = $data['client_enabled'];
                $config = $config->withClientEnabled(true)
                                 ->withClientPolyfillUrl((string) $client_data['client_polyfill_url'])
                                 ->withClientScriptUrl((string) $client_data['client_script_url'])
                                 ->withClientLimiter((int) $client_data['client_limiter']);
            } else {
                $config = $config->withClientEnabled(false);
            }

            if (is_array($data['server_enabled'])) {
                $server_data = $data['server_enabled'];
                $config = $config->withServerEnabled(true)
                                 ->withServerAddress((string) $server_data['server_address'])
                                 ->withServerTimeout((int) $server_data['server_timeout'])
                                 ->withServerForBrowser((bool) $server_data['server_for_browser'])
                                 ->withServerForExport((bool) $server_data['server_for_export'])
                                 ->withServerForPdf((bool) $server_data['server_for_pdf']);
            } else {
                $config = $config->withServerEnabled(false);
            }
            $this->repository->updateConfig($config);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this);
        }

        // form is not posted or has valisation errors
        $this->tpl->setContent($this->dic->ui()->renderer()->render($form));
    }

    /**
     * Clear the directory with cached LaTeX graphics
     */
    protected function clearCache() : void
    {
        ilMathJax::getInstance()->clearCache();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('mathjax_server_cache_cleared'), true);
        $this->ctrl->redirect($this);
    }

    /**
     * Render an html link
     */
    protected function renderLink(string $langvar, string $url, bool $new_tab = true) : string
    {
        $link = $this->dic->ui()->factory()->link()->standard(
            $this->lng->txt($langvar),
            $url
        )->withOpenInNewViewport($new_tab);
        return $this->dic->ui()->renderer()->render($link);
    }
}
