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

use ILIAS\components\WOPI\Launcher\LauncherRequest;
use ILIAS\components\WOPI\Embed\EmbeddedApplication;
use ILIAS\components\WOPI\Embed\Renderer;
use ILIAS\components\WOPI\Embed\EmbeddedApplicationGSProvider;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilWOPIEmbeddedApplicationGUI
{
    public const CMD_INDEX = 'index';
    private ilGlobalTemplateInterface $main_tpl;
    private ilTabsGUI $tabs;
    private \ILIAS\GlobalScreen\Services $global_screen;
    private Renderer $renderer;
    private \ILIAS\UI\Renderer $ui_renderer;

    public function __construct(
        private EmbeddedApplication $application,
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->global_screen = $DIC->globalScreen();
        $this->global_screen->layout()->meta()->addJs('./components/ILIAS/WOPI/js/dist/index.min.js');
        $this->global_screen->layout()->meta()->addOnloadCode('il.WOPI.init();');
        $this->renderer = new Renderer($this->application);
        $this->ui_renderer = $DIC->ui()->renderer();
    }

    public function executeCommand(): void
    {
        $this->tabs->clearTargets();
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            EmbeddedApplicationGSProvider::EMBEDDED_APPLICATION,
            $this->application
        );
        $this->index();
    }

    private function index(): void
    {
        $this->main_tpl->setContent($this->ui_renderer->render($this->renderer->getComponent()));
    }
}
