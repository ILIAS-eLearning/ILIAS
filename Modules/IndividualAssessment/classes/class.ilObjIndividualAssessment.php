<?php

declare(strict_types=1);

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
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the IndividualAssessment is used.
 * It carries a LPStatus, which is set Individually.
 */
class ilObjIndividualAssessment extends ilObject
{
    use ilIndividualAssessmentDIC;

    protected ?bool $lp_active = null;
    protected ilIndividualAssessmentSettings $settings;
    protected ilIndividualAssessmentSettingsStorageDB $settings_storage;
    protected ilIndividualAssessmentMembersStorageDB $members_storage;
    protected ilIndividualAssessmentAccessHandler $access_handler;
    protected ilAccessHandler $il_access_handler;
    protected ?Pimple\Container $dic = null;

    protected ?ilIndividualAssessmentInfoSettings $info_settings = null;
    protected ?ilIndividualAssessmentFileStorage $file_storage = null;

    public function __construct(int $id = 0, bool $call_by_reference = true)
    {
        global $DIC;
        $this->type = 'iass';
        $this->il_access_handler = $DIC["ilAccess"];

        parent::__construct($id, $call_by_reference);

        $this->settings_storage = new ilIndividualAssessmentSettingsStorageDB($DIC['ilDB']);
        $this->members_storage = new ilIndividualAssessmentMembersStorageDB($DIC['ilDB']);
        $this->access_handler = new ilIndividualAssessmentAccessHandler(
            $this,
            $DIC['ilAccess'],
            $DIC['rbacadmin'],
            $DIC['rbacreview'],
            $DIC['ilUser']
        );
    }

    /**
     * @inheritdoc
     */
    public function create(): int
    {
        $id = parent::create();
        $this->settings = new ilIndividualAssessmentSettings(
            $this->getId(),
            '',
            '',
            '',
            '',
            false,
            false
        );
        $this->settings_storage->createSettings($this->settings);
        return $id;
    }

    /**
     * @inheritdoc
     */
    public function read(): void
    {
        parent::read();
        global $DIC;
        $settings_storage = new ilIndividualAssessmentSettingsStorageDB($DIC['ilDB']);
        $this->settings = $settings_storage->loadSettings($this);
        $this->info_settings = $settings_storage->loadInfoSettings($this);
    }

    public function getSettings(): ilIndividualAssessmentSettings
    {
        if (!$this->settings) {
            $this->settings = $this->settings_storage->loadSettings($this);
        }
        return $this->settings;
    }

    /**
     * Set the settings
     */
    public function setSettings(ilIndividualAssessmentSettings $settings): void
    {
        $this->settings = $settings;
        $this->setTitle($settings->getTitle());
        $this->setDescription($settings->getDescription());
    }

    public function getInfoSettings(): ilIndividualAssessmentInfoSettings
    {
        if (!$this->info_settings) {
            $this->info_settings = $this->settings_storage->loadInfoSettings($this);
        }
        return $this->info_settings;
    }

    /**
     * Set info settings
     */
    public function setInfoSettings(ilIndividualAssessmentInfoSettings $info): void
    {
        $this->info_settings = $info;
    }

    /**
     * Get the members object associated with this.
     */
    public function loadMembers(): ilIndividualAssessmentMembers
    {
        return $this->members_storage->loadMembers($this);
    }

    /**
     * Get the members as single object associated with this.
     *
     * @return	ilIndividualAssessmentMember[]
     */
    public function loadMembersAsSingleObjects(string $filter = null, string $sort = null): array
    {
        return $this->members_storage->loadMembersAsSingleObjects($this, $filter, $sort);
    }

    /**
     * Get the members object associated with this and visible by the current user.
     */
    public function loadVisibleMembers(): ilIndividualAssessmentMembers
    {
        return $this->members_storage->loadMembers($this)
                ->withAccessHandling($this->il_access_handler);
    }

    /**
     * Update the members object associated with this.
     */
    public function updateMembers(ilIndividualAssessmentMembers $members): void
    {
        $members->updateStorageAndRBAC($this->members_storage, $this->access_handler);
    }

