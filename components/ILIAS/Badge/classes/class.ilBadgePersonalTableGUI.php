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

use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\UI\Factory;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\Link\Standard;
use ILIAS\Badge\ilBadgeImage;
use ILIAS\Badge\PresentationHeader;
use ILIAS\Badge\Tile;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\Badge\Table\TableContentWrapper;

class ilBadgePersonalTableGUI implements DataRetrieval
{
    private readonly Factory $factory;
    private readonly Renderer $renderer;
    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ILIAS\DI\Container $dic;
    private readonly ilObjUser $user;
    private readonly ilAccessHandler $access;
    private readonly Tile $tile;
    private readonly IRSS $irss;

    /**
     * @return null|list<array{
     *     id: int,
     *     active: bool,
     *     image: string,
     *     awarded_by: string,
     *     awarded_by_sortable: string,
     *     badge_issued_on: DateTimeImmutable,
     *     title: string,
     *     title_sortable: string
     *  }>
     */
    private ?array $cached_records = null;
    private ilObjectDataCache $object_data_cache;

    /**
     * @var array{id: int, type: string, title: string, deleted: bool}
     */
    private $parent_metadata_cache = [];

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->tile = new Tile($DIC);
        $this->irss = $DIC['resource_storage'];
        $this->object_data_cache = $DIC['ilObjDataCache'];
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
                if (in_array($order_field, ['title', 'awarded_by'], true)) {
                    if (in_array($order_field, ['title', 'awarded_by'], true)) {
                        $order_field .= '_sortable';
                    }

                    return ilStr::strCmp(
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

        $access_cache = [];
        $ref_id_cache = [];
        $parent_obj_ids = [];
        $identifications = [];

        foreach ($records as $record) {
            $badge = $record['badge'];
            $parent_obj_ids[] = $badge->getParentId();
            $identifications[] = $badge->getImageRid();
        }
        $this->irss->preload(array_filter($identifications));
        $this->object_data_cache->preloadObjectCache(array_unique($parent_obj_ids));

        foreach ($records as $record) {
            yield $row_builder->buildDataRow((string) $record['id'], $this->enrichRecord(
                $record,
                $access_cache,
                $ref_id_cache
            ));
        }
    }

    /**
     * @param array{
     *      id: int,
     *      title_sortable: string,
     *      awarded_by_sortable: string,
     *      badge_issued_on: DateTimeImmutable,
     *      active: bool,
     *      assignment: ilBadgeAssignment,
     *      badge: ilBadge
     *  } $record
     * @param array<int, bool> $access_cache
     * @param array<int, int> $ref_id_cache
     * @return array
     */
    private function enrichRecord(
        array $record,
        array &$access_cache,
        array &$ref_id_cache
    ): array {
        $badge = $record['badge'];
        $ass = $record['assignment'];

        $parent = null;
        if ($badge->getParentId()) {
            if (isset($this->parent_metadata_cache[$badge->getParentId()])) {
                $parent = $this->parent_metadata_cache[$badge->getParentId()];
            } else {
                $parent = $badge->getParentMeta();
                $this->parent_metadata_cache[$badge->getParentId()] = $parent;
            }
            if ($parent['type'] === 'bdga') {
                $parent = null;
            }
        }

        $awarded_by = '';
        if ($parent !== null) {
            if (isset($ref_id_cache[$parent['id']])) {
                $ref_id = $ref_id_cache[$parent['id']];
            } else {
                $ref_id = current(ilObject::_getAllReferences($parent['id']));
                $ref_id_cache[$parent['id']] = $ref_id;
            }

            $awarded_by = $parent['title'];
            if ($ref_id) {
                $access = $access_cache[$ref_id] ?? $this->access->checkAccess('read', '', $ref_id);
                if (!isset($access_cache[$ref_id])) {
                    $access_cache[$ref_id] = $access;
                }
            }
            if ($ref_id && $access) {
                $awarded_by = $this->renderer->render(
                    new Standard(
                        $awarded_by,
                        (string) new URI(ilLink::_getLink($ref_id, $parent['type']))
                    )
                );
            }

            $awarded_by = implode(' ', [
                $this->renderer->render(
                    $this->factory->symbol()->icon()->standard(
                        $parent['type'],
                        $parent['title']
                    )
                ),
                $awarded_by
            ]);
        }

        $record += [
            'image' => $this->renderer->render(
                $this->tile->asImage(
                    $this->tile->modalContentWithAssignment($badge, $ass),
                    ilBadgeImage::IMAGE_SIZE_XS
                )
            ),
            'title' => $this->renderer->render(
                $this->tile->asTitle(
                    $this->tile->modalContentWithAssignment($badge, $ass)
                )
            ),
            'awarded_by' => $awarded_by,
        ];

        return $record;
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getRecords());
    }

