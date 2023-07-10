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

namespace ILIAS\Awareness\User;

use ILIAS\DI\Container;

/**
 * All members of the same courses/groups as the user
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ProviderCurrentCourse implements Provider
{
    protected \ilLanguage $lng;
    protected \ILIAS\Awareness\StandardGUIRequest $request;
    protected \ILIAS\DI\RBACServices $rbac;
    protected \ilDBInterface $db;
    protected \ilTree $tree;
    protected \ilAccessHandler $access;

    public function __construct(Container $DIC)
    {
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->rbac = $DIC->rbac();
        $this->lng = $DIC->language();
        $this->request = $DIC->awareness()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    /**
     * Get provider id
     * @return string provider id
     */
    public function getProviderId(): string
    {
        return "crs_current";
    }

    /**
     * Provider title (used in awareness overlay and in administration settings)
     * @return string provider title
     */
    public function getTitle(): string
    {
        $this->lng->loadLanguageModule("crs");
        return $this->lng->txt("crs_awrn_current_course");
    }

    /**
     * Provider info (used in administration settings)
     * @return string provider info text
     */
    public function getInfo(): string
    {
        $this->lng->loadLanguageModule("crs");
        return $this->lng->txt("crs_awrn_current_course_info");
    }

    /**
     * Get initial set of users
     * @param ?int[] $user_ids
     * @return int[] array of user IDs
     */
    public function getInitialUserSet(?array $user_ids = null): array
    {
        $ilDB = $this->db;
        $tree = $this->tree;
        $ilAccess = $this->access;
        $rbacreview = $this->rbac->review();

        $ub = array();

        $awrn_logger = \ilLoggerFactory::getLogger('awrn');

        $ref_id = $this->request->getRefId();
        if ($ref_id > 0) {
            $path = $tree->getPathFull($ref_id);
            foreach ($path as $p) {
                if ($p["type"] == "crs" &&
                    ($ilAccess->checkAccess("write", "", $p["child"]) ||
                        (\ilObjCourse::lookupShowMembersEnabled($p["obj_id"]) && $ilAccess->checkAccess("read", "", $p["child"])))) {
                    $lrol = $rbacreview->getRolesOfRoleFolder($p["child"], false);
                    $set = $ilDB->query('SELECT DISTINCT(usr_id) FROM rbac_ua ' .
                        'WHERE ' . $ilDB->in('rol_id', $lrol, false, 'integer'));

                    //$set = $ilDB->query($q = "SELECT DISTINCT usr_id FROM obj_members ".
                    //	" WHERE obj_id = ".$ilDB->quote($p["obj_id"], "integer"));
                    $ub = array();
                    while ($rec = $ilDB->fetchAssoc($set)) {
                        $ub[] = $rec["usr_id"];

                        $awrn_logger->debug("ilAwarenessUserProviderCurrentCourse: obj_id: " . $p["obj_id"] . ", " .
                            "Collected User: " . $rec["usr_id"]);
                    }
                }
            }
        }
        return $ub;
    }

    public function isHighlighted(): bool
    {
        return false;
    }
}
