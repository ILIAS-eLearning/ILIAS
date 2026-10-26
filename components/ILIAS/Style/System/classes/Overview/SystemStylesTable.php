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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Table\Action\Factory as ActionFactory;
use ILIAS\UI\Component\Table\Column\Factory as ColumnFactory;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;

readonly class SystemStylesTable
{
    public const string TABLE_IDENTIFIER = 'system_styles';
    public const string QUERY_ACTION_KEY = self::TABLE_IDENTIFIER . '_table_action';
    private const string QUERY_ROW_IDS_KEY = self::TABLE_IDENTIFIER . '_row_ids';
    private const string ALL_OBJECTS = 'ALL_OBJECTS';
    public const string COL_STYLE_TITLE = 'style_title';
    public const string COL_SKIN_TITLE = 'skin_title';
    public const string COL_SUBSTYLE_OF = 'substyle_of';
    public const string COL_SCOPE = 'scope';
    public const string COL_DEFAULT = 'default';
    public const string COL_STATUS = 'status';
    public const string COL_USERS = 'users';
    public const string COL_VERSION = 'version';
    public const string ACTION_SET_DEFAULT = 'set_default';
    public const string ACTION_ACTIVATE = 'activate';
    public const string ACTION_DEACTIVATE = 'deactivate';
    public const string ACTION_ASSIGN_STYLE = 'assign_style';
    public const string ACTION_ASSIGN_CATEGORIES = 'assign_categories';
    public const string ACTION_EXPORT = 'export';

    public function __construct(
        private UIFactory $ui_factory,
        private ColumnFactory $column_factory,
        private ActionFactory $action_factory,
        private Language $lng,
        private ServerRequestInterface $request,
        private Renderer $renderer,
        private Refinery $refinery,
        private ilSkinFactory $skin_factory,
        private ilCtrl $ctrl,
        private ilSystemStyleMessageStack $message_stack,
        private ilSystemStyleOverviewGUI $overview,
        private GlobalHttpState $http,
    ) {
    }

    public function build(bool $is_management_enabled): Data
    {
        $this->lng->loadLanguageModule('ui');
        $users_title = $this->lng->txt('users');

        $columns = [
            self::COL_STYLE_TITLE => $this->column_factory->text($this->lng->txt('style_name'))->withIsSortable(true),
            self::COL_SKIN_TITLE => $this->column_factory->text($this->lng->txt('skin_name'))->withIsSortable(true),
            self::COL_SUBSTYLE_OF => $this->column_factory->text($this->lng->txt('sty_substyle_of'))->withIsSortable(false),
            self::COL_SCOPE => $this->column_factory->text($this->lng->txt('scope'))->withIsSortable(false),
            self::COL_DEFAULT => $this->column_factory->boolean(
                $this->lng->txt('default'),
                $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_ok.svg', $this->lng->txt('yes'), Icon::SMALL),
                $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_not_ok.svg', $this->lng->txt('no'), Icon::SMALL),
            )->withIsSortable(false),
            self::COL_STATUS => $this->column_factory->text($this->lng->txt('status'))->withIsSortable(false),
            self::COL_USERS => $this->column_factory->text($users_title)
                ->withIsSortable(true)
                ->withOrderingLabels(
                    "$users_title, {$this->lng->txt('order_option_generic_ascending')}",
                    "$users_title, {$this->lng->txt('order_option_generic_descending')}",
                ),
            self::COL_VERSION => $this->column_factory->text($this->lng->txt('version'))->withIsSortable(false),
        ];

        $url_builder = new URLBuilder(new DataFactory()->uri($this->request->getUri()->__toString()));

        /** @var URLBuilder $url_builder */
        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            [self::TABLE_IDENTIFIER],
            'table_action',
            'row_ids',
        );

        $actions = [
            self::ACTION_SET_DEFAULT => $this->action_factory->single(
                $this->lng->txt('default_skin_style'),
                $url_builder->withParameter($action_parameter_token, self::ACTION_SET_DEFAULT),
                $row_id_token,
            ),
            self::ACTION_ACTIVATE => $this->action_factory->standard(
                $this->lng->txt('sty_activate'),
                $url_builder->withParameter($action_parameter_token, self::ACTION_ACTIVATE),
                $row_id_token,
            ),
            self::ACTION_DEACTIVATE => $this->action_factory->standard(
                $this->lng->txt('sty_deactivate'),
                $url_builder->withParameter($action_parameter_token, self::ACTION_DEACTIVATE),
                $row_id_token,
            ),
            self::ACTION_EXPORT => $this->action_factory->single(
                $this->lng->txt('export'),
                $url_builder->withParameter($action_parameter_token, self::ACTION_EXPORT),
                $row_id_token,
            ),
            self::ACTION_ASSIGN_STYLE => $this->action_factory->single(
                $this->lng->txt('sty_change_user_assignment'),
                $url_builder->withParameter($action_parameter_token, self::ACTION_ASSIGN_STYLE),
                $row_id_token,
            )->withAsync(),
            self::ACTION_ASSIGN_CATEGORIES => $this->action_factory->single(
                $this->lng->txt('sty_cat_assignments'),
                $url_builder->withParameter($action_parameter_token, self::ACTION_ASSIGN_CATEGORIES),
                $row_id_token,
            ),
        ];

        return $this->ui_factory->table()
            ->data(
                new SystemStylesTableDataRetrieval($this->lng, $this->ui_factory, $this->renderer),
                $this->lng->txt('manage_system_styles'),
                $columns,
            )
            ->withId(self::TABLE_IDENTIFIER)
            ->withActions($is_management_enabled ? $actions : [])
            ->withRequest($this->request);
    }

    public function performAction(ArrayBasedRequestWrapper $query, bool $actions_enabled): void
    {
        if (!$query->has(self::QUERY_ACTION_KEY) || !$query->has(self::QUERY_ROW_IDS_KEY)) {
            return;
        }

        if (!$actions_enabled) {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage($this->lng->txt('permission_denied'), ilSystemStyleMessage::TYPE_ERROR),
            );
            return;
        }

        /** @var string $action */
        $action = $query->retrieve(self::QUERY_ACTION_KEY, $this->refinery->to()->string());
        /** @var string[] $row_ids */
        $row_ids = $query->retrieve(
            self::QUERY_ROW_IDS_KEY,
            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
        );

        switch ($action) {
            case self::ACTION_SET_DEFAULT:
                $this->setDefaultAction($row_ids);
                break;
            case self::ACTION_ACTIVATE:
                $this->activate($row_ids);
                break;
            case self::ACTION_DEACTIVATE:
                $this->deactivate($row_ids);
                break;
            case self::ACTION_ASSIGN_STYLE:
                $this->assignStyle($row_ids);
                // no break
            case self::ACTION_ASSIGN_CATEGORIES:
                $this->assignCategories($row_ids);
                break;
            case self::ACTION_EXPORT:
                $this->export($row_ids);
                break;
            default:
        }
    }

    /**
     * @param string[] $row_ids
     */
    private function setDefaultAction(array $row_ids): void
    {
        $style = $this->singleParentStyle($row_ids);
        if ($style === null) {
            $this->backToOverview();
            return;
        }

        $skin_id = (string) $style['skin_id'];
        $style_id = (string) $style['style_id'];
        if (!ilSystemStyleSettings::_lookupActivatedStyle($skin_id, $style_id)) {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage(
                    $style['style_name'] . ': ' . $this->lng->txt('inactive'),
                    ilSystemStyleMessage::TYPE_ERROR,
                ),
            );
            $this->backToOverview();
            return;
        }

        if (ilSystemStyleSettings::getCurrentDefaultSkin() !== $skin_id
            || ilSystemStyleSettings::getCurrentDefaultStyle() !== $style_id
        ) {
            ilSystemStyleSettings::setCurrentDefaultStyle($skin_id, $style_id);
            $this->message_stack->addMessage(
                new ilSystemStyleMessage($this->lng->txt('msg_obj_modified'), ilSystemStyleMessage::TYPE_SUCCESS),
            );
        }

        $this->backToOverview();
    }

    /**
     * @param string[] $row_ids
     */
    private function activate(array $row_ids): void
    {
        $changed = false;
        foreach ($this->parentStyles($row_ids) as $style) {
            $skin_id = (string) $style['skin_id'];
            $style_id = (string) $style['style_id'];
            if (ilSystemStyleSettings::_lookupActivatedStyle($skin_id, $style_id)) {
                continue;
            }

            ilSystemStyleSettings::_activateStyle($skin_id, $style_id);
            $changed = true;
        }

        if ($changed) {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage($this->lng->txt('msg_obj_modified'), ilSystemStyleMessage::TYPE_SUCCESS),
            );
        }

        $this->backToOverview();
    }

    /**
     * @param string[] $row_ids
     */
    private function deactivate(array $row_ids): void
    {
        $default_skin = ilSystemStyleSettings::getCurrentDefaultSkin();
        $default_style = ilSystemStyleSettings::getCurrentDefaultStyle();
        $to_deactivate = [];

        foreach ($this->parentStyles($row_ids) as $id => $style) {
            $skin_id = (string) $style['skin_id'];
            $style_id = (string) $style['style_id'];
            if ($skin_id === $default_skin && $style_id === $default_style) {
                $this->message_stack->addMessage(
                    new ilSystemStyleMessage(
                        $this->lng->txt('cant_deactivate_default_style'),
                        ilSystemStyleMessage::TYPE_ERROR,
                    ),
                );
                continue;
            }

            if (ilObjUser::_getNumberOfUsersForStyle((string) $style['template_id'], $style_id) > 0) {
                $this->message_stack->addMessage(
                    new ilSystemStyleMessage(
                        $style['style_name'] . ': ' . $this->lng->txt('cant_deactivate_if_users_assigned'),
                        ilSystemStyleMessage::TYPE_ERROR,
                    ),
                );
                continue;
            }

            if (!ilSystemStyleSettings::_lookupActivatedStyle($skin_id, $style_id)) {
                continue;
            }

            $to_deactivate[$id] = $style;
        }

        if ($to_deactivate === []) {
            $this->backToOverview();
            return;
        }

        $remaining_active = 0;
        foreach (ilStyleDefinition::getAllSkinStyles() ?? [] as $id => $style) {
            if (($style['substyle_of'] ?? '') !== '' || isset($to_deactivate[$id])) {
                continue;
            }
            if (ilSystemStyleSettings::_lookupActivatedStyle((string) $style['skin_id'], (string) $style['style_id'])) {
                $remaining_active++;
            }
        }

        if ($remaining_active < 1) {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage($this->lng->txt('at_least_one_style'), ilSystemStyleMessage::TYPE_ERROR),
            );
            $this->backToOverview();
            return;
        }

        foreach ($to_deactivate as $style) {
            ilSystemStyleSettings::_deactivateStyle((string) $style['skin_id'], (string) $style['style_id']);
        }

        $this->message_stack->addMessage(
            new ilSystemStyleMessage($this->lng->txt('msg_obj_modified'), ilSystemStyleMessage::TYPE_SUCCESS),
        );
        $this->backToOverview();
    }

    /**
     * @param string[] $row_ids
     */
    private function assignStyle(array $row_ids): never
    {
        $style = $this->assignmentStyle($row_ids);
        if ($style === null) {
            $this->showAsync(
                $this->ui_factory->messageBox()->failure($this->lng->txt('permission_denied')),
            );
        }

        $this->ctrl->setParameterByClass(ilSystemStyleOverviewGUI::class, 'old_skin_id', (string) $style['skin_id']);
        $this->ctrl->setParameterByClass(ilSystemStyleOverviewGUI::class, 'old_style_id', (string) $style['style_id']);
        $this->showAsync(
            $this->overview->getAssignmentCreationModal((string) $style['style_name'])
                ?? $this->ui_factory->messageBox()->failure($this->lng->txt('permission_denied')),
        );
    }

    /**
     * @param string[] $row_ids
     */
    private function export(array $row_ids): void
    {
        $style = $this->singleParentStyle($row_ids);
        if ($style === null) {
            $this->backToOverview();
            return;
        }

        $skin_id = (string) $style['skin_id'];
        if ($skin_id === 'default' || $skin_id === 'other' || ($style['substyle_of'] ?? '') !== '') {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage($this->lng->txt('permission_denied'), ilSystemStyleMessage::TYPE_ERROR),
            );
            $this->backToOverview();
            return;
        }

        try {
            $this->skin_factory->skinStyleContainerFromId($skin_id, $this->message_stack)->export();
        } catch (Exception $e) {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage(
                    "{$this->lng->txt('zip_export_failed')} {$e->getMessage()}",
                    ilSystemStyleMessage::TYPE_ERROR,
                ),
            );
            $this->backToOverview();
        }
    }

    /**
     * @param string[] $row_ids
     */
    private function assignCategories(array $row_ids): void
    {
        $style = $this->singleStyle($row_ids);
        if ($style === null || ($style['substyle_of'] ?? '') === '') {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage($this->lng->txt('permission_denied'), ilSystemStyleMessage::TYPE_ERROR),
            );
            $this->backToOverview();
            return;
        }

        $this->ctrl->setParameterByClass(ilSystemStyleConfigGUI::class, 'skin_id', (string) $style['skin_id']);
        $this->ctrl->setParameterByClass(ilSystemStyleConfigGUI::class, 'style_id', (string) $style['style_id']);
        $this->ctrl->redirectByClass(ilSystemStyleConfigGUI::class, 'assignStyle');
    }

    /**
     * @param string[] $row_ids
     * @return array<string, array<string, mixed>>
     */
    private function parentStyles(array $row_ids): array
    {
        return array_filter(
            $this->selectedStyles($row_ids),
            static fn(array $style): bool => ($style['substyle_of'] ?? '') === '',
        );
    }

    /**
     * @param string[] $row_ids
     * @return array<string, mixed>|null
     */
    private function singleParentStyle(array $row_ids): ?array
    {
        $styles = $this->parentStyles($row_ids);
        if (count($styles) !== 1) {
            $this->message_stack->addMessage(
                new ilSystemStyleMessage($this->lng->txt('permission_denied'), ilSystemStyleMessage::TYPE_ERROR),
            );
            return null;
        }

        return reset($styles);
    }

    /**
     * @param string[] $row_ids
     * @return array<string, mixed>|null
     */
    private function assignmentStyle(array $row_ids): ?array
    {
        if (count($row_ids) === 1 && $row_ids[0] === 'other') {
            return [
                'skin_id' => 'other',
                'style_id' => 'other',
                'style_name' => 'other',
                'substyle_of' => '',
            ];
        }

        $style = $this->singleStyle($row_ids);
        if ($style === null || ($style['substyle_of'] ?? '') !== '') {
            return null;
        }

        return $style;
    }

    /**
     * @param string[] $row_ids
     * @return array<string, mixed>|null
     */
    private function singleStyle(array $row_ids): ?array
    {
        $styles = $this->selectedStyles($row_ids);
        if (count($styles) !== 1) {
            return null;
        }

        return reset($styles);
    }

    /**
     * @param string[] $row_ids
     * @return array<string, array<string, mixed>>
     */
    private function selectedStyles(array $row_ids): array
    {
        $all_styles = ilStyleDefinition::getAllSkinStyles() ?? [];
        if ($row_ids === [] || $row_ids[0] === self::ALL_OBJECTS) {
            return $all_styles;
        }

        return array_intersect_key($all_styles, array_flip($row_ids));
    }

    private function backToOverview(): void
    {
        $this->message_stack->sendMessages();
        $this->ctrl->redirectByClass(ilSystemStyleOverviewGUI::class, 'edit');
    }

    private function showAsync(Component $component): never
    {
        $this->http->saveResponse(
            $this->http->response()->withBody(
                Streams::ofString($this->renderer->renderAsync($component)),
            ),
        );
        $this->http->sendResponse();
        $this->http->close();
    }
}
