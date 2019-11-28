<?php

use ILIAS\DI\Container;

/**
 * Class ilMStListCompetencesSkills
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilMStListCompetencesSkills
{

    /**
     * @var Container
     */
    protected $dic;


    /**
     * ilMStListCompetencesSkills constructor.
     *
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }


    /**
     * @param array $user_ids
     *
     * @param array $options
     *
     * @return int|array
     */
    public function getData(array $user_ids, array $options)
    {
        $select = $options['count'] === true ?
            'SELECT count(*)' : 'SELECT sktree.title as skill_title, skill_node_id, user_id, login, firstname, lastname, lvl.title as skill_level';

        $query = $select .
            ' FROM skl_personal_skill sk ' .
            ' INNER JOIN usr_data ud ON ud.usr_id = sk.user_id ' .
            ' INNER JOIN skl_tree_node sktree ON sktree.obj_id = sk.skill_node_id ' .
            ' INNER JOIN (SELECT  skill_id, MAX(level_id) AS level_id ' .
                ' FROM skl_user_has_level WHERE self_eval = 0 GROUP BY skill_id) ulvl ON sk.skill_node_id = ulvl.skill_id ' .
            ' INNER JOIN skl_level lvl ON lvl.id = ulvl.level_id ' .
            ' WHERE sk.user_id IN (' . implode(',', $user_ids) . ')' .
            $this->getAdditionalWhereStatement($options['filters']);


        if ($options['count'] === true) {
            $set = $this->dic->database()->query($query);
            return $this->dic->database()->numRows($set);
        }

        if ($options['sort']) {
            $query .= " ORDER BY " . $options['sort']['field'] . " " . $options['sort']['direction'];
        }

        if (isset($options['limit']['start']) && isset($options['limit']['end'])) {
            $query .= " LIMIT " . $options['limit']['start'] . "," . $options['limit']['end'];
        }

        $set = $this->dic->database()->query($query);

        $skills = [];
        while ($rec = $this->dic->database()->fetchAssoc($set)) {
            $skills[] = new ilMStListCompetencesSkill(
                $rec['skill_title'],
                $rec['skill_level'],
                $rec['login'],
                $rec['lastname'],
                $rec['firstname'],
                $rec['user_id']
            );
        }
        return $skills;
    }


    /**
     * @param array $filters
     *
     * @return string
     */
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
            $wheres[] = "(" . $this->dic->database()->like("ud.login", "text", "%" . $filters['user'] . "%") . " " . "OR " . $this->dic->database()
                    ->like("ud.firstname", "text", "%" . $filters['user'] . "%") . " " . "OR " . $this->dic->database()
                    ->like("ud.lastname", "text", "%" . $filters['user'] . "%") . " " . "OR " . $this->dic->database()
                    ->like("ud.email", "text", "%" . $filters['user'] . "%") . ") ";
        }

        if (!empty($arr_filter['org_unit'])) {
            $wheres[] = 'ud.usr_id IN (SELECT user_id FROM il_orgu_ua WHERE orgu_id = ' .
                $this->dic->database()->quote($filters['org_unit'], 'integer') . ')';
		}

        return empty($wheres) ? '' : ' AND ' . implode (' AND ', $wheres);
    }
}