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
use ILIAS\GlobalScreen\Services;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Refinery\Factory;
use ILIAS\components\WOPI\Embed\EmbeddedApplication;
use ILIAS\components\WOPI\Embed\Renderer;
use ILIAS\components\WOPI\Embed\EmbeddedApplicationGSProvider;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilWOPIEmbeddedApplicationGUI
{
    public const CMD_EDIT = 'edit';
    public const CMD_VIEW = 'view';
    public const CMD_RETURN = 'return';
    public const P_RETURN_TO = 'return_to';
    /**
     * @readonly
     */
    private ilGlobalTemplateInterface $main_tpl;
    /**
     * @readonly
     */
    private ilTabsGUI $tabs;
    /**
     * @readonly
     */
    private Services $global_screen;
    /**
     * @readonly
     */
    private Renderer $renderer;
    /**
     * @readonly
     */
    private \ILIAS\UI\Renderer $ui_renderer;
    /**
     * @readonly
     */
    private ArrayBasedRequestWrapper $http;
    /**
     * @readonly
     */
    private Factory $refinery;
    /**
     * @readonly
     */
    private ilCtrlInterface $ctrl;
    /**
     * @readonly
     */
    private ilLanguage $lng;

    public function __construct(
        private EmbeddedApplication $application,
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->global_screen = $DIC->globalScreen();
        $this->global_screen->layout()->meta()->addJs('./assets/js/wopi.min.js');
        $this->global_screen->layout()->meta()->addOnloadCode('il.WOPI.init();');
        $this->renderer = new Renderer($this->application);
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->http = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('wopi');
    }

    public function executeCommand(): void
    {
        if (!$this->application->isInline()) {
            $this->tabs->clearTargets();
        }
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            EmbeddedApplicationGSProvider::EMBEDDED_APPLICATION,
            $this->application
        );
        $a_value = bin2hex((string) $this->application->getBackTarget());
        $this->ctrl->setParameter($this, self::P_RETURN_TO, $a_value);

        match ($this->ctrl->getCmd()) {
            default => $this->edit(),
            self::CMD_EDIT => $this->edit(),
            self::CMD_VIEW => $this->view(),
            self::CMD_RETURN => $this->return(),
        };
    }

    private function view(): void
    {
        $this->main_tpl->setContent(
            $this->ui_renderer->render($this->renderer->getComponent())
        );
    }

    private function edit(): void
    {
        $this->main_tpl->setContent(
            $this->ui_renderer->render($this->renderer->getComponent())
        );
    }

    private function return(): void
    {
        $return_to = $this->http->has(self::P_RETURN_TO)
            ? hex2bin((string) $this->http->retrieve(self::P_RETURN_TO, $this->refinery->kindlyTo()->string()))
            : null;

        if ($return_to === null) {
            $return_to = (string) $this->application->getBackTarget();
        }

        $this->main_tpl->setOnScreenMessage(
            'info',
            $this->lng->txt('close_wopi_editor_info'),
            true
        );

        $this->ctrl->redirectToURL($return_to);
    }
}
