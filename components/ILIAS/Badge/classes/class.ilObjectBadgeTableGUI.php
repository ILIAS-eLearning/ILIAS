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

class ilObjectBadgeTableGUI implements DataRetrieval
{
    private readonly Factory $factory;
    private readonly Renderer $renderer;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly Services $http;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilObjBadgeAdministrationGUI $parent_obj;
    private readonly ilAccessHandler $access;
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
        $this->badge_image_service = new ilBadgeImage(
            $DIC->resourceStorage(),
            $DIC->upload(),
            $DIC->ui()->mainTemplate()
        );
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

        foreach ($records as $record) {
            yield $row_builder->buildDataRow((string) $record['id'], $record);
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return \count($this->getRecords());
    }

    /**
     * @return list<array{
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
    private function getRecords(): array
    {
        if ($this->cached_records !== null) {
            return $this->cached_records;
        }

        $container_deleted_title_part = '<span class="il_ItemAlertProperty">' . $this->lng->txt('deleted') . '</span>';
        $modal_container = new ModalBuilder();

        // A filter is not implemented, yet
        $filter = [
            'type' => '',
            'title' => '',
            'object' => ''
        ];

        $types = ilBadgeHandler::getInstance()->getAvailableTypes(false);
        $rows = [];
        foreach (ilBadge::getObjectInstances($filter) as $badge_item) {
            $type_caption = ilBadge::getExtendedTypeCaption($types[$badge_item['type_id']]);

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

            $sortable_container_title_parts = [
                'title' => $badge_item['parent_title'] ?? ''
            ];
            $container_title_parts = [
                'icon' => $this->renderer->render(
                    $this->factory->symbol()->icon()->custom(
                        ilObject::_getIcon($badge_item['parent_id'], 'big', $badge_item['parent_type'] ?? ''),
                        $this->lng->txt('obj_' . ($badge_item['parent_type'] ?? ''))
                    )
                ),
                'title' => $sortable_container_title_parts['title'],
            ];

            if ($badge_item['deleted']) {
                $container_title_parts['suffix'] = $container_deleted_title_part;
                $sortable_container_title_parts['suffix'] = $container_deleted_title_part;
            } else {
                $ref_ids = ilObject::_getAllReferences($badge_item['parent_id']);
                $ref_id = array_shift($ref_ids);
                if ($ref_id && $this->access->checkAccess('read', '', $ref_id)) {
                    $container_title_parts['title'] = $this->renderer->render(
                        new Standard(
                            $container_title_parts['title'],
                            (string) new URI(ilLink::_getLink($ref_id))
                        )
                    );
                } else {
                    $container_title_parts['suffix'] = $container_deleted_title_part;
                    $sortable_container_title_parts['suffix'] = $container_deleted_title_part;
                }
            }

            $modal = $modal_container->constructModal(
                $images['large'],
                $badge_item['title'],
                [
                    'active' => $badge_item['active'] ? $this->lng->txt('yes') : $this->lng->txt('no'),
                    'type' => $type_caption,
                    'container' => implode(' ', \array_slice($container_title_parts, 1, null, true)),
                ]
            );

            $rows[] = [
                'id' => $badge_item['id'],
                'active' => (bool) $badge_item['active'],
                'type' => $type_caption,
                'image' => $images['rendered'] ? ($modal_container->renderShyButton(
                    $images['rendered'],
                    $modal
                ) . ' ') : '',
                'title' => implode('', [
                    $modal_container->renderShyButton($badge_item['title'], $modal),
                    $modal_container->renderModal($modal)
                ]),
                'title_sortable' => $badge_item['title'],
                'container' => implode(' ', $container_title_parts),
                'container_sortable' => implode(' ', $sortable_container_title_parts),
            ];
        }

        $this->cached_records = $rows;

        return $rows;
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
            'container' => $this->factory->table()->column()->text($this->lng->txt('container')),
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
            ->data($this, $this->lng->txt('badge_object_badges'), $this->getColumns())
            ->withId(self::class)
            ->withOrder(new Order('title', Order::ASC))
            ->withActions($this->getActions($url_builder, $action_parameter_token, $row_id_token))
            ->withRequest($this->request);

        $out = [$table];

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

        $this->tpl->setContent($this->renderer->render($out));
    }
}
