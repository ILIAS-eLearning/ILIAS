<?php

use ILIAS\DI\Container;

/**
 * Class ilMStListCompetencesProfiles
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilMStListCompetencesProfiles
{
    /**
     * @var Container
     */
    protected $dic;


    /**
     * ilMStListCompetencesProfiles constructor.
     *
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }


    /**
     * @param array $user_ids
     * @param array $options
     */
    public function getData(array $user_ids, array $options)
    {
        $query = $options['count'] == true ?
            'SELECT count(*) ' :
            'SELECT 
                prof_u.user_id,
                login,
                firstname,
                lastname,
                prof.title AS profile_title,
                fulfilled_profiles.profile_id as fulfilled';

        $query .= ' FROM
                skl_profile_user prof_u
                    INNER JOIN
                usr_data ud ON ud.usr_id = prof_u.user_id
                    INNER JOIN
                skl_profile prof ON prof.id = prof_u.profile_id
                    LEFT JOIN
                    (SELECT 
                        u_prof.profile_id, u_prof.user_id
                    FROM
                        skl_profile_level prof_lvl
                    INNER JOIN skl_profile_user u_prof ON prof_lvl.profile_id = u_prof.profile_id
                    INNER JOIN (SELECT 
                        user_id, skill_id, status_date, MAX(level_id) AS level_id
                    FROM
                        skl_user_has_level
                    WHERE
                        self_eval = 0
                    GROUP BY skill_id) ulvl ON (prof_lvl.base_skill_id = ulvl.skill_id
                        AND u_prof.user_id = ulvl.user_id
                        AND prof_lvl.level_id = ulvl.level_id)) fulfilled_profiles ON (fulfilled_profiles.profile_id = prof_u.profile_id
                        AND fulfilled_profiles.user_id = prof_u.user_id)
                WHERE  ' . $this->dic->database()->in('prof_u.user_id', array_values($user_ids), false, 'integer') . ' ' .
                $this->getAdditionalWhereStatement($options['filters']);

        if ($options['count'] == true) {
            $res = $this->dic->database()->query($query);
            return $this->dic->database()->numRows($res);
        }

        if ($options['sort']) {
            $query .= " ORDER BY " . $options['sort']['field'] . " " . $options['sort']['direction'];
        }

        if (isset($options['limit']['start']) && isset($options['limit']['end'])) {
            $query .= " LIMIT " . $options['limit']['start'] . "," . $options['limit']['end'];
        }

        $res = $this->dic->database()->query($query);
        $profiles = [];
        while ($rec = $this->dic->database()->fetchAssoc($res)) {
            $profiles[] = new ilMStListCompetencesProfile(
                $rec['profile_title'],
                (bool) $rec['fulfilled'],
                $rec['login'],
                $rec['firstname'],
                $rec['lastname'],
                $rec['user_id']
            );
        }

        return $profiles;
    }


    /**
     * @param array $filters
     *
     * @return string
     */
    protected function getAdditionalWhereStatement(array $filters) : string
    {
        $wheres = [];

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