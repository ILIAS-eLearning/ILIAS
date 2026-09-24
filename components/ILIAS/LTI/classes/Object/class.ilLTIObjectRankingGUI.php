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

/**
 * Ranking of the users of an LTI object by the score its tool sent as xAPI statements: the best users,
 * the neighbourhood of the current user or both, as the object is set up.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIObjectRankingGUI
{
    public const string CMD_SHOW = 'show';

    /**
     * How many users around the current one the own ranking shows, the user included.
     */
    private const int OWN_RANKING_SIZE = 5;

    private readonly ILIAS\DI\Container $dic;

    public function __construct(private readonly ilObjLTIConsumer $object)
    {
        global $DIC;

        $this->dic = $DIC;
    }

    /**
     * @throws ilObjectException
     */
    public function executeCommand(): void
    {
        if (!ilObjLTIConsumerAccess::hasRankingAccess($this->object)) {
            throw new ilObjectException('no access to the ranking of this object');
        }

        $this->show();
    }

    /**
     * An LRS that cannot be reached is reported, with an empty ranking.
     */
    private function show(): void
    {
        $lng = $this->dic->language();
        $lng->loadLanguageModule('assessment');

        $rows = [];
        $user_rank = null;
        try {
            $filter = new ilCmiXapiStatementsReportFilter();
            $filter->setActivityId($this->object->getActivityId());
            $request = new ilCmiXapiHighscoreReportRequest(
                $this->object->getTool()->getXapiBasicAuth(),
                new ilCmiXapiHighscoreReportLinkBuilder(
                    $this->object->getId(),
                    $this->object->getTool()->getXapiAggregateEndpoint(),
                    $filter
                )
            );
            $report = $request->queryReport($this->object->getId());
            if ($report->initTableData()) {
                $rows = $report->getTableData();
                $user_rank = $report->getUserRank();
            }
        } catch (Exception $e) {
            $this->dic->ui()->mainTemplate()->setOnScreenMessage('failure', $e->getMessage());
        }

        $tables = [];
        if ($this->object->getHighscoreEnabled() && $this->object->getHighscoreTopTable()) {
            $tables[] = $this->buildTable(
                'top',
                $lng->txt('highscore_top_table'),
                array_slice($rows, 0, $this->object->getHighscoreTopNum())
            );
        }
        if ($this->object->getHighscoreEnabled() && $this->object->getHighscoreOwnTable() && $user_rank !== null) {
            $tables[] = $this->buildTable(
                'own',
                $lng->txt('highscore_own_table'),
                array_slice($rows, max(0, $user_rank - 2), self::OWN_RANKING_SIZE)
            );
        }

        $this->dic->ui()->mainTemplate()->setContent($this->dic->ui()->renderer()->render($tables));
    }

    private function buildTable(string $id, string $title, array $rows): ILIAS\UI\Component\Table\Data
    {
        $lng = $this->dic->language();
        $column = $this->dic->ui()->factory()->table()->column();

        $columns = [
            'rank' => $column->number($lng->txt('toplist_col_rank'))->withIsSortable(false),
            'participant' => $column->text($lng->txt('toplist_col_participant'))->withIsSortable(false),
        ];
        if ($this->object->getHighscoreAchievedTS()) {
            $columns['date'] = $column->text($lng->txt('toplist_col_achieved'))->withIsSortable(false);
        }
        if ($this->object->getHighscorePercentage()) {
            $columns['percentage'] = $column->number($lng->txt('toplist_col_percentage'))
                ->withDecimals(2)
                ->withUnit('%')
                ->withIsSortable(false);
        }
        if ($this->object->getHighscoreWTime()) {
            $columns['duration'] = $column->text($lng->txt('toplist_col_wtime'))->withIsSortable(false);
        }

        return $this->dic->ui()->factory()->table()
            ->data(new ilLTIObjectRankingTable($this->prepareRows($rows)), $title, $columns)
            ->withId('lti_ranking_' . $id)
            ->withRequest($this->dic->http()->request());
    }

    /**
     * The name of a participant is only resolved for who may read the outcomes, the others see the
     * name the report gives, which is only the one of the current user.
     */
    private function prepareRows(array $rows): array
    {
        $resolve_names = ilObjLTIConsumerAccess::hasOutcomesAccess($this->object);

        return array_map(function (array $row) use ($resolve_names): array {
            $participant = (string) $row['user'];
            if ($resolve_names) {
                $user = ilObjectFactory::getInstanceByObjId((int) $row['ilias_user_id'], false);
                $participant = $user instanceof ilObjUser
                    ? $user->getFullname()
                    : $this->dic->language()->txt('deleted_user');
            }

            return [
                'rank' => (int) $row['rank'],
                'participant' => $participant,
                'date' => (string) $row['date'],
                'percentage' => 100 * (float) $row['score'],
                'duration' => (string) $row['duration'],
            ];
        }, array_values($rows));
    }
}
