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

use ILIAS\HTTP\Services as HttpServices;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Factory;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Renderer;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Language\Language;
use Psr\Http\Message\ServerRequestInterface;

class ilSystemStyleOverviewGUI
{
    protected ilCtrl $ctrl;
    protected ilToolbarGUI $toolbar;
    protected Language $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilSkinFactory $skin_factory;
    protected ilSkinStyleContainer $style_container;
    protected ilSystemStyleMessageStack $message_stack;
    protected Factory $ui_factory;
    protected Renderer $renderer;
    protected WrapperFactory $request_wrapper;
    protected Refinery $refinery;
    protected ilSystemStyleConfig $config;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected FileUpload $upload;
    protected string $ref_id;
    protected string $style_id;

    public function __construct(
        ilCtrl $ctrl,
        Language $lng,
        ilGlobalTemplateInterface $tpl,
        Factory $ui_factory,
        Renderer $renderer,
        WrapperFactory $request_wrapper,
        ilToolbarGUI $toolbar,
        Refinery $refinery,
        ilSkinFactory $skin_factory,
        FileUpload $upload,
        ilTabsGUI $tabs,
        ilHelpGUI $help,
        private readonly ServerRequestInterface $request,
        private readonly HttpServices $http,
        string $skin_id,
        string $style_id,
        string $ref_id,
        private readonly bool $read_only = true,
        private readonly bool $management_enabled = false
    ) {
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->ui_factory = $ui_factory;
        $this->renderer = $renderer;
        $this->request_wrapper = $request_wrapper;
        $this->toolbar = $toolbar;
        $this->refinery = $refinery;
        $this->tabs = $tabs;
        $this->style_id = $style_id;
        $this->message_stack = new ilSystemStyleMessageStack($this->tpl);
        $this->skin_factory = $skin_factory;
        $this->style_container = $this->skin_factory->skinStyleContainerFromId($skin_id, $this->message_stack);
        $this->help = $help;
        $this->ref_id = $ref_id;
        $this->upload = $upload;
        $this->config = new ilSystemStyleConfig();
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd() ?? '';

        if ($cmd === '') {
            $cmd = $this->read_only ? 'view' : 'edit';
        }

        switch ($cmd) {
            case 'cancel':
            case 'edit':
                $this->edit();
                break;
            case 'moveUserStyles':
                $this->moveUserStyles();
                break;
            case 'view':
                $this->view();
                break;
        }

        $this->message_stack->sendMessages();
    }

    protected function view(): void
    {
        $this->showOverview(false);
    }

    public function getAssignmentCreationModal(string $style_name = ''): ?RoundTrip
    {
        $options = [];
        foreach (ilStyleDefinition::getAllSkinStyles() as $id => $skin_style) {
            if (!$skin_style['substyle_of'] && $style_name != $skin_style['style_name']) {
                $options[$id] = $skin_style['title'];
            }
        }

        $default = 'default:delos';
        if ($style_name == 'Delos') {
            $default = key($options);
        }

        if (count($options) == 0) {
            return null;
        }

        $txt = $this->lng->txt('sty_move_user_styles') . ' ' . $this->lng->txt('sty_to');

        $byline = $this->lng->txt('sty_move_user_styles') . ' ' .
            $this->lng->txt('sty_from') . ' ' . $style_name;

        $select = $this->ui_factory->input()->field()
                                            ->select($txt, $options, $byline)
                                            ->withValue($default)
                                            ->withAdditionalTransformation($this->refinery->string()->splitString(':'))
                                            ->withRequired(true);

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('change_assignment'),
            [],
            ['new_style' => $select],
            $this->ctrl->getLinkTargetByClass(ilSystemStyleOverviewGUI::class, 'moveUserStyles')
        );
    }

    public function edit(): void
    {
        if ($this->read_only) {
            throw new ilObjectException($this->lng->txt('permission_denied'));
        }

        $this->showOverview($this->management_enabled);
    }

    private function showOverview(bool $actions_enabled): void
    {
        $table = new SystemStylesTable(
            $this->ui_factory,
            $this->ui_factory->table()->column(),
            $this->ui_factory->table()->action(),
            $this->lng,
            $this->request,
            $this->renderer,
            $this->refinery,
            $this->skin_factory,
            $this->ctrl,
            $this->message_stack,
            $this,
            $this->http,
        );
        $table->performAction($this->request_wrapper->query(), $actions_enabled);

        $this->tpl->setContent($this->renderer->render($table->build($actions_enabled)));
    }

    public function moveUserStyles(): void
    {
        if ($this->read_only) {
            throw new ilObjectException($this->lng->txt('permission_denied'));
        }

        $modal = $this->getAssignmentCreationModal()->withRequest($this->request);
        [$new_skin, $new_style] = $modal->getData()['new_style'];

        $old_skin = $this->request_wrapper->query()->retrieve('old_skin_id', $this->refinery->kindlyTo()->string());
        $old_style = $this->request_wrapper->query()->retrieve('old_style_id', $this->refinery->kindlyTo()->string());

        if ($old_style == 'other') {
            // get all user assigned styles
            $all_user_styles = ilObjUser::_getAllUserAssignedStyles();

            // move users that are not assigned to currently existing style
            foreach ($all_user_styles as $style) {
                if (!ilStyleDefinition::styleExists($style)) {
                    [$old_skin, $old_style] = explode(':', $style);
                    ilObjUser::_moveUsersToStyle($old_skin, $old_style, $new_skin, $new_style);
                }
            }
        } else {
            ilObjUser::_moveUsersToStyle($old_skin, $old_style, $new_skin, $new_style);
        }
        $message = sprintf($this->lng->txt('sty_move_user_styles_saved'), $old_skin, $new_skin);

        $this->message_stack->addMessage(new ilSystemStyleMessage($message, ilSystemStyleMessage::TYPE_SUCCESS));
        $this->message_stack->sendMessages();
        $this->ctrl->redirect($this, 'edit');
    }
}
