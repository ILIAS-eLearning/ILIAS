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
 * Class ilObjDataCollection
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
 * @extends ilObject2
 */
class ilObjDataCollection extends ilObject2
{
    private bool $is_online = false;
    private string $rating = "";
    private string $approval = "";
    private string $public_notes = "";
    private string $notification = "";

    protected function initType(): void
    {
        $this->type = "dcl";
    }

    protected function doRead(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query("SELECT * FROM il_dcl_data WHERE id = " . $ilDB->quote($this->getId(), "integer"));

        $data = $ilDB->fetchObject($result);
        if ($data) {
            $this->setOnline($data->is_online);
            $this->setRating($data->rating);
            $this->setApproval($data->approval);
            $this->setPublicNotes($data->public_notes);
            $this->setNotification($data->notification);
        }
    }

    protected function doCreate(bool $clone_mode = false): void
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
                "id" => array("integer", $this->getId()),
                "is_online" => array("integer", (int) $this->getOnline()),
                "rating" => array("integer", (int) $this->getRating()),
                "public_notes" => array("integer", (int) $this->getPublicNotes()),
                "approval" => array("integer", (int) $this->getApproval()),
                "notification" => array("integer", (int) $this->getNotification()),
            )
        );
    }

    protected function doDelete(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        foreach ($this->getTables() as $table) {
            $table->doDelete(false, true);
        }

        $query = "DELETE FROM il_dcl_data WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);
    }

    protected function doUpdate(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->update(
            "il_dcl_data",
            array(
                "id" => array("integer", $this->getId()),
                "is_online" => array("integer", (int) $this->getOnline()),
                "rating" => array("integer", (int) $this->getRating()),
                "public_notes" => array("integer", (int) $this->getPublicNotes()),
                "approval" => array("integer", (int) $this->getApproval()),
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
        $http = $DIC->http();
        $refinery = $DIC->refinery();

        $ref_id = $http->wrapper()->query()->retrieve('table_id', $refinery->kindlyTo()->int());

        // If coming from trash, never send notifications and don't load dcl Object
        if ($ref_id === SYSTEM_FOLDER_ID) {
            return;
        }

        $dclObj = new ilObjDataCollection($ref_id);

        if ($dclObj->getNotification() != 1) {
            return;
        }
        $obj_table = ilDclCache::getTableCache($a_table_id);
        $obj_dcl = $obj_table->getCollectionObject();

        // recipients
        $users = ilNotification::getNotificationsForObject(
            ilNotification::TYPE_DATA_COLLECTION,
            $obj_dcl->getId(),
            true
        );
        if (!sizeof($users)) {
            return;
        }

        ilNotification::updateNotificationTime(ilNotification::TYPE_DATA_COLLECTION, $obj_dcl->getId(), $users);

        $http = $DIC->http();
        $refinery = $DIC->refinery();
        $ref_id = $http->wrapper()->query()->retrieve('ref_id', $refinery->kindlyTo()->int());

        $link = ilLink::_getLink($ref_id);

        // prepare mail content
        // use language of recipient to compose message

        // send mails
        foreach (array_unique($users) as $idx => $user_id) {
            // the user responsible for the action should not be notified
            $record = ilDclCache::getRecordCache($a_record_id);
            $ilDclTable = new ilDclTable($record->getTableId());
            if ($user_id != $ilUser->getId() && $ilDclTable->hasPermissionToViewRecord(filter_input(
                INPUT_GET,
                'ref_id'
            ), $record, $user_id)) {
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

                    $ref_id = $http->wrapper()->query()->retrieve('ref_id', $refinery->kindlyTo()->int());

                    if ($tableview_id = $record->getTable()->getFirstTableViewId($ref_id, $user_id)) {
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
                $mail_obj->enqueue(ilObjUser::_lookupLogin($user_id), "", "", $subject, $message, array());
            } else {
                unset($users[$idx]);
            }
        }
    }

    /**
     * for users with write access, return id of table with the lowest sorting
     * for users with no write access, return id of table with the lowest sorting, which is visible
     */
    public function getFirstVisibleTableId(): int
    {
        global $DIC;
        /** @var ilDBInterface $ilDB */
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

    public function reorderTables(array $table_order): void
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
     * @param ilObject2 $new_obj
     * @param int       $a_target_id ref_id
     * @param int|null  $a_copy_id
     * @return void
     */
    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null): void
    {
        assert($new_obj instanceof ilObjDataCollection);
        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOnline($this->getOnline());
        }

        $new_obj->cloneStructure($this->getRefId());
    }

    /**
     * Attention only use this for objects who have not yet been created (use like: $x = new ilObjDataCollection; $x->cloneStructure($id))
     * @param int $original_id The original ID of the dataselection you want to clone it's structure
     */
    public function cloneStructure(int $original_id): void
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
    public function setOnline($a_val): void
    {
        $this->is_online = $a_val;
    }

    /**
     * getOnline
     */
    public function getOnline(): bool
    {
        return $this->is_online;
    }

    public function setRating(string $a_val): void
    {
        $this->rating = $a_val;
    }

    public function getRating(): string
    {
        return $this->rating;
    }

    public function setPublicNotes(string $a_val)
    {
        $this->public_notes = $a_val;
    }

    public function getPublicNotes(): string
    {
        return $this->public_notes;
    }

    public function setApproval(string $a_val): void
    {
        $this->approval = $a_val;
    }

    public function getApproval(): string
    {
        return $this->approval;
    }

    public function setNotification(string $a_val): void
    {
        $this->notification = $a_val;
    }

    public function getNotification(): string
    {
        return $this->notification;
    }

    /**
     * @param $ref int the reference id of the datacollection object to check.
     * @return bool whether or not the current user has admin/write access to the referenced datacollection
     * @deprecated
     */
    public static function _hasWriteAccess(int $ref): bool
    {
        return ilObjDataCollectionAccess::hasWriteAccess($ref);
    }

    /**
     * @param $ref int the reference id of the datacollection object to check.
     * @return bool whether or not the current user has add/edit_entry access to the referenced datacollection
     * @deprecated
     */
    public static function _hasReadAccess(int $ref): bool
    {
        return ilObjDataCollectionAccess::hasReadAccess($ref);
    }

    /**
     * @return ilDclTable[] Returns an array of tables of this collection with ids of the tables as keys.
     */
    public function getTables(): array
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

    public function getTableById(int $table_id): ilDclTable
    {
        return ilDclCache::getTableCache($table_id);
    }

    public function getVisibleTables(): array
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
     * @param string $title  Title of table
     * @param int    $obj_id Obj-ID of the table
     * @return bool
     */
    public static function _hasTableByTitle(string $title, int $obj_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->query(
            'SELECT * FROM il_dcl_table WHERE obj_id = ' . $ilDB->quote($obj_id, 'integer') . ' AND title = '
            . $ilDB->quote($title, 'text')
        );

        return ($ilDB->numRows($result)) ? true : false;
    }

    public function getStyleSheetId(): int
    {
        return 0;
    }
}
