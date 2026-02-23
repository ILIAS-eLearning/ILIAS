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
use Psr\Http\Message\RequestInterface;
use ILIAS\UI\Component\Table\DataRowBuilder;
use Generator;
use ILIAS\UI\Component\Table\DataRetrieval;
use ilBadgeHandler;
use ilObject;
use ilBadge;
use ilBadgeAssignment;
use ilUserQuery;
use DateTimeImmutable;
use ILIAS\UI\URLBuilderToken;
use ilObjectDataDeletionLog;
use ilTree;
use ilObjUser;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\Action\Action;

class ilBadgeUserTableGUI implements DataRetrieval
{
    private readonly Factory $factory;
    private readonly Renderer $renderer;
    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilTree $tree;
    private readonly ilObjUser $user;
    private DateFormat $date_format;
    private bool $is_container_context = false;
    /**
     * @var null|list<array{
     *     id: string,
     *     name: string,
     *     login: string,
     *     type: string,
     *     title: string,
     *     issued: ?DateTimeImmutable,
     *     issued_sortable: ?DateTimeImmutable,
     *     parent: ?string,
     *     parent_sortable: ?string
     *  }>
     */
    private ?array $cached_records = null;

    public function __construct(
        private readonly ?int $parent_ref_id = null,
        private readonly ?ilBadge $award_badge = null,
        private readonly ?int $parent_obj_id = null,
        private readonly ?int $restrict_badge_id = null
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();

        if ($this->parent_ref_id) {
            $parent_type = ilObject::_lookupType($this->parent_ref_id, true);
            if (\in_array($parent_type, ['grp', 'crs'], true)) {
                $this->is_container_context = $this->parent_obj_id === null && $this->award_badge === null;
            }
        }
    }

    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     login: string,
     *     type: string,
     *     title: string,
     *     issued: ?DateTimeImmutable,
     *     issued_sortable: ?DateTimeImmutable,
     *     parent: ?string,
     *     parent_sortable: ?string
     *  }>
     */
    private function getRecords(): array
    {
        if ($this->cached_records !== null) {
            return $this->cached_records;
        }

        /** @var array<int, list<ilBadgeAssignment>> $assignments */
        $assignments = [];
        $user_ids = [];
        $rows = [];
        $badges = [];

        $parent_obj_id = $this->parent_obj_id;
        if (!$parent_obj_id && $this->parent_ref_id) {
            $parent_obj_id = ilObject::_lookupObjId($this->parent_ref_id);
        }

        if ($this->parent_ref_id) {
            $user_ids = ilBadgeHandler::getInstance()->getUserIds($this->parent_ref_id, $parent_obj_id);
        }

        $obj_ids = [$parent_obj_id];
        if ($this->is_container_context) {
            foreach ($this->tree->getSubTree($this->tree->getNodeData($this->parent_ref_id)) as $node) {
                $obj_ids[] = (int) $node['obj_id'];
            }
            $obj_ids = array_unique($obj_ids);
        }

        foreach ($obj_ids as $obj_id) {
            foreach (ilBadge::getInstancesByParentId($obj_id) as $badge) {
                $badges[$badge->getId()] = $badge;
            }

            foreach (ilBadgeAssignment::getInstancesByParentId($obj_id) as $ass) {
                if ($this->restrict_badge_id && $this->restrict_badge_id !== $ass->getBadgeId()) {
                    continue;
                }

                if ($this->award_badge instanceof ilBadge &&
                    $ass->getBadgeId() !== $this->award_badge->getId()) {
                    continue;
                }

                $assignments[$ass->getUserId()][] = $ass;
            }
        }

        if (!$user_ids) {
            $user_ids = array_keys($assignments);
        }

        $tmp['set'] = [];
        if (\count($user_ids) > 0) {
            $uquery = new ilUserQuery();
            $uquery->setLimit(9999);
            $uquery->setUserFilter($user_ids);
            $tmp = $uquery->query();
        }

        foreach ($tmp['set'] as $user) {
            if (\array_key_exists($user['usr_id'], $assignments)) {
                foreach ($assignments[$user['usr_id']] as $user_ass) {
                    $idx = $user_ass->getBadgeId() . '_' . $user_ass->getUserId();

                    $badge = $badges[$user_ass->getBadgeId()];

                    $parent = null;
                    $paren_sortable = null;
                    if ($this->is_container_context) {
                        $parent_metadata = $badge->getParentMeta();

                        $parent = implode(' ', [
                            $this->renderer->render(
                                $this->factory->symbol()->icon()->custom(
                                    ilObject::_getIcon($parent_metadata['id'], 'big', $parent_metadata['type']),
                                    $this->lng->txt('obj_' . $parent_metadata['type'])
                                )
                            ),
                            $parent_metadata['title']
                        ]);
                        $paren_sortable = $parent_metadata['title'];
                    }

                    $rows[] = [
                        'id' => $idx,
                        'name' => $user['lastname'] . ', ' . $user['firstname'],
                        'login' => $user['login'],
                        'type' => ilBadge::getExtendedTypeCaption($badge->getTypeInstance()),
                        'title' => $badge->getTitle(),
                        'issued' => (new DateTimeImmutable())
                            ->setTimestamp($user_ass->getTimestamp())
                            ->setTimezone(new \DateTimeZone($this->user->getTimeZone()))
                            ->format($this->date_format->toString()),
                        'issued_sortable' => (new DateTimeImmutable())
                            ->setTimestamp($user_ass->getTimestamp())
                            ->setTimezone(new \DateTimeZone($this->user->getTimeZone())),
                        'parent' => $parent,
                        'parent_sortable' => $paren_sortable,
                    ];
                }
            } elseif ($this->award_badge) {
                $idx = $this->award_badge->getId() . '_' . $user['usr_id'];

                $rows[] = [
                    'id' => $idx,
                    'name' => $user['lastname'] . ', ' . $user['firstname'],
                    'login' => $user['login'],
                    'type' => '',
                    'title' => '',
                    'issued' => null,
                    'issued_sortable' => null,
                    'parent' => null,
                    'parent_sortable' => null,
                ];
            }
        }

        $this->cached_records = $rows;

        return $rows;
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
                if (\in_array($order_field, ['name', 'login', 'type', 'title', 'parent'], true)) {
                    if ($order_field === 'parent') {
                        $order_field .= '_sortable';
                    }

                    return \ilStr::strCmp(
                        $left[$order_field] ?? '',
                        $right[$order_field] ?? ''
                    );
                }

                if ($order_field === 'issued') {
                    $order_field .= '_sortable';
                    return $left[$order_field] <=> $right[$order_field];
                }

                return $left[$order_field] <=> $right[$order_field];
            });

            if ($order_direction === ORDER::DESC) {
                $records = array_reverse($records);
            }
        }

        if ($range) {
            $records = \array_slice($records, $range->getStart(), $range->getLength());
        }

        foreach ($records as $record) {
            yield $row_builder->buildDataRow($record['id'], $record)->withDisabledAction(
                'badge_award_badge',
                $record['issued'] === null
            )->withDisabledAction(
                'badge_revoke_badge',
                $record['issued'] !== null
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
     * @return array<string, Column>
     */
    public function getColumns(): array
    {
        $columns = [
            'name' => $this->factory->table()->column()->text($this->lng->txt('name')),
            'login' => $this->factory->table()->column()->text($this->lng->txt('login')),
            'type' => $this->factory->table()->column()->text($this->lng->txt('type')),
            'title' => $this->factory->table()->column()->text($this->lng->txt('title')),
            // Cannot be a date column, because when awarding/revoking badges for uses, the list items may contain NULL values for `issued`
            'issued' => $this->factory->table()->column()->text($this->lng->txt('badge_issued_on'))
        ];

        if ($this->is_container_context) {
            $columns['parent'] = $this->factory->table()->column()->text($this->lng->txt('object'));
        }

        return $columns;
    }

    /**
     * @return array<string, Action>
     */
    private function getActions(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token,
    ): array {
        return ($this->award_badge instanceof ilBadge) ? [
            'badge_award_badge' =>
                $this->factory->table()->action()->multi(
                    $this->lng->txt('badge_award_badge'),
                    $url_builder->withParameter($action_parameter_token, 'assignBadge'),
                    $row_id_token
                ),
            'badge_revoke_badge' =>
                $this->factory->table()->action()->multi(
                    $this->lng->txt('badge_remove_badge'),
                    $url_builder->withParameter($action_parameter_token, 'revokeBadge'),
                    $row_id_token
                )
        ] : [];
    }

    public function renderTable(string $url): void
    {
        $df = new \ILIAS\Data\Factory();
        $this->date_format = $this->user->getDateTimeFormat();

        $table_uri = $df->uri($url);
        $url_builder = new URLBuilder($table_uri);
        $query_params_namespace = ['tid'];

        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'id',
        );

        if ($this->award_badge instanceof ilBadge) {
            $title = $this->lng->txt('badge_award_badge') . ': ' . $this->award_badge->getTitle();
        } else {
            $parent = '';
            if ($this->parent_obj_id) {
                $title = ilObject::_lookupTitle($this->parent_obj_id);
                if (!$title) {
                    $title = ilObjectDataDeletionLog::get($this->parent_obj_id);
                    if ($title) {
                        $title = $title['title'];
                    }
                }

                if ($this->restrict_badge_id) {
                    $badge = new ilBadge($this->restrict_badge_id);
                    $title .= ' - ' . $badge->getTitle();
                }

                $parent = $title . ': ';
            }
            $title = $parent . $this->lng->txt('users');
        }

        $table = $this->factory
            ->table()
            ->data($this, $title, $this->getColumns())
            ->withId(str_replace('\\', '', self::class) . '_' . $this->parent_ref_id)
            ->withOrder(new Order('name', Order::ASC))
            ->withRange(new Range(0, 100))
            ->withActions($this->getActions($url_builder, $action_parameter_token, $row_id_token))
            ->withRequest($this->request);

        $out = [$table];

        $this->tpl->setContent($this->renderer->render($out));
    }
}
