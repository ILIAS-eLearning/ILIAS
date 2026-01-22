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

namespace ILIAS\Contact\BuddySystem\Tables;

use ILIAS\Contact\TableRows;
use ilBuddySystemArrayCollection;
use ILIAS\UI\Component\Component;
use ilBuddySystemRelationState;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\Data\Range;
use ilObjUser;
use ilStr;
use ilUserUtil;
use ilBuddyList;
use ilBuddySystemRelationStateFactory;
use ilBuddySystemRelation;
use ilLanguage;
use ILIAS\UI\Factory as UIFactory;
use ilUIService;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use Closure;
use ILIAS\HTTP\GlobalHttpState as Http;

/**
 * @phpstan-type RelationRecord array{
 *     user_id: int,
 *     public_name: string,
 *     login: string,
 *     state: ilBuddySystemRelationState,
 *     target_states: list<ilBuddySystemRelationState>,
 *     state_text: string
 * }
 */
class RelationsTable
{
    /**
     * @param Closure(int, string): string $link_to_profile
     */
    public function __construct(
        private readonly UIFactory $create,
        private readonly ilLanguage $lng,
        private readonly ilUIService $ui_service,
        private readonly Http $http,
        private readonly Closure $link_to_profile,
    ) {
    }

    /**
     * @param array{state?: string, name?: string} $filter
     * @return list<RelationRecord>
     */
    public static function data(array $filter = []): array
    {
        $relations = ilBuddyList::getInstanceByGlobalUser()->getRelations();

        $state_filter = (string) ($filter['state'] ?? '');
        $state_factory = ilBuddySystemRelationStateFactory::getInstance();

        if ($state_filter) {
            $relations = $relations->filter(static fn(ilBuddySystemRelation $relation): bool => (
                $state_factory
                    ->getTableFilterStateMapper($relation->getState())
                    ->filterMatchesRelation($state_filter, $relation)
            ));
        }

        $public_names = ilUserUtil::getNamePresentation($relations->getKeys(), false, false, '', false, true, false);
        /** @var array<int, string> $logins */
        $logins = ilUserUtil::getNamePresentation($relations->getKeys(), false, false, '', false, false, false);

        $logins = array_map(static function (string $value): string {
            $matches = null;
            preg_match_all('/\[([^\[]+?)\]/', $value, $matches);
            return $matches[1][\count($matches[1]) - 1] ?? '';
        }, $logins);

        $public_name_query = (string) ($filter['name'] ?? '');
        if ($public_name_query) {
            $relations = $relations->filter(self::filter($public_name_query, $relations, $public_names, $logins));
        }

        $data = [];
        foreach ($relations->toArray() as $user_id => $relation) {
            $txt = $state_factory->getTableFilterStateMapper($relation->getState())->text($relation);
            $data[] = [
                'user_id' => $user_id,
                'public_name' => $public_names[$user_id],
                'login' => $logins[$user_id],
                'state' => $relation->getState(),
                'target_states' => $relation->getCurrentPossibleTargetStates()->toArray(),
                'state_text' => $txt,
            ];
        }

        return $data;
    }

    /**
     * @param array<string, Action> $multi_actions
     * @param callable(string, string, string): Action $action
     * @return list<Component>
     */
    public function build(array $multi_actions, string $target_url, callable $action): array
    {
        $filter = $this->filterComponent($target_url);
        $data = static::data($this->ui_service->filter()->getData($filter) ?: []);
        $single_actions = $this->actions($data, $action);

        $components = [
            $filter
        ];
        $components[] = $this->create
            ->table()
            ->data(
                new TableRows($data, array_keys($single_actions), $this->link_to_profile),
                $this->lng->txt('buddy_tbl_title_relations'),
                [
                    'public_name' => $this->create->table()->column()->text($this->lng->txt('name')),
                    'login' => $this->create->table()->column()->text($this->lng->txt('login')),
                    'state_text' => $this->create
                        ->table()->column()
                        ->text($this->lng->txt('buddy_tbl_state_actions_col_label')),
                ]
            )
            ->withId('buddy_relations_table')
            ->withRequest($this->http->request())
            ->withRange(new Range(0, 50))
            ->withActions(
                array_merge($single_actions, $multi_actions)
            );

        return $components;
    }

    /**
     * @template A
     * @param ilBuddySystemArrayCollection<int, A> $relations
     * @param array<int, string> $public_names
     * @param array<int, string> $logins
     * @return Closure(A): bool
     */
    private static function filter(string $public_name_query, ilBuddySystemArrayCollection $relations, array $public_names, array $logins): Closure
    {
        $in_string = static fn(string $needle, string $haystack): bool => ilStr::strpos(
            ilStr::strtolower($haystack),
            ilStr::strtolower($needle),
            0
        ) !== false;

        return self::pipe($relations->getKey(...), static fn(int $user_id): bool => (
            $in_string($public_name_query, $public_names[$user_id]) ||
            $in_string($public_name_query, $logins[$user_id])
        ) && ilObjUser::_lookupActive($user_id));
    }

    private static function pipe(Closure $a, Closure $b): Closure
    {
        return static fn($x) => $b($a($x));
    }

    /**
     * @param list<RelationRecord> $data
     * @param callable(string, string, string): Action $action
     *
     * @return array<string, Action>
     */
    private function actions(array $data, callable $action): array
    {
        $actions = [];
        foreach ($data as $row) {
            foreach ($row['target_states'] as $state) {
                $actions[$row['state'] . '->' . $state] = $action(
                    'single',
                    'buddy_bs_act_btn_txt_' . $row['state']->getSnakeName() . '_to_' . $state->getSnakeName(),
                    $state->getAction()
                );
            }
        }

        return $actions;
    }

    private function filterComponent(string $target_url): Filter
    {
        $state_factory = ilBuddySystemRelationStateFactory::getInstance();
        $options = array_merge(...array_map(
            static fn($m): array => $m->optionsForState(),
            array_map(
                $state_factory->getTableFilterStateMapper(...),
                array_filter($state_factory->getValidStates(), static fn($s): bool => !$s->isInitial())
            )
        ));

        $fields = [
            'state' => $this->create->input()->field()->select($this->lng->txt('buddy_tbl_filter_state'), $options),
            'name' => $this->create->input()->field()->text($this->lng->txt('name')),
        ];

        return $this->ui_service->filter()->standard(
            'contact-filter',
            $target_url,
            $fields,
            array_map(static fn(): bool => true, $fields),
            true,
            true
        );
    }
}
