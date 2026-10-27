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

namespace ILIAS\News\Table;

use Generator;
use ilBlockSetting;
use ilCtrl;
use ilGlobalTemplateInterface;
use ilLanguage;
use ilNewsItemGUI;
use ilObject;
use ilObjUser;
use ilRepositoryGUI;
use ilSetting;
use ilUIFilterService;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\News\Access\NewsAccess;
use ILIAS\News\Common\HttpService;
use ILIAS\News\Common\Table\Table;
use ILIAS\News\Common\Table\TableActionExecutorTrait;
use ILIAS\News\Common\Table\TableActions;
use ILIAS\News\Data\NewsCollection;
use ILIAS\News\Data\NewsContext;
use ILIAS\News\Data\NewsCriteria;
use ILIAS\News\Data\NewsItem;
use ILIAS\News\Domain\NewsCollectionService;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Filter\Standard as StandardFilter;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\News\NewsItem\Table\Action\NewsItemTableActionsFactory;

class NewsItemTable implements Table
{
    use TableActionExecutorTrait;

    public const string URL_NS = 'nmit';
    public const string ROW_ID_PARAMETER = 'news_item_row';
    public const string ACTION_PARAMETER = 'action';
    public const string ACTION_TYPE_PARAMETER = 'action_type';

    protected ?StandardFilter $filter = null;

    public function __construct(
        protected Factory $ui_factory,
        protected ilLanguage $lng,
        protected ServerRequestInterface $request,
        protected ilCtrl $ctrl,
        protected ilSetting $setting,
        protected Renderer $ui_renderer,
        protected NewsAccess $news_access,
        protected ilUIFilterService $filter_service,
        protected NewsCollectionService $news_collection_service,
        protected ilObjUser $user,
        protected HttpService $http_service,
        protected ilGlobalTemplateInterface $main_tpl,
        protected Refinery $refinery,
    ) {
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $records = $this->limitRecords(
            $this->sortRecords(
                $this->loadRecords(
                    $this->extractFilterData($filter_data)
                ),
                $order
            ),
            $range
        );

        foreach ($records as $record) {
            yield $this->getTableActions()->onDataRow(
                $row_builder->buildDataRow((string) $record['id'], $record),
                $record
            );
        }
    }

    private function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();

        $columns = [
            'post' => $column_factory->text($this->lng->txt('news_news_item_post'))->withIsSortable(false),
            'assigned_to' => $column_factory->text($this->lng->txt('news_attached_to'))->withIsSortable(false),
        ];

        if ($this->isInternalRssEnabled()) {
            $columns['access'] = $column_factory->text($this->lng->txt('access'))->withIsSortable(false);
        }

        $date_format = $this->user->getDateTimeFormat();
        $columns += [
            'author' => $column_factory->text($this->lng->txt('author'))->withIsSortable(false),
            'created_at' => $column_factory->date($this->lng->txt('created'), $date_format)->withIsSortable(true),
            'updated_at' => $column_factory->date($this->lng->txt('last_update'), $date_format)->withIsSortable(true),
        ];