    /**
     * @inheritdoc
     */
    public function delete(): bool
    {
        $this->settings_storage->deleteSettings($this);
        $this->members_storage->deleteMembers($this);
        return parent::delete();
    }

    /**
     * @inheritdoc
     */
    public function update(): bool
    {
        parent::update();
        $this->settings_storage->updateSettings($this->settings);
        return true;
    }

    public function updateInfo(): void
    {
        $this->settings_storage->updateInfoSettings($this->getInfoSettings());
    }

    /**
     * Get the member storage object used by this.
     */
    public function membersStorage(): ilIndividualAssessmentMembersStorage
    {
        return $this->members_storage;
    }

    /**
     * @inheritdoc
     */
    public function initDefaultRoles(): void
    {
        $this->access_handler->initDefaultRolesForObject($this);
    }

    /**
     * Get the access handler of this.
     */
    public function accessHandler(): IndividualAssessmentAccessHandler
    {
        return $this->access_handler;
    }

    /**
     * @inheritdoc
     */
    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);
        $settings = $this->getSettings();
        $info_settings = $this->getInfoSettings();
        $new_settings = new ilIndividualAssessmentSettings(
            $new_obj->getId(),
            $new_obj->getTitle(),
            $new_obj->getDescription(),
            $settings->getContent(),
            $settings->getRecordTemplate(),
            $settings->isEventTimePlaceRequired(),
            $settings->isFileRequired()
        );
        $new_obj->settings = $new_settings;

        $new_info_settings = new ilIndividualAssessmentInfoSettings(
            $new_obj->getId(),
            $info_settings->getContact(),
            $info_settings->getResponsibility(),
            $info_settings->getPhone(),
            $info_settings->getMails(),
            $info_settings->getConsultationHours()
        );
        $new_obj->settings = $new_settings;
        $new_obj->info_settings = $new_info_settings;
        $new_obj->settings_storage->updateSettings($new_settings);
        $new_obj->settings_storage->updateInfoSettings($new_info_settings);

        $fstorage = $this->getFileStorage();
        if (count($fstorage->readDir()) > 0) {
            $n_fstorage = $new_obj->getFileStorage();
            $n_fstorage->create();
            $fstorage->_copyDirectory($fstorage->getAbsolutePath(), $n_fstorage->getAbsolutePath());
        }
        return $new_obj;
    }

    /**
     * Get the file storage system
     */
    public function getFileStorage(): ilIndividualAssessmentFileStorage
    {
        if ($this->file_storage === null) {
            $this->file_storage = ilIndividualAssessmentFileStorage::getInstance($this->getId());
        }
        return $this->file_storage;
    }

    /**
     * Check whether the LP is activated for current object.
     */
    public function isActiveLP(): bool
    {
        if ($this->lp_active === null) {
            $this->lp_active = ilIndividualAssessmentLPInterface::isActiveLP($this->getId());
        }
        return $this->lp_active;
    }

    /**
     * Bubbles up the tree.
     * Starts from object with id $id.
     * Ends at root or when a given $type of object is found.
     *
     * @global array $DIC
     * @param int $id start at this id
     * @param string[] $types search for these strings
     *
     * @return int the obj_id or 0 if root is reached
     */
    public function getParentContainerIdByType(int $id, array $types): int
    {
        global $DIC;

        $tree = $DIC['tree'];
        $node = $tree->getParentNodeData($id);

        while ($node['type'] !== "root") {
            if (in_array($node['type'], $types)) {
                return (int) $node['ref_id'];
            }
            $node = $tree->getParentNodeData((int) $node['ref_id']);
        }
        return 0;
    }

    protected function getDic(): Pimple\Container
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getObjectDIC(
                $this,
                $DIC
            );
        }
        return $this->dic;
    }

    public function getMembersGUI(): ilIndividualAssessmentMembersGUI
    {
        return $this->getDic()['ilIndividualAssessmentMembersGUI'];
    }

    public function getSettingsGUI(): ilIndividualAssessmentSettingsGUI
    {
        return $this->getDic()['ilIndividualAssessmentSettingsGUI'];
    }
}
