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
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

/**
 * The xAPI statements the tool of an LTI object sent to its LRS, queried page by page through the
 * statement reports of CmiXapi. A user without the permission to read the outcomes only sees their
 * own statements.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIToolXapiStatementsGUI implements DataRetrieval
{
    public const string CMD_SHOW = 'show';

    private const string TABLE_ID = 'lti_statements';
    private const string ACTION_RAW = 'raw';
    /**
     * The raw statements of the page shown last, for the action that displays them.
     */
    private const string SESSION_RAW = 'lti_statements_raw';

    private readonly ILIAS\DI\Container $dic;
    private readonly URLBuilder $url_builder;
    private readonly URLBuilderToken $action_token;
    private readonly URLBuilderToken $id_token;
    private bool $failure_shown = false;

    public function __construct(private readonly ilObjLTITool $object)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->dic->language()->loadLanguageModule('cmix');
        $url_builder = new URLBuilder(new DataFactory()->uri((string) $DIC->http()->request()->getUri()));
        [$this->url_builder, $this->action_token, $this->id_token] = $url_builder->acquireParameters(
            ['lti', 'statements'],
            'action',
            'ids'
        );
    }

    /**
     * @throws ilObjectException
     */
    public function executeCommand(): void
    {
        if (!ilObjLTIToolAccess::hasStatementsAccess($this->object)) {
            throw new ilObjectException('no access to the statements of this object');
        }

        $query = $this->dic->http()->request()->getQueryParams();
        if (($query[$this->action_token->getName()] ?? '') === self::ACTION_RAW) {
            $this->showRawStatement((string) (((array) ($query[$this->id_token->getName()] ?? []))[0] ?? ''));
            return;
        }

        $this->show();
    }

    private function show(): void
    {
        $lng = $this->dic->language();
        $column = $this->dic->ui()->factory()->table()->column();
        $filter = $this->buildFilter();

        $columns = ['date' => $column->date($lng->txt('tbl_statements_date'), $this->dic->user()->getDateTimeFormat())];
        if (ilObjLTIToolAccess::hasOutcomesAccess($this->object)) {
            $columns['actor'] = $column->text($lng->txt('tbl_statements_actor'));
        }
        $columns['verb'] = $column->text($lng->txt('tbl_statements_verb'));
        $columns['object'] = $column->text($lng->txt('tbl_statements_object'));

        $table = $this->dic->ui()->factory()->table()->data($this, $lng->txt('tab_statements'), $columns)
            ->withId(self::TABLE_ID)
            ->withOrder(new Order('date', Order::DESC))
            ->withRange(new Range(0, 20))
            ->withActions([
                self::ACTION_RAW => $this->dic->ui()->factory()->table()->action()->single(
                    $lng->txt('tbl_action_raw_data'),
                    $this->url_builder->withParameter($this->action_token, self::ACTION_RAW),
                    $this->id_token
                )->withAsync(),
            ])
            ->withFilter($this->dic->uiService()->filter()->getData($filter))
            ->withRequest($this->dic->http()->request());

        $this->dic->ui()->mainTemplate()->setContent($this->dic->ui()->renderer()->render([$filter, $table]));
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
        [$field, $direction] = $order->join([], static fn(array $result, string $key, string $value): array => [$key, $value]);
        $report = $this->queryReport($filter_data, $range->getStart(), $range->getLength(), $field, $direction);

        $raw = [];
        foreach ($report?->getTableData() ?? [] as $index => $statement) {
            $id = (string) (json_decode($statement['statement'], true)['id'] ?? $range->getStart() + $index);
            $raw[$id] = $statement['statement'];

            yield $row_builder->buildDataRow($id, [
                'date' => new DateTimeImmutable($statement['date']),
                'actor' => $this->getActorName($statement['actor']),
                'verb' => ilCmiXapiVerbList::getVerbTranslation($this->dic->language(), $statement['verb_id']),
                'object' => trim($statement['object'] . ' ' . $statement['object_info']),
            ]);
        }
        ilSession::set(self::SESSION_RAW, $raw);
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return $this->queryReport($filter_data, 0, 1, 'date', Order::DESC)?->getMaxCount() ?? 0;
    }

    /**
     * An LRS that cannot be reached is reported once, and leaves the table empty.
     */
    private function queryReport(mixed $filter_data, int $offset, int $limit, string $order_field, string $direction): ?ilCmiXapiStatementsReport
    {
        try {
            $filter = new ilCmiXapiStatementsReportFilter();
            $filter->setActivityId($this->object->getActivityId());
            $filter->setOffset($offset);
            $filter->setLimit($limit);
            $filter->setOrderField($order_field);
            $filter->setOrderDirection(strtolower($direction));
            $this->applyFilterData($filter, is_array($filter_data) ? $filter_data : []);

            $request = new ilCmiXapiStatementsReportRequest(
                $this->object->getTool()->getXapiBasicAuth(),
                new ilCmiXapiStatementsReportLinkBuilder(
                    $this->object->getId(),
                    $this->object->getTool()->getXapiAggregateEndpoint(),
                    $filter
                )
            );

            return $request->queryReport($this->object->getId());
        } catch (Exception $e) {
            if (!$this->failure_shown) {
                $this->failure_shown = true;
                $this->dic->ui()->mainTemplate()->setOnScreenMessage('failure', $e->getMessage());
            }
            return null;
        }
    }

    /**
     * A user who may not read the outcomes is always filtered to their own statements.
     *
     * @throws ilCmiXapiInvalidStatementsFilterException
     */
    private function applyFilterData(ilCmiXapiStatementsReportFilter $filter, array $data): void
    {
        $privacy_ident = $this->object->getTool()->getPrivacyIdent();
        $login = trim((string) ($data['actor'] ?? ''));

        if (!ilObjLTIToolAccess::hasOutcomesAccess($this->object)) {
            $filter->setActor(new ilCmiXapiUser($this->object->getId(), $this->dic->user()->getId(), $privacy_ident));
        } elseif ($login !== '') {
            $usr_id = ilObjUser::getUserIdByLogin($login);
            if (!$usr_id) {
                throw new ilCmiXapiInvalidStatementsFilterException(
                    "given actor ({$login}) is not a valid actor for object ({$this->object->getId()})"
                );
            }
            $filter->setActor(new ilCmiXapiUser($this->object->getId(), $usr_id, $privacy_ident));
        }

        $verb = urldecode((string) ($data['verb'] ?? ''));
        if (ilCmiXapiVerbList::getInstance()->isValidVerb($verb)) {
            $filter->setVerb($verb);
        }

        [$start, $end] = $this->getPeriod($data['period'] ?? null);
        if ($start !== null) {
            $filter->setStartDate($start);
        }
        if ($end !== null) {
            $filter->setEndDate($end);
        }
    }

    /**
     * @return array start and end of the period of the filter, each null when not given
     */
    private function getPeriod(mixed $period): array
    {
        $dates = array_values(array_filter(
            is_array($period) ? $period : [],
            static fn(mixed $value): bool => $value instanceof DateTimeInterface
        ));

        return array_map(
            static fn(?DateTimeInterface $date): ?ilCmiXapiDateTime => $date === null
                ? null
                : ilCmiXapiDateTime::fromIliasDateTime(new ilDateTime($date->getTimestamp(), IL_CAL_UNIX)),
            [$dates[0] ?? null, $dates[1] ?? null]
        );
    }

    private function buildFilter(): Filter
    {
        $lng = $this->dic->language();
        $field = $this->dic->ui()->factory()->input()->field();

        $verbs = ilCmiXapiVerbList::getInstance()->getSelectOptions();
        unset($verbs['']);

        $inputs = [];
        if (ilObjLTIToolAccess::hasOutcomesAccess($this->object)) {
            $inputs['actor'] = $field->text($lng->txt('tbl_statements_actor'));
        }
        $inputs['verb'] = $field->select($lng->txt('tbl_statements_verb'), $verbs);
        $inputs['period'] = $field->duration($lng->txt('tbl_grade_period'))->withUseTime(true);

        return $this->dic->uiService()->filter()->standard(
            self::TABLE_ID,
            $this->dic->ctrl()->getLinkTarget($this, self::CMD_SHOW),
            $inputs,
            array_fill(0, count($inputs), true),
            true,
            true
        );
    }

    private function getActorName(ilCmiXapiUser $actor): string
    {
        $user = ilObjectFactory::getInstanceByObjId($actor->getUsrId(), false);

        return $user instanceof ilObjUser ? $user->getFullname() : $this->dic->language()->txt('deleted_user');
    }

    /**
     * The raw statement of a row of the page shown last, in a modal.
     */
    private function showRawStatement(string $id): void
    {
        $raw = (array) ilSession::get(self::SESSION_RAW);
        $factory = $this->dic->ui()->factory();

        echo $this->dic->ui()->renderer()->renderAsync(
            $factory->modal()->roundtrip(
                $this->dic->language()->txt('tbl_action_raw_data'),
                $factory->legacy()->content('<pre>' . htmlspecialchars((string) ($raw[$id] ?? '')) . '</pre>')
            )->withCancelButtonLabel($this->dic->language()->txt('close'))
        );
        exit();
    }
}