        return $columns;
    }

    public function getTotalRowCount(mixed $additional_viewcontrol_data, mixed $filter_data, mixed $additional_parameters): ?int
    {
        return count($this->loadRecords($this->extractFilterData($filter_data)));
    }

    private function extractFilterData(mixed $filter_data): ?array
    {
        return $filter_data instanceof StandardFilter
            ? $this->filter_service->getData($filter_data)
            : null;
    }

    private function getNewsRequestContext(): array
    {
        return [
            'ref_id' => (int) ($this->request->getQueryParams()['ref_id'] ?? 0),
            'context_obj_id' => $this->ctrl->getContextObjId() ?? 0,
            'context_obj_type' => $this->ctrl->getContextObjType() ?? '',
            'context_sub_obj_id' => 0,
        ];
    }

    private function isInternalRssEnabled(): bool
    {
        return (bool) $this->setting->get('enable_rss_for_internal');
    }

    private function sortRecords(array $records, Order $order): array
    {
        foreach ($order->get() as $order_field => $order_direction) {
            usort($records, static function (array $left, array $right) use ($order_field): int {
                $l = $left[$order_field] ?? null;
                $r = $right[$order_field] ?? null;
                return is_string($l) && is_string($r) ? strcmp($l, $r) : $l <=> $r;
            });

            if ($order_direction !== Order::DESC) {
                continue;
            }

            $records = array_reverse($records);
        }

        return $records;
    }

    private function limitRecords(array $records, Range $range): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    public function getTableId(): string
    {
        return self::URL_NS;
    }

    /**
     * @return array<Component>
     */
    public function getComponents(URLBuilder $url_builder): array
    {
        $this->filter = $this->getFilter();
        $table = $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('news'),
            $this->getColumns()
        )
            ->withRequest($this->request)
            ->withFilter($this->filter)
            ->withActions($this->getTableActions()->getEnabledActions(...$this->acquireParameters($url_builder)))
            ->withId($this->getTableId());

        return [
            $this->filter,
            $table
        ];
    }

    /**
     * @return array{0: URLBuilder, 1: URLBuilderToken, 2: URLBuilderToken, 3: URLBuilderToken}
     */
    protected function acquireParameters(URLBuilder $url_builder): array
    {
        return $url_builder->acquireParameters(
            [$this->getTableId()],
            self::ROW_ID_PARAMETER,
            self::ACTION_PARAMETER,
            self::ACTION_TYPE_PARAMETER
        );
    }

    protected function getTableActions(): TableActions
    {
        return (new NewsItemTableActionsFactory(
            $this->ui_factory,
            $this->lng,
            $this->news_access,
            $this->ctrl,
            $this->ui_renderer,
            $this->refinery,
            $this->http_service,
            $this->main_tpl,
            $this->loadRecords()
        ))->getTableActions();
    }

    private function getFilter(): StandardFilter
    {
        $author_options = [];

        foreach ($this->loadNewsCollection()->getNewsItems() as $item) {
            $user_id = $item->getUserId();
            if ($user_id <= 0) {
                continue;
            }

            $login = ilObjUser::_lookupLogin($user_id);
            $author_options[$login] = $login;
        }

        $field_factory = $this->ui_factory->input()->field();
        $filter_inputs = [
            'text' => $field_factory->text($this->lng->txt('news_news_item_content')),
        ];

        if ($this->isInternalRssEnabled()) {
            $filter_inputs['access'] = $field_factory->select(
                $this->lng->txt('access'),
                [
                    'public' => $this->lng->txt('news_visibility_public'),
                    'users' => $this->lng->txt('news_visibility_users'),
                ]
            );
        }

        $filter_inputs['author'] = $field_factory->select($this->lng->txt('author'), $author_options);

        return $this->filter_service->standard(
            'news_item_table_filter',
            $this->ctrl->getLinkTargetByClass(ilNewsItemGUI::class, 'editNews', ''),
            $filter_inputs,
            array_fill(0, count($filter_inputs), true),
            true,
            true
        );
    }

    private function buildOverviewCriteria(string $context_obj_type): NewsCriteria
    {
        return new NewsCriteria(
            limit: null,
            prevent_nesting: !in_array($context_obj_type, ['cat', 'grp', 'crs', 'root'], true),
            no_auto_generated: true,
            include_read_status: false
        );
    }

    private function loadNewsCollection(): NewsCollection
    {
        $news_request_context = $this->getNewsRequestContext();

        return $this->news_collection_service->getNewsForContext(
            new NewsContext(
                $news_request_context['ref_id'],
                $news_request_context['context_obj_id'],
                $news_request_context['context_obj_type']
            ),
            $this->buildOverviewCriteria($this->getNewsRequestContext()['context_obj_type']),
            $this->user->getId()
        );
    }

    private function loadRecords(?array $filter_data = null): array
    {
        $data = $this->loadNewsCollection()->getNewsItems();
        if ($data === []) {
            return [];
        }

        $filter = $filter_data ?? [];
        $filter_raw = $filter['text'] ?? '';
        $filter_text = $filter_raw !== '' ? strtolower($filter_raw) : '';
        $filter_access = $filter['access'] ?? '';
        $filter_author = $filter['author'] ?? '';
        $enable_internal_rss = $this->isInternalRssEnabled();

        $records = [];
        foreach ($data as $item) {
            $item_matches_filters = $this->itemMatchesFilters(
                $item,
                $filter_text,
                $filter_access,
                $filter_author,
                $enable_internal_rss
            );

            if (!$item_matches_filters || !$this->itemShouldAppearInTable($item)) {
                continue;
            }

            $assigned_to = $this->assignedToMarkupOrNull($item);
            if ($assigned_to === null) {
                continue;
            }

            $record = [
                'id' => (string) $item->getId(),
                'post' => $this->renderPostCell($item),
                'assigned_to' => $assigned_to,
                'author' => $this->renderAuthorDisplay($item),
                'created_at' => $item->getCreationDate(),
                'updated_at' => $item->getUpdateDate(),
            ];

            if ($enable_internal_rss) {
                $record['access'] = $this->renderAccessDisplay($item);
            }

            $records[] = $record;
        }

        return $records;
    }

    private function itemMatchesFilters(
        NewsItem $item,
        string $search_text,
        string $filter_access,
        string $filter_author,
        bool $enable_internal_rss
    ): bool {
        if ($search_text !== '') {
            $search_text = strtolower($search_text);

            if (
                !str_contains(strtolower($item->getTitle()), $search_text)
                && !str_contains(strtolower($item->getContent() . $item->getContentLong()), $search_text)
            ) {
                return false;
            }
        }

        if (
            $filter_access !== ''
            && $this->getItemAccess($item, $enable_internal_rss) !== $filter_access
        ) {
            return false;
        }

        if ($filter_author !== '') {
            $filter_user_id = $item->getUserId();
            if (
                $filter_user_id <= 0
                || ilObjUser::_lookupLogin($filter_user_id) !== $filter_author
            ) {
                return false;
            }
        }

        return true;
    }

    private function itemShouldAppearInTable(NewsItem $item): bool
    {
        return $item->getId() > 0 && ($this->news_access->canEdit($item) || $this->news_access->canDelete($item));
    }

    private function renderPostCell(NewsItem $item): string
    {
        $post_title = $item->getTitle();
        if ($post_title === '' || $item->getId() <= 0) {
            return '';
        }

        $post_content = "{$item->getContent()}{$item->getContentLong()}";
        $lightbox_page = $this->ui_factory->modal()->lightboxTextPage(
            $post_content ?: $this->lng->txt('news_news_item_no_content'),
            $post_title
        );
        $modal = $this->ui_factory->modal()->lightbox([$lightbox_page]);
        $post_button = $this->ui_factory->button()->shy($post_title, '')
            ->withOnClick($modal->getShowSignal());

        return $this->ui_renderer->render([$post_button, $modal]);
    }

    private function renderAccessDisplay(NewsItem $item): string
    {
        return $this->lng->txt(
            $this->getItemAccess($item, true) === 'public'
                ? 'news_visibility_public'
                : 'news_visibility_users'
        );
    }

    private function renderAuthorDisplay(NewsItem $item): string
    {
        $author_user_id = $item->getUserId();
        return $author_user_id <= 0 ? '' : ilObjUser::_lookupLogin($author_user_id);
    }

    private function assignedToMarkupOrNull(NewsItem $item): ?string
    {
        $context_obj_type = $item->getContextObjType();
        $context_obj_id = $item->getContextObjId();
        if ($context_obj_type === '' || $context_obj_id <= 0) {
            return '';
        }

        $ref_ids = ilObject::_getAllReferences($context_obj_id);
        $item_ref_id = array_values($ref_ids)[0] ?? 0;

        if ($item_ref_id <= 0) {
            return null;
        }

        $obj_title = ilObject::_lookupTitle($context_obj_id);
        $obj_type_txt = $this->lng->txt("obj_{$context_obj_type}");

        $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $item_ref_id);
        $link_url = $this->ctrl->getLinkTargetByClass(ilRepositoryGUI::class, '');
        $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', null);

        $obj_link = $this->ui_factory->link()->standard($obj_title, $link_url);

        return "{$obj_type_txt}: {$this->ui_renderer->render($obj_link)}";
    }

    private function getItemAccess(NewsItem $item, bool $enable_internal_rss): string
    {
        if (!$enable_internal_rss) {
            return '';
        }

        $public_notifications_for_context = (bool) ilBlockSetting::_lookup(
            'news',
            'public_notifications',
            0,
            $item->getContextObjId()
        );
        $public_via_block_defaults = $item->getPriority() === 0 && $public_notifications_for_context;

        return ($item->getVisibility() === 'public' || $public_via_block_defaults) ? 'public' : 'users';
    }

    public function execute(URLBuilder $url_builder): ?Modal
    {
        return $this->getTableActions()->execute(...$this->acquireParameters($url_builder));
    }
}
