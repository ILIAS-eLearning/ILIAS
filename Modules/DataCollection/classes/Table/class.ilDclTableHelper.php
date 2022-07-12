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
 ********************************************************************
 */

/**
 * Class ilDclTableHelper
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDclTableHelper
{
    protected int $obj_id = 0;
    protected int $ref_id = 0;
    protected ilRbacReview $rbac_review;
    protected ilObjUser $user;
    protected ilDBInterface $database;

    /**
     * ilDclTableHelper constructor.
     */
    public function __construct(
        int $obj_id,
        int $ref_id,
        ilRbacReview $rbac_review,
        ilObjUser $user,
        ilDBInterface $database
    ) {
        $this->obj_id = $obj_id;
        $this->ref_id = $ref_id;
        $this->rbac_review = $rbac_review;
        $this->user = $user;
        $this->database = $database;
    }

    public function getRoleTitlesWithoutReadRightOnAnyStandardView() : array
    {
        $visible_tables_for_data_collection = $this->getAllVisibleTablesForDataColleciton();
        $standard_views_for_data_collection = $this->getStandardViewsByVisibleTables($visible_tables_for_data_collection);

        $roles_ids = $this->getRolesIdsByViews($standard_views_for_data_collection);

        $roles_with_read_acces_ids = $this->getRolesIdsWithReadAccessOnDataCollection();

        //check if there are roles with rbac read right on the datacollection but without read right on any table view
        $roles_with_no_read_right_on_any_standard_view = array_diff($roles_with_read_acces_ids, $roles_ids);

        $roles_data = $this->rbac_review->getRolesForIDs($roles_with_no_read_right_on_any_standard_view, true);
        $role_titles = [];
        if (!empty($roles_data)) {
            foreach ($roles_data as $role_data) {
                $role_titles[] = $role_data['title'];
            }
        }

        return $role_titles;
    }

    protected function getRolesIdsWithReadAccessOnDataCollection() : array
    {
        $rbac_roles = $this->rbac_review->getParentRoleIds($this->ref_id);
        $roles_with_read_acces_ids = [];
        //get all roles with read access on data collection
        foreach ($rbac_roles as $role) {
            $operations = $this->rbac_review->getActiveOperationsOfRole($this->ref_id, $role['rol_id']);
            //3 corresponds to the read rbac right
            if (!empty($operations) && in_array(3, $operations)) {
                $roles_with_read_acces_ids[] = $role['rol_id'];
            }
        }

        return $roles_with_read_acces_ids;
    }

    protected function getRolesIdsByViews(array $views_for_data_collection) : array
    {
        $roles_ids = [];
        /**
         * @var $ilDclTableView                   ilDclTableView
         * @var $view_for_data_collection_object  ilDclTableView
         */
        foreach ($views_for_data_collection as $key => $view_for_data_collection_array_of_objects) {
            foreach ($view_for_data_collection_array_of_objects as $view_for_data_collection_object) {
                $ilDclTableView = ilDclTableView::find($view_for_data_collection_object->getId());
                $roles_of_view = $ilDclTableView->getRoles();
                $roles_ids = array_merge($roles_ids, $roles_of_view);
            }
        }

        return $roles_ids;
    }

    protected function getStandardViewsByVisibleTables(array $visible_tables_for_data_collection) : array
    {
        $standard_views_for_data_collection = [];
        foreach ($visible_tables_for_data_collection as $visible_table) {
            $standard_views_for_data_collection[] = ilDclTableView::where(
                [
                    'table_id' => $visible_table['id'],
                ]
            )->get();
        }

        return $standard_views_for_data_collection;
    }

    protected function getAllVisibleTablesForDataColleciton() : array
    {
        $visible_tables_for_data_collection = [];
        $res = $this->database->queryF(
            "SELECT * FROM il_dcl_table WHERE obj_id = %s AND is_visible = 1",
            array('integer'),
            array($this->obj_id)
        );
        while ($rec = $this->database->fetchAssoc($res)) {
            $visible_tables_for_data_collection[] = $rec;
        }

        return $visible_tables_for_data_collection;
    }

    protected function hasUserReadAccessOnAnyVisibleTableView() : bool
    {
        // admin user has always access to the views of a data collection
        if ($this->user->getId() == 6) {
            return true;
        }

        $visible_tables_for_data_collection = $this->getAllVisibleTablesForDataColleciton();
        $standard_views_for_data_collection = $this->getStandardViewsByVisibleTables($visible_tables_for_data_collection);

        $roles_ids = $this->getRolesIdsByViews($standard_views_for_data_collection);

        $user_ids_with_read_right_on_any_standard_view = [];
        foreach ($roles_ids as $role_id) {
            $assigned_users = $this->rbac_review->assignedUsers($role_id);
            if (!empty($assigned_users)) {
                $user_ids_with_read_right_on_any_standard_view[] = array_merge($user_ids_with_read_right_on_any_standard_view,
                    $assigned_users);
            }
        }

        //check if current user id is in the array of user ids with read right on standard view
        if ($this->in_array_r($this->user->getId(), $user_ids_with_read_right_on_any_standard_view)) {
            return true;
        } else {
            return false;
        }
    }

    protected function in_array_r(string $needle, array $haystack, bool $strict = false) : bool
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle,
                        $item, $strict))) {
                return true;
            }
        }

        return false;
    }
}
