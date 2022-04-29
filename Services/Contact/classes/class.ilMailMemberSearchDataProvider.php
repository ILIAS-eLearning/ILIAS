<?php declare(strict_types=1);

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

/**
 * Class ilMailMemberSearchDataProvider
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailMemberSearchDataProvider
{
    protected ilAccessHandler $access;
    protected int $ref_id;
    protected string $type = 'crs';
    protected array $data = [];
    protected ilParticipants $objParticipants;
    protected ilObjectDataCache $dataCache;
    /**
     * @var array<string, int>
     */
    protected array $roleSortWeightMap = [
        'il_crs_a' => 10,
        'il_grp_a' => 10,
        'il_crs_t' => 9,
        'il_crs_m' => 8,
        'il_grp_m' => 8,
    ];
    protected ilLanguage $lng;

    
    public function __construct(ilParticipants $objParticipants, int $a_ref_id)
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
            if (!($user instanceof ilObjUser)) {
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
                $title = $this->dataCache->lookupTitle((int) $roleId);
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
