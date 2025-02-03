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

namespace ILIAS\Badge;

use ILIAS\UI\Factory;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ilLanguage;
use ilGlobalTemplateInterface;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Services;
use Psr\Http\Message\RequestInterface;
use ILIAS\UI\Component\Table\DataRowBuilder;
use Generator;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\URLBuilderToken;
use ilBadge;
use ilBadgeAuto;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\Component\Table\Column\Column;

class ilBadgeTableGUI implements DataRetrieval
{
    private readonly Factory $factory;
    private readonly Renderer $renderer;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly Services $http;
    private readonly int $parent_id;
    private readonly string $parent_type;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    private ilBadgeImage $badge_image_service;
    /**
     * @return null|list<array{
     *     id: int,
     *     badge: ilBadge,
     *     active: bool,
     *     type: string,
     *     manual: bool,
     *     image: string,
     *     title: string,
     *     title_sortable: string
     * }>
     */
    private ?array $cached_records = null;

    public function __construct(int $parent_obj_id, string $parent_obj_type, protected bool $has_write = false)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->request = $DIC->http()->request();
        $this->http = $DIC->http();

        $this->parent_id = $parent_obj_id;
        $this->parent_type = $parent_obj_type;
        $this->badge_image_service = new ilBadgeImage(
            $DIC->resourceStorage(),
            $DIC->upload(),
            $DIC->ui()->mainTemplate()
        );
    }

    /**
     * @return list<array{
     *     id: int,
     *     badge: ilBadge,
     *     active: bool,
     *     type: string,
     *     manual: bool,
     *     image: string,
     *     title: string,
     *     title_sortable: string
     * }>
     */
    private function getRecords(): array
    {
        if ($this->cached_records !== null) {
            return $this->cached_records;
        }

        $rows = [];
        $modal_container = new ModalBuilder();

        foreach (ilBadge::getInstancesByParentId($this->parent_id) as $badge) {
            $images = [
                'rendered' => null,
                'large' => null,
            ];
            $image_src = $this->badge_image_service->getImageFromBadge($badge);
            if ($image_src !== '') {
                $images['rendered'] = $this->renderer->render(
                    $this->factory->image()->responsive(
                        $image_src,
                        $badge->getTitle()
                    )
                );

                $image_src_large = $this->badge_image_service->getImageFromBadge(
                    $badge,
                    ilBadgeImage::IMAGE_SIZE_XL
                );
                if ($image_src_large !== '') {
                    $images['large'] = $this->factory->image()->responsive(
                        $image_src_large,
                        $badge->getTitle()
                    );
                }
            }

            $modal = $modal_container->constructModal(
                $images['large'],
                $badge->getTitle(),
                [
                    'description' => $badge->getDescription(),
                    'badge_criteria' => $badge->getCriteria(),
                ]
            );

            $rows[] = [
                'id' => $badge->getId(),
                'badge' => $badge,
                'active' => $badge->isActive(),
                'type' => $this->parent_type !== 'bdga'
                    ? ilBadge::getExtendedTypeCaption($badge->getTypeInstance())
                    : $badge->getTypeInstance()->getCaption(),
                'manual' => !$badge->getTypeInstance() instanceof ilBadgeAuto,
                'image' => $images['rendered'] ? ($modal_container->renderShyButton(
                    $images['rendered'],
                    $modal
                ) . ' ') : '',
                'title' => implode('', [
                    $modal_container->renderShyButton($badge->getTitle(), $modal),
                    $modal_container->renderModal($modal)
                ]),
                'title_sortable' => $badge->getTitle()
            ];
        }

        $this->cached_records = $rows;

        return $rows;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        $records = $this->getRecords();

        if ($order) {
            [$order_field, $order_direction] = $order->join(
                [],
                fn($ret, $key, $value) => [$key, $value]
            );

            usort($records, static function (array $left, array $right) use ($order_field): int {
                if (\in_array($order_field, ['title', 'type'], true)) {
                    if ($order_field === 'title') {
                        $order_field .= '_sortable';
                    }

                    return \ilStr::strCmp(
                        $left[$order_field],
                        $right[$order_field]
                    );
                }

                if ($order_field === 'active') {
                    return $right[$order_field] <=> $left[$order_field];
                }

                return $left[$order_field] <=> $right[$order_field];
            });

            if ($order_direction === Order::DESC) {
                $records = array_reverse($records);
            }
        }

        if ($range) {
            $records = \array_slice($records, $range->getStart(), $range->getLength());
        }

        foreach ($records as $record) {
            yield $row_builder
                ->buildDataRow((string) $record['id'], $record)
                ->withDisabledAction(
                    'award_revoke_badge',
                    !$record['manual'] || !$record['active']
                );
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return \count($this->getRecords());
    }

    /**
     * @return array<string, Column>
     */
    private function getColumns(): array
    {
        return [
            'image' => $this->factory->table()->column()->text($this->lng->txt('image'))->withIsSortable(false),
            'title' => $this->factory->table()->column()->text($this->lng->txt('title')),
            'type' => $this->factory->table()->column()->text($this->lng->txt('type')),
            'active' => $this->factory->table()->column()->boolean(
                $this->lng->txt('active'),
                $this->lng->txt('yes'),
                $this->lng->txt('no')
            )->withOrderingLabels(
                $this->lng->txt('badge_sort_active_badges_first'),
                $this->lng->txt('badge_sort_active_badges_last')
            )
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    private function getActions(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token,
    ): array {
        return $this->has_write ? [
            'badge_table_activate' =>
                $this->factory->table()->action()->multi(
                    $this->lng->txt('activate'),
                    $url_builder->withParameter($action_parameter_token, 'badge_table_activate'),
                    $row_id_token
                ),
            'badge_table_deactivate' =>
                $this->factory->table()->action()->multi(
                    $this->lng->txt('deactivate'),
                    $url_builder->withParameter($action_parameter_token, 'badge_table_deactivate'),
                    $row_id_token
                ),
            'badge_table_edit' => $this->factory->table()->action()->single(
                $this->lng->txt('edit'),
                $url_builder->withParameter($action_parameter_token, 'badge_table_edit'),
                $row_id_token
            ),
            'badge_table_delete' =>
                $this->factory->table()->action()->standard(
                    $this->lng->txt('delete'),
                    $url_builder->withParameter($action_parameter_token, 'badge_table_delete'),
                    $row_id_token
                ),
            'award_revoke_badge' =>
                $this->factory->table()->action()->single(
                    $this->lng->txt('badge_award_revoke'),
                    $url_builder->withParameter($action_parameter_token, 'award_revoke_badge'),
                    $row_id_token
                )
        ] : [];
    }

    public function renderTable(): void
    {
        $df = new \ILIAS\Data\Factory();

        $table_uri = $df->uri($this->request->getUri()->__toString());
        $url_builder = new URLBuilder($table_uri);
        $query_params_namespace = ['tid'];

        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'id',
        );

        $table = $this->factory
            ->table()
            ->data($this->lng->txt('obj_bdga'), $this->getColumns(), $this)
            ->withId(self::class . '_' . $this->parent_id)
            ->withOrder(new Order('title', Order::ASC))
            ->withActions($this->getActions($url_builder, $action_parameter_token, $row_id_token))
            ->withRequest($this->request);
        $out = [$table];

        $query = $this->http->wrapper()->query();

        if ($query->has($action_parameter_token->getName())) {
            $action = $query->retrieve($action_parameter_token->getName(), $this->refinery->to()->string());
            $ids = $query->retrieve($row_id_token->getName(), $this->refinery->custom()->transformation(fn($v) => $v));

            if ($action === 'delete') {
                $items = [];
                foreach ($ids as $id) {
                    $items[] = $this->factory->modal()->interruptiveItem()->keyValue(
                        $id,
                        $row_id_token->getName(),
                        $id
                    );
                }

                $this->http->saveResponse(
                    $this->http
                        ->response()
                        ->withBody(
                            Streams::ofString($this->renderer->renderAsync([
                                $this->factory->modal()->interruptive(
                                    $this->lng->txt('badge_deletion'),
                                    $this->lng->txt('badge_deletion_confirmation'),
                                    '#'
                                )->withAffectedItems($items)
                            ]))
                        )
                );
                $this->http->sendResponse();
                $this->http->close();
            }
        }

        $this->tpl->setContent($this->renderer->render($out));
    }
}
