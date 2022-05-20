<?php

use ILIAS\DI\Container;
use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\Services\MyStaff\Utils\ListFetcherResult;

/**
 * Class ilMStListCompetencesSkills
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilMStListCompetencesSkills
{
    protected Container $dic;

    /**
     * ilMStListCompetencesSkills constructor.
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }

    /**
     * @param int[] $options
     * @return ilMStListCompetencesSkill[]
     */
    final public function getData(array $options) : ListFetcherResult
    {
        //Permission Filter
        $operation_access = ilOrgUnitOperation::OP_VIEW_COMPETENCES;

        $select = $options['count'] === true ?
            'SELECT count(*)' : 'SELECT sktree.title as skill_title, skill_node_id, ulvl.trigger_obj_id, user_id, login, firstname, lastname, lvl.title as skill_level';

        $query = $select .
            ' FROM skl_personal_skill sk ' .
            ' INNER JOIN usr_data ud ON ud.usr_id = sk.user_id ' .
            ' INNER JOIN skl_tree_node sktree ON sktree.obj_id = sk.skill_node_id ' .
            ' INNER JOIN (SELECT trigger_obj_id, skill_id, MAX(level_id) AS level_id ' .
            ' FROM skl_user_has_level WHERE self_eval = 0 GROUP BY skill_id) ulvl ON sk.skill_node_id = ulvl.skill_id ' .
            ' INNER JOIN skl_level lvl ON lvl.id = ulvl.level_id ' .
            ' WHERE ';

        $users_per_position = ilMyStaffAccess::getInstance()->getUsersForUserPerPosition($this->dic->user()->getId());

        if (empty($users_per_position)) {
            return new ListFetcherResult([], 0);
        }

        $arr_query = [];
        foreach ($users_per_position as $position_id => $users) {
            $obj_ids = ilMyStaffAccess::getInstance()->getIdsForUserAndOperation(
                $this->dic->user()->getId(),
                $operation_access
            );
            $arr_query[] = $query . $this->dic->database()->in(
                'ulvl.trigger_obj_id',
                $obj_ids,
                false,
                'integer'
            ) . " AND " . $this->dic->database()->in('sk.user_id ', $users, false, 'integer')
                . $this->getAdditionalWhereStatement($options['filters']);
        }

        $union_query = "SELECT * FROM ((" . implode(') UNION (', $arr_query) . ")) as a_table";

        $set = $this->dic->database()->query($union_query);
        $numRows = $this->dic->database()->numRows($set);

        if ($options['sort']) {
            $union_query .= " ORDER BY " . $options['sort']['field'] . " " . $options['sort']['direction'];
        }

        if (isset($options['limit']['start']) && isset($options['limit']['end'])) {
            $union_query .= " LIMIT " . $options['limit']['start'] . "," . $options['limit']['end'];
        }

        $set = $this->dic->database()->query($union_query);

        $skills = [];
        while ($rec = $this->dic->database()->fetchAssoc($set)) {
            $skills[] = new ilMStListCompetencesSkill(
                $rec['skill_title'],
                $rec['skill_level'],
                $rec['login'],
                $rec['lastname'],
                $rec['firstname'],
                intval($rec['user_id'])
            );
        }

        return new ListFetcherResult($skills, $numRows);
    }

    protected function getAdditionalWhereStatement(array $filters) : string
    {
        $wheres = [];

        if (!empty($filters['skill'])) {
            $wheres[] = "sktree.title LIKE '%" . $filters['skill'] . "%'";
        }

        if (!empty($filters['skill_level'])) {
            $wheres[] = "lvl.title LIKE '%" . $filters['skill_level'] . "%'";
        }

        if (!empty($filters['user'])) {
            $wheres[] = "(" . $this->dic->database()->like(
                "ud.login",
                "text",
                "%" . $filters['user'] . "%"
            ) . " " . "OR " . $this->dic->database()
                                                                            ->like(
                                                                                "ud.firstname",
                                                                                "text",
                                                                                "%" . $filters['user'] . "%"
                                                                            ) . " " . "OR " . $this->dic->database()
                                                                                                                                        ->like(
                                                                                                                                            "ud.lastname",
                                                                                                                                            "text",
                                                                                                                                            "%" . $filters['user'] . "%"
                                                                                                                                        ) . " " . "OR " . $this->dic->database()
                                                                                                                                                                                                    ->like(
                                                                                                                                                                                                        "ud.email",
                                                                                                                                                                                                        "text",
                                                                                                                                                                                                        "%" . $filters['user'] . "%"
                                                                                                                                                                                                    ) . ") ";
        }

        if (!empty($arr_filter['org_unit'])) {
            $wheres[] = 'ud.usr_id IN (SELECT user_id FROM il_orgu_ua WHERE orgu_id = ' .
                $this->dic->database()->quote($filters['org_unit'], 'integer') . ')';
        }

        return empty($wheres) ? '' : ' AND ' . implode(' AND ', $wheres);
    }
}
