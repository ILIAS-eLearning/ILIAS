<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMemberSearchDataProvider
 *
 * @author Nadia Matuschek <nmatuschek@databay.de>
 *
 **/
class ilMailMemberSearchDataProvider
{
    /** @var ilAccessHandler */
    protected $access;

    /** @var int */
    protected $ref_id;

    /** @var string */
    protected $type = 'crs';

    /** @var array */
    protected $data = [];

    /** @var null */
    protected $objParticipants = null;

    /** @var ilObjectDataCache */
    protected $dataCache;

    /** @var array */
    protected $roleSortWeightMap = [
        'il_crs_a' => 10,
        'il_grp_a' => 10,
        'il_crs_t' => 9,
        'il_crs_m' => 8,
        'il_grp_m' => 8,
    ];

    /** @var ilLanguage */
    protected $lng;

    /**
     * @param ilParticipants $objParticipants
     * @param int $a_ref_id
     */
    public function __construct($objParticipants, $a_ref_id)
    {
        global $DIC;

        $this->dataCache = $DIC['ilObjDataCache'];
        $this->access = $DIC->access();
        $this->objParticipants = $objParticipants;
        $this->type = $this->objParticipants->getType();
        $this->lng = $DIC['lng'];

        $this->ref_id = $a_ref_id;

        $this->collectTableData();
    }

    private function collectTableData() : void
    {
        $participants = $this->objParticipants->getParticipants();
        if ($this->type === 'crs' || $this->type === 'grp') {
            $participants = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'read',
                'manage_members',
                $this->ref_id,
                $participants
            );
        }

        $preloadedRoleIds = [];
        foreach ($participants as $user_id) {
            $user = ilObjectFactory::getInstanceByObjId($user_id, false);
            if (!$user || !($user instanceof ilObjUser)) {
                continue;
            }

            if (!$user->getActive()) {
                continue;
            }

            $login = $user->getLogin();

            $publicName = '';
            if (in_array($user->getPref('public_profile'), ['g', 'y'])) {
                $publicName = $user->getLastname() . ', ' . $user->getFirstname();
            }

            $this->data[$user_id]['user_id'] = $user_id;
            $this->data[$user_id]['login'] = $login;
            $this->data[$user_id]['name'] = $publicName;

            $assignedRoles = $this->objParticipants->getAssignedRoles($user_id);
            $rolesToPreload = array_diff($assignedRoles, $preloadedRoleIds);
            $this->dataCache->preloadObjectCache($rolesToPreload);

            $roleTitles = [];
            foreach ($assignedRoles as $roleId) {
                $preloadedRoleIds[$roleId] = $roleId;
                $title = $this->dataCache->lookupTitle($roleId);
                $roleTitles[] = $title;
            }

            $roleTitles = $this->sortRoles($roleTitles);

            $roleTitles = array_map(function (string $roleTitle) : string {
                return $this->buildRoleTitle($roleTitle);
            }, $roleTitles);

            $this->data[$user_id]['role'] = implode(', ', $roleTitles);
        }
    }

    /**
     * @param string[] $roleTitles
     * @return string[]
     */
    private function sortRoles(array $roleTitles) : array
    {
        usort($roleTitles, function (string $a, string $b) : int {
            $leftPrefixTitle = substr($a, 0, 8);
            $rightPrefixTitle = substr($b, 0, 8);

            $leftRating = $this->roleSortWeightMap[$leftPrefixTitle] ?? 0;
            $rightRating = $this->roleSortWeightMap[$rightPrefixTitle] ?? 0;

            if ($leftRating > 0 || $rightRating > 0) {
                if ($leftRating !== $rightRating) {
                    return $rightRating - $leftRating > 0 ? 1 : -1;
                }

                return 0;
            }

            return strcmp($a, $b);
        });

        return $roleTitles;
    }

    private function buildRoleTitle(string $role) : string
    {
        return ilObjRole::_getTranslation($role);
    }

    public function getData() : array
    {
        return $this->data;
    }
}
