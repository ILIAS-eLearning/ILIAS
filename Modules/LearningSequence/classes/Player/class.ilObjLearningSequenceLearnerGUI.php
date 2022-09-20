<?php

declare(strict_types=1);

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

use ILIAS\HTTP\Wrapper\RequestWrapper;

class ilObjLearningSequenceLearnerGUI
{
    public const CMD_STANDARD = 'learnerView';
    public const CMD_EXTRO = 'learnerViewFinished';
    public const CMD_UNSUBSCRIBE = 'unsubscribe';
    public const CMD_VIEW = 'view';
    public const CMD_START = 'start';
    public const PARAM_LSO_NEXT_ITEM = 'lsoni';
    public const LSO_CMD_NEXT = 'lson';
    public const LSO_CMD_PREV = 'lsop';

    protected ilAccess $access;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ILIAS\UI\Factory $ui_factory;
    protected ILIAS\UI\Renderer $renderer;
    protected ilLearningSequenceRoles $roles;
    protected ilLearningSequenceSettings $settings;
    protected ilLSCurriculumBuilder $curriculum_builder;
    protected ilLSLaunchlinksBuilder $launchlinks_builder;
    protected ilLSPlayer $player;
    protected string $intro;
    protected string $extro;
    protected RequestWrapper $get;



    public function __construct(
        int $usr_id,
        ilAccess $access,
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilGlobalTemplateInterface $tpl,
        ilToolbarGUI $toolbar,
        ILIAS\UI\Factory $ui_factory,
        ILIAS\UI\Renderer $ui_renderer,
        ilLearningSequenceRoles $roles,
        ilLearningSequenceSettings $settings,
        ilLSCurriculumBuilder $curriculum_builder,
        ilLSLaunchlinksBuilder $launchlinks_builder,
        ilLSPlayer $player,
        string $intro,
        string $extro,
        RequestWrapper $get
    ) {
        $this->usr_id = $usr_id;
        $this->access = $access;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->ui_factory = $ui_factory;
        $this->renderer = $ui_renderer;
        $this->roles = $roles;
        $this->settings = $settings;
        $this->curriculum_builder = $curriculum_builder;
        $this->launchlinks_builder = $launchlinks_builder;
        $this->player = $player;
        $this->intro = $intro;
        $this->extro = $extro;
        $this->get = $get;
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_STANDARD:
            case self::CMD_EXTRO:
                $this->view($cmd);
                break;
            case self::CMD_START:
                $this->addMember($this->usr_id);
                $this->ctrl->redirect($this, self::CMD_VIEW);
                break;
            case self::CMD_UNSUBSCRIBE:
                if ($this->launchlinks_builder->currentUserMayUnparticipate()) {
                    $this->roles->leave($this->usr_id);
                }
                $this->ctrl->redirect($this, self::CMD_STANDARD);
                break;
            case self::CMD_VIEW:
                $this->play();
                break;

            default:
                throw new ilException(
                    "ilObjLearningSequenceLearnerGUI: " .
                    "Command not supported: $cmd"
                );
        }
    }

    protected function view(string $cmd): void
    {
        $content = $this->getWrappedHTML(
            $this->getMainContent($cmd)
        );

        $this->tpl->setContent($content);

        $element = '<' . ilPCLauncher::PCELEMENT . '>';
        if (!str_contains($content, $element)) {
            $this->initToolbar($cmd);
        }

        $element = '<' . ilPCCurriculum::PCELEMENT . '>';
        if (!str_contains($content, $element)) {
            $curriculum = $this->curriculum_builder->getLearnerCurriculum();
            $this->tpl->setRightContent(
                $this->getWrappedHTML([$curriculum])
            );
        }
    }

    protected function addMember(int $usr_id): void
    {
        $admins = $this->roles->getLearningSequenceAdminIds();
        if (!in_array($usr_id, $admins)) {
            $this->roles->join($usr_id);
        }
    }

    protected function initToolbar(string $cmd)
    {
        foreach ($this->launchlinks_builder->getLinks() as $entry) {
            list($label, $link) = $entry;
            $this->toolbar->addButton(
                $label,
                $link
            );
        }
    }

    private function getWrappedHTML(array $components): string
    {
        array_unshift(
            $components,
            $this->ui_factory->legacy('<div class="ilLSOLearnerView">')
        );
        $components[] = $this->ui_factory->legacy('</div>');

        return $this->renderer->render($components);
    }

    private function getMainContent(string $cmd): array
    {
        $img = null;
        $contents = [];

        if ($cmd === self::CMD_STANDARD) {
            if ($this->intro === '') {
                $contents[] = $this->ui_factory->legacy($this->settings->getAbstract());
                $img = $this->settings->getAbstractImage();
                if ($img) {
                    $contents[] = $this->ui_factory->image()->responsive($img, '');
                }
            } else {
                $contents[] = $this->ui_factory->legacy($this->intro);
            }
        }

        if ($cmd === self::CMD_EXTRO) {
            if ($this->extro === '') {
                $contents[] = $this->ui_factory->legacy($this->settings->getExtro());
                $img = $this->settings->getExtroImage();
                if ($img) {
                    $contents[] = $this->ui_factory->image()->responsive($img, '');
                }
            } else {
                $contents[] = $this->ui_factory->legacy($this->intro);
            }
        }
        return $contents;
    }

    protected function play(): void
    {
        $response = $this->player->play($this->get);

        switch ($response) {
            case null:
                //render the page
                $this->tpl->setFileUploadRefId(0);
                $this->tpl->setContent('THIS SHOULD NOT SHOW');
                return;

            case 'EXIT::' . $this->player::LSO_CMD_FINISH:
                $cmd = self::CMD_EXTRO;
                break;

            case 'EXIT::' . $this->player::LSO_CMD_SUSPEND:
            default:
                $cmd = self::CMD_STANDARD;
                break;
        }
        $href = $this->ctrl->getLinkTarget($this, $cmd, '', false, false);
        \ilUtil::redirect($href);
    }
}