    /**
     * @return list<array{
     *     id: int,
     *     title_sortable: string,
     *     awarded_by_sortable: string,
     *     badge_issued_on: DateTimeImmutable,
     *     active: bool,
     *     assignment: ilBadgeAssignment,
     *     badge: ilBadge
     *  }>
     */
    private function getRecords(): array
    {
        if ($this->cached_records !== null) {
            return $this->cached_records;
        }

        $rows = [];
        $a_user_id = $this->user->getId();

        foreach (ilBadgeAssignment::getInstancesByUserId($a_user_id) as $ass) {
            $badge = new ilBadge($ass->getBadgeId());

            $parent = null;
            if ($badge->getParentId()) {
                if (isset($this->parent_metadata_cache[$badge->getParentId()])) {
                    $parent = $this->parent_metadata_cache[$badge->getParentId()];
                } else {
                    $parent = $badge->getParentMeta();
                    $this->parent_metadata_cache[$badge->getParentId()] = $parent;
                }
                if ($parent['type'] === 'bdga') {
                    $parent = null;
                }
            }

            $awarded_by_sortable = '';
            if ($parent !== null) {
                $awarded_by_sortable = $parent['title'];
            }

            $rows[] = [
                'id' => $badge->getId(),
                'title_sortable' => $badge->getTitle(),
                'awarded_by_sortable' => $awarded_by_sortable,
                'badge_issued_on' => (new DateTimeImmutable())
                    ->setTimestamp($ass->getTimestamp())
                    ->setTimezone(new DateTimeZone($this->user->getTimeZone())),
                'active' => (bool) $ass->getPosition(),
                'assignment' => $ass,
                'badge' => $badge
            ];
        }

        $this->cached_records = $rows;

        return $rows;
    }

    /**
     * @return array<string, Column>
     */
    private function getColumns(\ILIAS\Data\DateFormat\DateFormat $date_format): array
    {
        return [
            'image' => $this->factory->table()->column()->text($this->lng->txt('image'))->withIsSortable(false),
            'title' => $this->factory->table()->column()->text($this->lng->txt('title')),
            'awarded_by' => $this->factory->table()->column()->text($this->lng->txt('awarded_by')),
            'badge_issued_on' => $this->factory->table()->column()->date(
                $this->lng->txt('badge_issued_on'),
                $date_format
            ),
            'active' => $this->factory->table()->column()->boolean(
                $this->lng->txt('badge_in_profile'),
                $this->lng->txt('yes'),
                $this->lng->txt('no')
            )->withOrderingLabels(
                $this->lng->txt('badge_sort_added_to_profile_first'),
                $this->lng->txt('badge_sort_excluded_from_profile_first')
            )
        ];
    }

    /**
     * @return array<string,\ILIAS\UI\Component\Table\Action\Action>
     */
    protected function getActions(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token
    ): array {
        return [
            'obj_badge_activate' => $this->factory->table()->action()->multi(
                $this->lng->txt('badge_add_to_profile'),
                $url_builder->withParameter($action_parameter_token, 'obj_badge_activate'),
                $row_id_token
            ),
            'obj_badge_deactivate' =>
                $this->factory->table()->action()->multi(
                    $this->lng->txt('badge_remove_from_profile'),
                    $url_builder->withParameter($action_parameter_token, 'obj_badge_deactivate'),
                    $row_id_token
                )
        ];
    }

    public function renderTable(string $url): void
    {
        $df = new \ILIAS\Data\Factory();

        $table_uri = $df->uri($url);
        $url_builder = new URLBuilder($table_uri);
        $query_params_namespace = ['badge'];

        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'id',
        );

        $table = $this->factory
            ->table()
            ->data(
                $this,
                $this->lng->txt('badge_personal_badges'),
                $this->getColumns($this->user->getDateTimeFormat()),
            )
            ->withId(str_replace('\\', '', self::class))
            ->withOrder(new Order('title', Order::ASC))
            ->withRange(new Range(0, 50))
            ->withActions($this->getActions($url_builder, $action_parameter_token, $row_id_token))
            ->withRequest($this->request);

        $pres = new PresentationHeader($this->dic, ilBadgeProfileGUI::class);
        $pres->show($this->lng->txt('table_view'));

        $content_wrapper = new TableContentWrapper($this->renderer, $this->factory);
        $this->tpl->setContent($this->renderer->render(
            $content_wrapper->wrap(
                $table
            )
        ));
    }
}
