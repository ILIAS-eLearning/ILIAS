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

        foreach ($records as $record) {
            yield $row_builder->buildDataRow((string) $record['id'], $record);
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return count($this->getRecords());
    }

    /**
     * @return list<array{
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
                $parent = $badge->getParentMeta();
                if ($parent['type'] === 'bdga') {
                    $parent = null;
                }
            }

            $awarded_by = '';
            $awarded_by_sortable = '';
            if ($parent !== null) {
                $ref_ids = ilObject::_getAllReferences($parent['id']);
                $ref_id = current($ref_ids);

                $awarded_by = $parent['title'];
                $awarded_by_sortable = $parent['title'];
                if ($ref_id && $this->access->checkAccess('read', '', $ref_id)) {
                    $awarded_by = $this->renderer->render(
                        new Standard(
                            $awarded_by,
                            (string) new URI(ilLink::_getLink($ref_id))
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

            $rows[] = [
                'id' => $badge->getId(),
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
                'title_sortable' => $badge->getTitle(),
                'badge_issued_on' => (new DateTimeImmutable())
                    ->setTimestamp($ass->getTimestamp())
                    ->setTimezone(new DateTimeZone($this->user->getTimeZone())),
                'awarded_by' => $awarded_by,
                'awarded_by_sortable' => $awarded_by_sortable,
                'active' => (bool) $ass->getPosition()
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

    public function renderTable(): void
    {
        $df = new \ILIAS\Data\Factory();

        $table_uri = $df->uri($this->request->getUri()->__toString());
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
            ->withId(self::class)
            ->withOrder(new Order('title', Order::ASC))
            ->withActions($this->getActions($url_builder, $action_parameter_token, $row_id_token))
            ->withRequest($this->request);

        $pres = new PresentationHeader($this->dic, ilBadgeProfileGUI::class);
        $pres->show($this->lng->txt('table_view'));

        $this->tpl->setContent($this->renderer->render($table));
    }
}
