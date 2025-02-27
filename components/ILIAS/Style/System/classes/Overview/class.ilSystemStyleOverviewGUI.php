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

use ILIAS\UI\Factory;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Renderer;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Language\Language;

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
    protected bool $read_only = true;
    protected bool $management_enabled = false;

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
        string $skin_id,
        string $style_id,
        string $ref_id,
        bool $read_only,
        bool $management_enabled
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
        $this->setReadOnly($read_only);
        $this->setManagementEnabled($management_enabled);
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        if ($cmd == '') {
            $cmd = $this->isReadOnly() ? 'view' : 'edit';
        }

        switch ($cmd) {
            case 'addSystemStyle':
            case 'addSubStyle':
            case 'saveNewSystemStyle':
            case 'saveNewSubStyle':
            case 'copyStyle':
            case 'importStyle':
            case 'deleteStyles':
            case 'deleteStyle':
            case 'confirmDelete':
                if (!$this->isManagementEnabled()) {
                    throw new ilObjectException($this->lng->txt('permission_denied'));
                }
                $this->$cmd();
                break;
            case 'cancel':
            case 'edit':
            case 'export':
            case 'moveUserStyles':
            case 'saveStyleSettings':
                if ($this->isReadOnly()) {
                    throw new ilObjectException($this->lng->txt('permission_denied'));
                }
                $this->$cmd();
                break;
            case 'view':
                $this->$cmd();
                break;
        }
        $this->message_stack->sendMessages();
    }

    protected function view(): void
    {
        $table = new ilSystemStylesTableGUI($this, 'edit');
        $this->tpl->setContent($table->getHTML().$table->getModalsHtml());
    }

    public function getAssignmentCreationModal(string $style_name = ""): ?\ILIAS\UI\Component\Modal\RoundTrip
    {
        $options = [];
        foreach (ilStyleDefinition::getAllSkinStyles() as $id => $skin_style) {
            if (!$skin_style['substyle_of'] && $style_name != $skin_style['style_name']) {
                $options[$id] = $skin_style['title'];
            }
        }

        $default = "default:delos";
        if ($style_name == "Delos") {
            $default = key($options);
        }

        if (count($options) == 0) {
            return null;
        }

        $txt = $this->lng->txt('sty_move_user_styles').' '.$this->lng->txt('sty_to');

        $byline = $this->lng->txt('sty_move_user_styles') . ' ' .
            $this->lng->txt('sty_from')  . ' ' . $style_name;

        $select = $this->ui_factory->input()->field()
                                            ->select($txt, $options, $byline)
                                            ->withValue($default)
                                            ->withAdditionalTransformation($this->refinery->string()->splitString(':'))
                                            ->withRequired(true);

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('change_assignment'),
            [],
            ["new_style" => $select],
            $this->ctrl->getLinkTargetByClass(ilSystemStyleOverviewGUI::class, 'moveUserStyles')
        );
    }

    protected function cancel(): void
    {
        $this->edit();
    }

    public function edit(): void
    {
        $table = new ilSystemStylesTableGUI($this, 'edit');
        $table->addActions($this->isManagementEnabled());
        $this->tpl->setContent($table->getHTML().$table->getModalsHtml());
    }

    public function saveStyleSettings(): void
    {
        $active_styles = $this->request_wrapper->post()->retrieve('st_act', $this->refinery->identity());

        if ($this->checkStyleSettings($this->message_stack, $active_styles)) {
            $all_styles = ilStyleDefinition::getAllSkinStyles();
            foreach ($all_styles as $style) {
                if (!isset($active_styles[$style['id']])) {
                    ilSystemStyleSettings::_deactivateStyle($style['template_id'], $style['style_id']);
                } else {
                    ilSystemStyleSettings::_activateStyle($style['template_id'], $style['style_id']);
                }
            }

            //set default skin and style
            if ($this->request_wrapper->post()->has('default_skin_style')) {
                $sknst = $this->request_wrapper->post()->retrieve(
                    'default_skin_style',
                    $this->refinery->string()->splitString(':')
                );
                ilSystemStyleSettings::setCurrentDefaultStyle($sknst[0], $sknst[1]);
            }
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('msg_obj_modified'),
                ilSystemStyleMessage::TYPE_SUCCESS
            ));
        }
        $this->message_stack->sendMessages();
        $this->ctrl->redirect($this, 'edit');
    }

    public function moveUserStyles(): void
    {
        global $DIC;

        $request = $DIC->http()->request();

        $modal = $this->getAssignmentCreationModal()->withRequest($request);
        [$new_skin, $new_style] = $modal->getData()["new_style"];

        $old_skin = $this->request_wrapper->query()->retrieve('old_skin_id', $this->refinery->kindlyTo()->string());
        $old_style = $this->request_wrapper->query()->retrieve('old_style_id', $this->refinery->kindlyTo()->string());



        if ($old_style == 'other') {
            // get all user assigned styles
            $all_user_styles = ilObjUser::_getAllUserAssignedStyles();

            // move users that are not assigned to
            // currently existing style
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

    protected function checkStyleSettings(ilSystemStyleMessageStack $message_stack, ?array $active_styles): bool
    {
        $passed = true;

        if (!$active_styles || count($active_styles) < 1) {
            $passed = false;
            $message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('at_least_one_style'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }

        if ($this->request_wrapper->post()->has('default_skin_style')) {
            $default_style = $this->request_wrapper->post()->retrieve(
                'default_skin_style',
                $this->refinery->kindlyTo()->string()
            );
        } else {
            $default_style = $this->config->getDefaultStyleId();
        }



        if (!isset($active_styles[$default_style])) {
            $passed = false;
            $message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('cant_deactivate_default_style'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }

        // check if a style should be deactivated, that still has
        // a user assigned to
        $all_styles = ilStyleDefinition::getAllSkinStyles();

        foreach ($all_styles as $style) {
            if (!isset($active_styles[$style['id']])) {
                if (ilObjUser::_getNumberOfUsersForStyle($style['template_id'], $style['style_id']) > 0) {
                    $passed = false;
                    $message_stack->addMessage(new ilSystemStyleMessage(
                        $style['style_name'] . ': ' . $this->lng->txt('cant_deactivate_if_users_assigned'),
                        ilSystemStyleMessage::TYPE_ERROR
                    ));
                }
            }
        }
        return $passed;
    }


    protected function export(): void
    {
        try {
            $this->style_container->export();
        } catch (Exception $e) {
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('zip_export_failed') . ' ' . $e->getMessage(),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }
    }


    public function isReadOnly(): bool
    {
        return $this->read_only;
    }

    public function setReadOnly(bool $read_only): void
    {
        $this->read_only = $read_only;
    }

    public function isManagementEnabled(): bool
    {
        return $this->management_enabled;
    }

    public function setManagementEnabled(bool $management_enabled): void
    {
        $this->management_enabled = $management_enabled;
    }
}
