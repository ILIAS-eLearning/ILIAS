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
use ilBadgeHandler;
use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\Link\Standard;
use ilObject;
use ilLink;
use ilObjBadgeAdministrationGUI;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\Component\Table\Action\Action;
use ilAccessHandler;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\Badge\Table\TableContentWrapper;

class ilObjectBadgeTableGUI implements DataRetrieval
{
    private const RECORD_RAW = '__raw__';

    private readonly Factory $factory;
    private readonly Renderer $renderer;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly Services $http;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilObjBadgeAdministrationGUI $parent_obj;
    private readonly ilAccessHandler $access;
    private readonly \ILIAS\ResourceStorage\Services $irss;
    private readonly ilBadgeImage $badge_image_service;
    /**
     * @var null|list<array{
     *     id: int,
     *     active: bool,
     *     type: string,
     *     image: string,
     *     title: string,
     *     title_sortable: string,
     *     container: string,
     *     container_sortable: string
     * }>
     */
    private ?array $cached_records = null;
    /** @var array<int, bool> */
    private array $has_access_by_parent_cache = [];
    /** @var array<int, int|null> */
    private array $first_ref_id_for_parent_cache = [];

    public function __construct(
        ilObjBadgeAdministrationGUI $parentObj,
        protected bool $has_write = false
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->request = $DIC->http()->request();
        $this->http = $DIC->http();
        $this->access = $DIC->access();
        $this->parent_obj = $parentObj;
        $this->irss = $DIC->resourceStorage();
        $this->badge_image_service = new ilBadgeImage(
            $this->irss,
            $DIC->upload(),
            $DIC->ui()->mainTemplate()
        );
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
        $records = $this->getRecords();

        if ($order) {
            [$order_field, $order_direction] = $order->join(
                [],
                fn($ret, $key, $value) => [$key, $value]
            );

            usort($records, static function (array $left, array $right) use ($order_field): int {
                if (\in_array($order_field, ['container', 'title', 'type'], true)) {
                    if (\in_array($order_field, ['container', 'title'], true)) {
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

        $identifications = [];
        foreach ($records as $record) {
            if (isset($record[self::RECORD_RAW]['image_rid']) && $record[self::RECORD_RAW]['image_rid'] !== '') {
                $identifications[] = $record[self::RECORD_RAW]['image_rid'];
            }
        }

        $this->irss->preload($identifications);

        $modal_container = new ModalBuilder();
        $container_deleted_title_part = '<span class="il_ItemAlertProperty">' . $this->lng->txt('deleted') . '</span>';
        foreach ($records as $record) {
            yield $row_builder->buildDataRow(
                (string) $record['id'],
                $this->enrichRecord($modal_container, $container_deleted_title_part, $record)
            );
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return \count($this->getRecords());
    }

    /**
     * @param array{
     *     id: int,
     *     active: bool,
     *     type: string,
     *     title_sortable: string,
     *     container_sortable: string,
     *     __raw__: array{
     *      id: int,
     *      parent_id: int,
     *      type_id: string,
     *      active: int,
     *      title: ?string,
     *      descr: ?string,
     *      conf: ?string,
     *      image: ?string,
     *      valid: ?string,
     *      crit: ?string,
     *      image_rid: ?string,
     *      parent_title: ?string,
     *      parent_type: ?string,
     *      deleted: bool
     *  }
     * } $record
     * @return array{
     *     id: int,
     *     active: bool,
     *     type: string,
     *     image: string,
     *     title: string,
     *     title_sortable: string,
     *     container: string,
     *     container_sortable: string
     * }
     */
    private function enrichRecord(
        ModalBuilder $modal_builder,
        string $container_deleted_title_part,
        array $record
    ): array {
        $badge_item = $record[self::RECORD_RAW];

        $badge = new ilBadge(0);
        $badge->setId($badge_item['id']);
        $badge->setImageRid($badge_item['image_rid']);
        $badge->setImage($badge_item['image']);

        $images = [
            'rendered' => null,
            'large' => null,
        ];
        $image_src = $this->badge_image_service->getImageFromResourceId($badge);
        if ($image_src !== '') {
            $images['rendered'] = $this->renderer->render(
                $this->factory->image()->responsive(
                    $image_src,
                    $badge_item['title']
                )
            );

            $image_src_large = $this->badge_image_service->getImageFromResourceId(
                $badge,
                ilBadgeImage::IMAGE_SIZE_XL
            );
            if ($image_src_large !== '') {
                $images['large'] = $this->factory->image()->responsive(
                    $image_src_large,
                    $badge_item['title']
                );
            }
        }

        $container_title_parts = [
            'icon' => $this->renderer->render(
                $this->factory->symbol()->icon()->custom(
                    ilObject::_getIcon($badge_item['parent_id'], 'big', $badge_item['parent_type'] ?? ''),
                    $this->lng->txt('obj_' . ($badge_item['parent_type'] ?? ''))
                )
            ),
            'title' => $badge_item['parent_title'] ?? '',
        ];

        $sortable_container_title_parts = [
            'title' => $badge_item['parent_title'] ?? ''
        ];
        if ($badge_item['deleted']) {
            $container_title_parts['suffix'] = $container_deleted_title_part;
            $sortable_container_title_parts['suffix'] = $container_deleted_title_part;
        } else {
            if (isset($this->has_access_by_parent_cache[$badge_item['parent_id']])) {
                $has_access = $this->has_access_by_parent_cache[$badge_item['parent_id']] ?? false;
                $ref_id = $this->first_ref_id_for_parent_cache[$badge_item['parent_id']] ?? null;
            } else {
                $ref_ids = ilObject::_getAllReferences($badge_item['parent_id']);
                $ref_id = array_shift($ref_ids);
                $this->first_ref_id_for_parent_cache[$badge_item['parent_id']] = $ref_id;
                $has_access = $ref_id && $this->access->checkAccess('read', '', $ref_id);
                $this->has_access_by_parent_cache[$badge_item['parent_id']] = $has_access;
            }

            if ($has_access) {
                $container_title_parts['title'] = $this->renderer->render(
                    new Standard(
                        $container_title_parts['title'],
                        (string) new URI(
                            ilLink::_getLink(
                                $ref_id,
                                $badge_item['parent_type'] ?? ''
                            )
                        )
                    )
                );
            } else {
                $container_title_parts['suffix'] = $container_deleted_title_part;
                $sortable_container_title_parts['suffix'] = $container_deleted_title_part;
            }
        }

        $modal = $modal_builder->constructModal(
            $images['large'],
            $badge_item['title'],
            [
                'active' => $badge_item['active'] ? $this->lng->txt('yes') : $this->lng->txt('no'),
                'type' => $record['type'],
                'container' => implode(' ', \array_slice($container_title_parts, 1, null, true)),
            ],
            true
        );

        return [
            'id' => $badge_item['id'],
            'active' => (bool) $badge_item['active'],
            'type' => $record['type'],
            'image' => $images['rendered'] ? ($modal_builder->renderShyButton(
                $images['rendered'],
                $modal
            ) . ' ') : '',
            'title' => implode('', [
                $modal_builder->renderShyButton($badge_item['title'], $modal),
                $modal_builder->renderModal($modal)
            ]),
            'title_sortable' => $badge_item['title'],
            'container' => implode(' ', $container_title_parts),
            'container_sortable' => implode(' ', $sortable_container_title_parts),
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     active: bool,
     *     type: string,
     *     title_sortable: string,
     *     container_sortable: string,
     *     __raw__: array{
     *      id: int,
     *      parent_id: int,
     *      type_id: string,
     *      active: int,
     *      title: ?string,
     *      descr: ?string,
     *      conf: ?string,
     *      image: ?string,
     *      valid: ?string,
     *      crit: ?string,
     *      image_rid: ?string,
     *      parent_title: ?string,
     *      parent_type: ?string,
     *      deleted: bool
     *  }
     * }>
     */
    private function getRecords(): array
    {
        if ($this->cached_records !== null) {
            return $this->cached_records;
        }

        // A filter is not implemented, yet
        $filter = [
            'type' => '',
            'title' => '',
            'object' => ''
        ];

        $types = ilBadgeHandler::getInstance()->getAvailableTypes(false);
        $raw_records = ilBadge::getObjectInstances($filter);

        $sortable_rows = array_map(function (array $badge_item) use ($types) {
            return [
                'id' => $badge_item['id'],
                'active' => (bool) $badge_item['active'],
                'type' => ilBadge::getExtendedTypeCaption($types[$badge_item['type_id']]),
                'title_sortable' => $badge_item['title'],
                'container_sortable' => ($badge_item['parent_title'] ?? '') .
                    ($badge_item['deleted'] ? ' ' . $this->lng->txt('deleted') : ''),
                self::RECORD_RAW => $badge_item
            ];
        }, $raw_records);

        $this->cached_records = $sortable_rows;

        return $this->cached_records;
    }

    /**
     * @return array<string, Column>
     */
    public function getColumns(): array
    {
        return [
            'image' => $this->factory->table()->column()->text($this->lng->txt('image'))->withIsSortable(false),
            'title' => $this->factory->table()->column()->text($this->lng->txt('title')),
            'type' => $this->factory->table()->column()->text($this->lng->txt('type')),
            'container' => $this->factory->table()->column()->text($this->lng->txt('object')),
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
     * @return array<string, Action>
     */
    private function getActions(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token
    ): array {
        return $this->has_write ? [
            'obj_badge_activate' => $this->factory->table()->action()->multi(
                $this->lng->txt('activate'),
                $url_builder->withParameter($action_parameter_token, 'obj_badge_activate'),
                $row_id_token
            ),
            'obj_badge_deactivate' =>
                $this->factory->table()->action()->multi(
                    $this->lng->txt('deactivate'),
                    $url_builder->withParameter($action_parameter_token, 'obj_badge_deactivate'),
                    $row_id_token
                ),
            'obj_badge_delete' =>
                $this->factory->table()->action()->multi(
                    $this->lng->txt('delete'),
                    $url_builder->withParameter($action_parameter_token, 'obj_badge_delete'),
                    $row_id_token
                ),
            'obj_badge_show_users' =>
                $this->factory->table()->action()->single(
                    $this->lng->txt('user'),
                    $url_builder->withParameter($action_parameter_token, 'obj_badge_show_users'),
                    $row_id_token
                )
        ] : [];
    }

    public function renderTable(string $url): void
    {
        $df = new \ILIAS\Data\Factory();

        $table_uri = $df->uri($url);
        $url_builder = new URLBuilder($table_uri);
        $query_params_namespace = ['tid'];

        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'id',
        );

        $table = $this->factory
            ->table()
            ->data($this, $this->lng->txt('badge_object_badges'), $this->getColumns())
            ->withId(str_replace('\\', '', self::class))
            ->withOrder(new Order('title', Order::ASC))
            ->withRange(new Range(0, 100))
            ->withActions($this->getActions($url_builder, $action_parameter_token, $row_id_token))
            ->withRequest($this->request);

        $query = $this->http->wrapper()->query();
        if ($query->has($action_parameter_token->getName())) {
            $action = $query->retrieve($action_parameter_token->getName(), $this->refinery->kindlyTo()->string());
            $ids = $query->retrieve($row_id_token->getName(), $this->refinery->custom()->transformation(fn($v) => $v));

            if ($action === 'obj_badge_delete') {
                $items = [];
                if (\is_array($ids) && \count($ids) > 0) {
                    if ($ids === ['ALL_OBJECTS']) {
                        $filter = [
                            'type' => '',
                            'title' => '',
                            'object' => ''
                        ];
                        $ids = [];
                        foreach (ilBadge::getObjectInstances($filter) as $badge_item) {
                            $ids[] = $badge_item['id'];
                        }
                    }

                    foreach ($ids as $id) {
                        $badge = new ilBadge((int) $id);
                        $items[] = $this->factory->modal()->interruptiveItem()->keyValue(
                            (string) $id,
                            (string) $badge->getId(),
                            $badge->getTitle()
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
        }

        $content_wrapper = new TableContentWrapper($this->renderer, $this->factory);
        $this->tpl->setContent($this->renderer->render(
            $content_wrapper->wrap(
                $table
            )
        ));
    }
}
