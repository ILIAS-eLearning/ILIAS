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

class ilObjDataCollection extends ilObject2
{
    public const TYPE = 'dcl';
    private bool $is_online = false;
    private bool $rating = false;
    private bool $approval = false;
    private bool $public_notes = false;
    private bool $notification = false;
    private ilDclNotification $notification_settings;

    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        parent::__construct($a_id, $a_reference);
        $this->notification_settings = new ilDclNotification($this->db);
    }

    protected function initType(): void
    {
        $this->type = $this::TYPE;
    }

    protected function doRead(): void
    {
        $stmt = $this->db->queryF('SELECT * FROM il_dcl_data WHERE id = %s', [ilDBConstants::T_INTEGER], [$this->getId()]);
        if ($data = $this->db->fetchObject($stmt)) {
            $this->setOnline((bool) $data->is_online);
            $this->setRating((bool) $data->rating);
            $this->setApproval((bool) $data->approval);
            $this->setPublicNotes((bool) $data->public_notes);
            $this->setNotification((bool) $data->notification);
        }
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        if (!$clone_mode) {
            $main_table = ilDclCache::getTableCache();
            $main_table->setObjId($this->getId());
            $main_table->setTitle($this->lng->txt('dcl_title_standard'));
            $main_table->setAddPerm(true);
            $main_table->setEditPerm(true);
            $main_table->setDeletePerm(false);
            $main_table->setDeleteByOwner(true);
            $main_table->setEditByOwner(true);
            $main_table->setLimited(false);
            $main_table->setIsVisible(true);
            $main_table->doCreate();
        }
        $this->createMetaData();
        $this->db->insert(
            'il_dcl_data',
            [
                'id' => [ilDBConstants::T_INTEGER, $this->getId()],
                'is_online' => [ilDBConstants::T_INTEGER, $this->getOnline() ? 1 : 0],
                'rating' => [ilDBConstants::T_INTEGER, $this->getRating() ? 1 : 0],
                'public_notes' => [ilDBConstants::T_INTEGER, $this->getPublicNotes() ? 1 : 0],
                'approval' => [ilDBConstants::T_INTEGER, $this->getApproval() ? 1 : 0],
                'notification' => [ilDBConstants::T_INTEGER, $this->getNotification() ? 1 : 0],
            ]
        );
    }

    protected function doDelete(): void
    {
        foreach ($this->getTables() as $table) {
            $table->doDelete(false, true);
        }
        $this->deleteMetaData();
        $this->notification_settings->deleteForObject($this);
        $this->db->manipulateF('DELETE FROM il_dcl_data WHERE id = %s', [ilDBConstants::T_INTEGER], [$this->getId()]);
    }

    protected function doUpdate(): void
    {
        $this->updateMetaData();
        $this->db->update(
            'il_dcl_data',
            [
                'id' => [ilDBConstants::T_INTEGER, $this->getId()],
                'is_online' => [ilDBConstants::T_INTEGER, $this->getOnline() ? 1 : 0],
                'rating' => [ilDBConstants::T_INTEGER, $this->getRating() ? 1 : 0],
                'public_notes' => [ilDBConstants::T_INTEGER, $this->getPublicNotes() ? 1 : 0],
                'approval' => [ilDBConstants::T_INTEGER, $this->getApproval() ? 1 : 0],
                'notification' => [ilDBConstants::T_INTEGER, $this->getNotification() ? 1 : 0],
            ],
            [
                'id' => [ilDBConstants::T_INTEGER, $this->getId()],
            ]
        );
    }

    public function sendRecordNotification(ilDclNotificationType $action, ilDclBaseRecordModel $record): void
    {
        if (!$this->getNotification()) {
            return;
        }

        $users = ilNotification::getNotificationsForObject(
            ilNotification::TYPE_DATA_COLLECTION,
            $this->getId(),
            1
        );

        ilNotification::updateNotificationTime(ilNotification::TYPE_DATA_COLLECTION, $this->getId(), $users);

        $mail = new ilDataCollectionMailNotification();
        $mail->setType($action->value);
        $mail->setActor($this->user->getId());
        $mail->setObjId($this->getId());
        $mail->setRefId($this->getRefId());
        $mail->setRecord($record);
        $mail->setSender(ANONYMOUS_USER_ID);

        foreach ($users as $user_id) {
            if (
                $user_id !== $this->user->getId() &&
                $record->getTable()->hasPermissionToViewRecord($this->getRefId(), $record, $user_id) &&
                [] !== $record->getTable()->getVisibleTableViews($user_id) &&
                $this->notification_settings->has($this, $user_id, $action)
            ) {
                $mail->addRecipient($user_id);
            }
        }

        $mail->send();
    }

    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null): void
    {
        $new_obj->setNotification($this->getNotification());
        if (!(ilCopyWizardOptions::_getInstance($a_copy_id))->isRootNode($this->getRefId())) {
            $new_obj->setOnline($this->getOnline());
        }
        $new_obj->update();
        $new_obj->cloneStructure($this->getRefId());
        $this->cloneMetaData($new_obj);
    }

    public function cloneStructure(int $original_id): void
    {
        $original = new ilObjDataCollection($original_id);
        $this->setApproval($original->getApproval());
        $this->setNotification($original->getNotification());
        $this->setPublicNotes($original->getPublicNotes());
        $this->setRating($original->getRating());
        foreach ($this->getTables() as $table) {
            $table->doDelete();
        }
        foreach ($original->getTables() as $table) {
            $new_table = new ilDclTable();
            $new_table->setObjId($this->getId());
            $new_table->cloneStructure($table);
        }
        ilDclCache::setCloneOf($original_id, $this->getId(), ilDclCache::TYPE_DATACOLLECTION);
        foreach ($this->getTables() as $table) {
            $table->afterClone();
        }
    }

    public function setOnline(bool $a_val): void
    {
        $this->is_online = $a_val;
    }

    public function getOnline(): bool
    {
        return $this->is_online;
    }

    public function setRating(bool $a_val): void
    {
        $this->rating = $a_val;
    }

    public function getRating(): bool
    {
        return $this->rating;
    }

    public function setPublicNotes(bool $a_val): void
    {
        $this->public_notes = $a_val;
    }

    public function getPublicNotes(): bool
    {
        return $this->public_notes;
    }

    public function setApproval(bool $a_val): void
    {
        $this->approval = $a_val;
    }

    public function getApproval(): bool
    {
        return $this->approval;
    }

    public function setNotification(bool $a_val): void
    {
        $this->notification = $a_val;
    }

    public function getNotification(): bool
    {
        return $this->notification;
    }

    /**
     * @return ilDclTable[]
     */
    public function getTables(): array
    {
        $stmt = $this->db->queryF(
            'SELECT id FROM il_dcl_table WHERE obj_id = %s ORDER BY table_order',
            [ilDBConstants::T_INTEGER],
            [$this->getId()]
        );
        $tables = [];
        while ($rec = $this->db->fetchAssoc($stmt)) {
            $tables[$rec['id']] = $this->getTableById($rec['id']);
        }
        return $tables;
    }

    public function getTableById(int $table_id): ilDclTable
    {
        return ilDclCache::getTableCache($table_id);
    }

    /**
     * @return ilDclTable[]
     */
    public function getVisibleTables(): array
    {
        $tables = [];
        foreach ($this->getTables() as $table) {
            if ($table->getIsVisible() && $table->getVisibleTableViews()) {
                $tables[$table->getId()] = $table;
            }
        }
        return $tables;
    }

    public function getStyleSheetId(): int
    {
        return 0;
    }
}
