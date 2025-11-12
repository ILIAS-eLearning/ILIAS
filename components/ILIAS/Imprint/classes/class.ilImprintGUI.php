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

use ILIAS\Imprint\StandardGUIRequest;

/**
 * @ilCtrl_Calls ilImprintGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilImprintGUI: ILIAS\User\Profile\PublicProfileGUI, ilPageObjectGUI
 */
class ilImprintGUI extends ilPageObjectGUI implements ilCtrlBaseClassInterface
{
    private StandardGUIRequest $imprint_request;
    private \ILIAS\Http\GlobalHttpState $http;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->http = $DIC->http();

        $this->imprint_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        if (!ilImprint::_exists('impr', 1)) {
            $page = new ilImprint();
            $page->setId(1);
            $page->create(false);
        }

        // there is only 1 imprint page
        parent::__construct('impr', 1);

        // content style (using system defaults)
        $this->tpl->setCurrentBlock('SyntaxStyle');
        $this->tpl->setVariable(
            'LOCATION_SYNTAX_STYLESHEET',
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock('ContentStyle');
        $this->tpl->setVariable(
            'LOCATION_CONTENT_STYLESHEET',
            ilObjStyleSheet::getContentStylePath(0)
        );
        $this->tpl->parseCurrentBlock();
    }

    public function executeCommand(): string
    {
        if (strtolower($this->imprint_request->getBaseClass()) === strtolower(__CLASS__)) {
            $this->renderFullscreen();
        }

        $next_class = $this->ctrl->getNextClass($this);

        $title = $this->lng->txt('adm_imprint');

        switch ($next_class) {
            default:
                $this->setPresentationTitle($title);
                $ret = parent::executeCommand();
                $this->tabs_gui->activateTab('pg');
                return $ret;
        }
    }

    public function postOutputProcessing(string $a_output): string
    {
        $lng = $this->lng;

        if ($this->getOutputMode() === ilPageObjectGUI::PREVIEW && !$this->getPageObject()->getActive()) {
            $this->tpl->setOnScreenMessage('info', $lng->txt('adm_imprint_inactive'));
        }

        return $a_output;
    }

    private function renderFullscreen(): never
    {
        if (!ilImprint::isActive()) {
            $this->ctrl->redirectToURL('ilias.php?baseClass=ilDashboardGUI');
        }

        $this->tpl->setTitle($this->lng->txt('imprint'));
        $this->tpl->loadStandardTemplate();

        $this->setRawPageContent(true);
        $this->tpl->setContent($this->showPage());

        $this->tpl->printToStdout('DEFAULT', true, false);

        $this->http->close();
    }

    public function showEditToolbar(): void
    {
        $ui = $this->ui;
        $lng = $this->lng;
        if ($this->getEnableEditing()) {
            $b = $ui->factory()->button()->standard(
                $lng->txt('edit_page'),
                $this->ctrl->getLinkTargetByClass([ilObjLegalNoticeGUI::class, __CLASS__], 'edit')
            );
            $this->toolbar->addComponent($b);
        }
    }

    public function getTabs(string $a_activate = ''): void
    {
        if ($this->getOutputMode() === self::PRESENTATION) {
            $this->tabs_gui->activateTab('view');
        }
    }

    public function preview(): string
    {
        $this->setOutputMode(self::PREVIEW);
        return $this->showPage();
    }
}
