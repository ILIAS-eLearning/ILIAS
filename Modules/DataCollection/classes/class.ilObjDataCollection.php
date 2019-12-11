<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjDataCollection
 *
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
 *
 * @extends ilObject2
 */
class ilObjDataCollection extends ilObject2
{
    public function initType()
    {
        $this->type = "dcl";
    }


    public function doRead()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query("SELECT * FROM il_dcl_data WHERE id = " . $ilDB->quote($this->getId(), "integer"));

        $data = $ilDB->fetchObject($result);
        $this->setOnline($data->is_online);
        $this->setRating($data->rating);
        $this->setApproval($data->approval);
        $this->setPublicNotes($data->public_notes);
        $this->setNotification($data->notification);
    }


    protected function doCreate($clone_mode = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];

        $ilLog->write('doCreate');

        if (!$clone_mode) {
            //Create Main Table - The title of the table is per default the title of the data collection object
            $main_table = ilDclCache::getTableCache();
            $main_table->setObjId($this->getId());
            $main_table->setTitle($this->getTitle());
            $main_table->setAddPerm(1);
            $main_table->setEditPerm(1);
            $main_table->setDeletePerm(0);
            $main_table->setDeleteByOwner(1);
            $main_table->setEditByOwner(1);
            $main_table->setLimited(0);
            $main_table->setIsVisible(true);
            $main_table->doCreate();
        }

        $ilDB->insert(
            "il_dcl_data",
            array(
            "id"           => array("integer", $this->getId()),
            "is_online"    => array("integer", (int) $this->getOnline()),
            "rating"       => array("integer", (int) $this->getRating()),
            "public_notes" => array("integer", (int) $this->getPublicNotes()),
            "approval"     => array("integer", (int) $this->getApproval()),
            "notification" => array("integer", (int) $this->getNotification()),
        )
        );
    }


    /**
     * @return bool
     */
    public function doClone()
    {
        return false;
    }


    protected function doDelete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        foreach ($this->getTables() as $table) {
            $table->doDelete(false, true);
        }

        $query = "DELETE FROM il_dcl_data WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);
    }


    public function doUpdate()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->update(
            "il_dcl_data",
            array(
            "id"           => array("integer", $this->getId()),
            "is_online"    => array("integer", (int) $this->getOnline()),
            "rating"       => array("integer", (int) $this->getRating()),
            "public_notes" => array("integer", (int) $this->getPublicNotes()),
            "approval"     => array("integer", (int) $this->getApproval()),
            "notification" => array("integer", (int) $this->getNotification()),
        ),
            array(
                "id" => array("integer", $this->getId()),
            )
        );
    }


    /**
     * @param      $a_action
     * @param      $a_table_id
     * @param null $a_record_id
     */
    public static function sendNotification($a_action, $a_table_id, $a_record_id = null)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];

        // If coming from trash, never send notifications and don't load dcl Object
        if ($_GET['ref_id'] == SYSTEM_FOLDER_ID) {
            return;
        }

        $dclObj = new ilObjDataCollection($_GET['ref_id']);

        if ($dclObj->getNotification() != 1) {
            return;
        }
        $obj_table = ilDclCache::getTableCache($a_table_id);
        $obj_dcl = $obj_table->getCollectionObject();

        // recipients
        $users = ilNotification::getNotificationsForObject(ilNotification::TYPE_DATA_COLLECTION, $obj_dcl->getId(), true);
        if (!sizeof($users)) {
            return;
        }

        ilNotification::updateNotificationTime(ilNotification::TYPE_DATA_COLLECTION, $obj_dcl->getId(), $users);

        //FIXME  $_GET['ref_id]
        $link = ilLink::_getLink($_GET['ref_id']);

        // prepare mail content
        // use language of recipient to compose message

        // send mails
        foreach (array_unique($users) as $idx => $user_id) {
            // the user responsible for the action should not be notified
            // FIXME  $_GET['ref_id]
            $record = ilDclCache::getRecordCache($a_record_id);
            $ilDclTable = new ilDclTable($record->getTableId());
            if ($user_id != $ilUser->getId() && $ilDclTable->hasPermissionToViewRecord(filter_input(INPUT_GET, 'ref_id'), $record, $user_id)) {
                // use language of recipient to compose message
                $ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
                $ulng->loadLanguageModule('dcl');

                $subject = sprintf($ulng->txt('dcl_change_notification_subject'), $obj_dcl->getTitle());
                // update/delete
                $message = $ulng->txt("dcl_hello") . " " . ilObjUser::_lookupFullname($user_id) . ",\n\n";
                $message .= $ulng->txt('dcl_change_notification_dcl_' . $a_action) . ":\n\n";
                $message .= $ulng->txt('obj_dcl') . ": " . $obj_dcl->getTitle() . "\n\n";
                $message .= $ulng->txt('dcl_table') . ": " . $obj_table->getTitle() . "\n\n";
                $message .= $ulng->txt('dcl_record') . ":\n";
                $message .= "------------------------------------\n";
                if ($a_record_id) {
                    if (!$record->getTableId()) {
                        $record->setTableId($a_table_id);
                    }
                    //					$message .= $ulng->txt('dcl_record_id').": ".$a_record_id.":\n";
                    $t = "";
                    if ($tableview_id = $record->getTable()->getFirstTableViewId($_GET['ref_id'], $user_id)) {
                        $visible_fields = ilDclTableView::find($tableview_id)->getVisibleFields();
                        if (empty($visible_fields)) {
                            continue;
                        }
                        /** @var ilDclBaseFieldModel $field */
                        foreach ($visible_fields as $field) {
                            if ($field->isStandardField()) {
                                $value = $record->getStandardFieldPlainText($field->getId());
                            } elseif ($record_field = $record->getRecordField($field->getId())) {
                                $value = $record_field->getPlainText();
                            }

                            if ($value) {
                                $t .= $field->getTitle() . ": " . $value . "\n";
                            }
                        }
                    }
                    $message .= $t;
                }
                $message .= "------------------------------------\n";
                $message .= $ulng->txt('dcl_changed_by') . ": " . $ilUser->getFullname() . " " . ilUserUtil::getNamePresentation($ilUser->getId())
                    . "\n\n";
                $message .= $ulng->txt('dcl_change_notification_link') . ": " . $link . "\n\n";

                $message .= $ulng->txt('dcl_change_why_you_receive_this_email');

                $mail_obj = new ilMail(ANONYMOUS_USER_ID);
                $mail_obj->appendInstallationSignature(true);
                $mail_obj->sendMail(ilObjUser::_lookupLogin($user_id), "", "", $subject, $message, array(), array("system"));
            } else {
                unset($users[$idx]);
            }
        }
    }


    /**
     * for users with write access, return id of table with the lowest sorting
     * for users with no write access, return id of table with the lowest sorting, which is visible
     *
     * @return mixed
     */
    public function getFirstVisibleTableId()
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];
        $ilDB->setLimit(1);
        $only_visible = ilObjDataCollectionAccess::hasWriteAccess($this->ref_id) ? '' : ' AND is_visible = 1 ';
        $result = $ilDB->query(
            'SELECT id 
									FROM il_dcl_table 
									WHERE obj_id = ' . $ilDB->quote($this->getId(), 'integer') .
            $only_visible . '
									ORDER BY -table_order DESC '
        ); //"-table_order DESC" is ASC with NULL last

        // if there's no visible table, fetch first one not visible
        // this is to avoid confusion, since the default of a table after creation is not visible
        if (!$result->numRows() && $only_visible) {
            $ilDB->setLimit(1);
            $result = $ilDB->query(
                'SELECT id 
									FROM il_dcl_table 
									WHERE obj_id = ' . $ilDB->quote($this->getId(), 'integer') . '
									ORDER BY -table_order DESC '
            );
        }

        return $ilDB->fetchObject($result)->id;
    }


    /**
     * @param $table_order
     */
    public function reorderTables($table_order)
    {
        if ($table_order) {
            $order = 10;
            foreach ($table_order as $title) {
                $table_id = ilDclTable::_getTableIdByTitle($title, $this->getId());
                $table = ilDclCache::getTableCache($table_id);
                $table->setOrder($order);
                $table->doUpdate();
                $order += 10;
            }
        }
    }


    /**
     * Clone DCL
     *
     * @param ilObjDataCollection $new_obj
     * @param int                 $a_target_id ref_id
     * @param int                 $a_copy_id
     *
     * @return ilObjPoll
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null, $a_omit_tree = false)
    {

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOnline($this->getOnline());
        }

        $new_obj->cloneStructure($this->getRefId());

        return $new_obj;
    }

    //TODO: Find better way to copy data (including references)
    /*public function doCloneObject(ilObjDataCollection $new_obj, $a_target_id, $a_copy_id = 0) {
        //$new_obj->delete();
        $created_new_id = $new_obj->getId();
        $obj_id = $this->getId();

        $exp = new ilExport();
        $exp->exportObject($this->getType(), $obj_id, "5.0.0");

        $file_name = substr(strrchr($exp->export_run_dir, DIRECTORY_SEPARATOR), 1);

        $import = new ilImport((int)$a_target_id);
        $new_id = $import->importObject(null, $exp->export_run_dir.".zip", $file_name.".zip", $this->getType(), "", true);

        $new_obj->delete();

        if ($new_id > 0)
        {
            $obj = ilObjectFactory::getInstanceByObjId($new_id);
            $obj->setId($created_new_id);

            $obj->createReference();
            $obj->putInTree($a_target_id);
            $obj->setPermissions($a_target_id);


        }

        return $obj;
    }*/

    /**
     * Attention only use this for objects who have not yet been created (use like: $x = new ilObjDataCollection; $x->cloneStructure($id))
     *
     * @param $original_id The original ID of the dataselection you want to clone it's structure
     */
    public function cloneStructure($original_id)
    {
        $original = new ilObjDataCollection($original_id);

        $this->setApproval($original->getApproval());
        $this->setNotification($original->getNotification());
        $this->setPublicNotes($original->getPublicNotes());
        $this->setRating($original->getRating());

        // delete old tables.
        foreach ($this->getTables() as $table) {
            $table->doDelete();
        }

        // add new tables.
        foreach ($original->getTables() as $table) {
            $new_table = new ilDclTable();
            $new_table->setObjId($this->getId());
            $new_table->cloneStructure($table);
        }

        // mandatory for all cloning functions
        ilDclCache::setCloneOf($original_id, $this->getId(), ilDclCache::TYPE_DATACOLLECTION);

        foreach ($this->getTables() as $table) {
            $table->afterClone();
        }
    }


    /**
     * setOnline
     */
    public function setOnline($a_val)
    {
        $this->is_online = $a_val;
    }


    /**
     * getOnline
     */
    public function getOnline()
    {
        return $this->is_online;
    }


    /**
     * setRating
     */
    public function setRating($a_val)
    {
        $this->rating = $a_val;
    }


    /**
     * getRating
     */
    public function getRating()
    {
        return $this->rating;
    }


    /**
     * setPublicNotes
     */
    public function setPublicNotes($a_val)
    {
        $this->public_notes = $a_val;
    }


    /**
     * getPublicNotes
     */
    public function getPublicNotes()
    {
        return $this->public_notes;
    }


    /**
     * setApproval
     */
    public function setApproval($a_val)
    {
        $this->approval = $a_val;
    }


    /**
     * getApproval
     */
    public function getApproval()
    {
        return $this->approval;
    }


    /**
     * setNotification
     */
    public function setNotification($a_val)
    {
        $this->notification = $a_val;
    }


    /**
     * getNotification
     */
    public function getNotification()
    {
        return $this->notification;
    }


    /**
     * @param $ref int the reference id of the datacollection object to check.
     *
     * @deprecated
     * @return bool whether or not the current user has admin/write access to the referenced datacollection
     */
    public static function _hasWriteAccess($ref)
    {
        return ilObjDataCollectionAccess::hasWriteAccess($ref);
    }


    /**
     * @param $ref int the reference id of the datacollection object to check.
     *
     * @deprecated
     * @return bool whether or not the current user has add/edit_entry access to the referenced datacollection
     */
    public static function _hasReadAccess($ref)
    {
        return ilObjDataCollectionAccess::hasReadAccess($ref);
    }


    /**
     * @return ilDclTable[] Returns an array of tables of this collection with ids of the tables as keys.
     */
    public function getTables()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT id FROM il_dcl_table WHERE obj_id = " . $ilDB->quote($this->getId(), "integer") .
            " ORDER BY -table_order DESC";
        $set = $ilDB->query($query);
        $tables = array();

        while ($rec = $ilDB->fetchAssoc($set)) {
            $tables[$rec['id']] = ilDclCache::getTableCache($rec['id']);
        }

        return $tables;
    }


    public function getTableById($table_id)
    {
        return ilDclCache::getTableCache($table_id);
    }


    /**
     * @return array
     */
    public function getVisibleTables()
    {
        $tables = array();
        foreach ($this->getTables() as $table) {
            if ($table->getIsVisible() && $table->getVisibleTableViews($this->ref_id)) {
                $tables[$table->getId()] = $table;
            }
        }

        return $tables;
    }


    /**
     * Checks if a DataCollection has a table with a given title
     *
     * @param $title  Title of table
     * @param $obj_id Obj-ID of the table
     *
     * @return bool
     */
    public static function _hasTableByTitle($title, $obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->query(
            'SELECT * FROM il_dcl_table WHERE obj_id = ' . $ilDB->quote($obj_id, 'integer') . ' AND title = '
            . $ilDB->quote($title, 'text')
        );

        return ($ilDB->numRows($result)) ? true : false;
    }


    public function getStyleSheetId()
    {
    }
}
