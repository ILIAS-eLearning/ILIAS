<?php
/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the IndividualAssessment is used.
 * It caries a LPStatus, which is set Individually.
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */

class ilObjIndividualAssessment extends ilObject
{
    use ilIndividualAssessmentDIC;

    /**
     * @var bool|null
     */
    protected $lp_active = null;

    /**
     * @var ilIndividualAssessmentSettings
     */
    protected $settings;

    /**
     * @var ilIndividualAssessmentSettingsStorageDB
     */
    protected $settings_storage;

    /**
     * @var ilIndividualAssessmentMembersStorageDB
     */
    protected $members_storage;

    /**
     * @var ilIndividualAssessmentAccessHandler
     */
    protected $access_handler;

    /**
     * @var ilAccessHandler
     */
    protected $il_access_handler;

    /**
     * @var Pimple\Container
     */
    protected $dic;

    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;
        $this->type = 'iass';
        $this->il_access_handler = $DIC["ilAccess"];
        parent::__construct($a_id, $a_call_by_reference);
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
    public function create()
    {
        parent::create();
        $this->createMetaData();

        $this->settings = new ilIndividualAssessmentSettings(
            (int) $this->getId(),
            '',
            '',
            '',
            '',
            false,
            false
        );
        $this->settings_storage->createSettings($this->settings);
    }

    /**
     * @inheritdoc
     */
    public function read()
    {
        parent::read();
        global $DIC;
        $settings_storage = new ilIndividualAssessmentSettingsStorageDB($DIC['ilDB']);
        $this->settings = $settings_storage->loadSettings($this);
        $this->info_settings = $settings_storage->loadInfoSettings($this);
    }

    public function getSettings() : ilIndividualAssessmentSettings
    {
        if (!$this->settings) {
            $this->settings = $this->settings_storage->loadSettings($this);
        }
        return $this->settings;
    }

    /**
     * Set the settings
     */
    public function setSettings(ilIndividualAssessmentSettings $settings)
    {
        $this->settings = $settings;
        $this->setTitle($settings->getTitle());
        $this->setDescription($settings->getDescription());
    }

    public function getInfoSettings() : ilIndividualAssessmentInfoSettings
    {
        if (!$this->info_settings) {
            $this->info_settings = $this->settings_storage->loadInfoSettings($this);
        }
        return $this->info_settings;
    }

    /**
     * Set info settings
     */
    public function setInfoSettings(ilIndividualAssessmentInfoSettings $info)
    {
        $this->info_settings = $info;
    }

    /**
     * Get the members object associated with this.
     *
     * @return	ilIndividualAssessmentMembers
     */
    public function loadMembers()
    {
        return $this->members_storage->loadMembers($this);
    }

    /**
     * Get the members as single object associated with this.
     *
     * @return	ilIndividualAssessmentMember[]
     */
    public function loadMembersAsSingleObjects(string $filter = null, string $sort = null)
    {
        return $this->members_storage->loadMembersAsSingleObjects($this, $filter, $sort);
    }

    /**
     * Get the members object associated with this and visible by the current user.
     *
     * @return	ilIndividualAssessmentMembers
     */
    public function loadVisibleMembers()
    {
        return $this->members_storage->loadMembers($this)
                ->withAccessHandling($this->il_access_handler);
    }

    /**
     * Update the members object associated with this.
     *
     * @param	ilIndividualAssessmentMembers	$members
     */
    public function updateMembers(ilIndividualAssessmentMembers $members)
    {
        $members->updateStorageAndRBAC($this->members_storage, $this->access_handler);
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $this->deleteMetaData();
        $this->settings_storage->deleteSettings($this);
        $this->members_storage->deleteMembers($this);
        parent::delete();
    }

    /**
     * @inheritdoc
     */
    public function update()
    {
        parent::update();
        $this->updateMetaData();

        $this->settings_storage->updateSettings($this->settings);
    }

    public function updateInfo()
    {
        $this->settings_storage->updateInfoSettings($this->info_settings);
    }

    /**
     * Get the member storage object used by this.
     *
     * @return ilIndividualAssessmentMembersStorage
     */
    public function membersStorage()
    {
        return $this->members_storage;
    }

    /**
     * @inheritdoc
     */
    public function initDefaultRoles()
    {
        $this->access_handler->initDefaultRolesForObject($this);
    }

    /**
     * Get the access handler of this.
     *
     * @return	IndividualAssessmentAccessHandler
     */
    public function accessHandler()
    {
        return $this->access_handler;
    }

    /**
     * @inheritdoc
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        $settings = $this->getSettings();
        $info_settings = $this->getInfoSettings();
        $new_settings = new ilIndividualAssessmentSettings(
            (int) $new_obj->getId(),
            (int) $new_obj->getTitle(),
            (int) $new_obj->getDescription(),
            $settings->getContent(),
            $settings->getRecordTemplate(),
            $settings->isEventTimePlaceRequired(),
            $settings->isFileRequired()
        );
        $new_obj->settings = $new_settings;

        $new_info_settings = new ilIndividualAssessmentInfoSettings(
            (int) $new_obj->getId(),
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
     *
     * @return ilIndividualAssessmentFileStorage
     */
    public function getFileStorage()
    {
        if ($this->file_storage === null) {
            $this->file_storage = ilIndividualAssessmentFileStorage::getInstance($this->getId());
        }
        return $this->file_storage;
    }

    /**
     * Check wether the LP is activated for current object.
     *
     * @return bool
     */
    public function isActiveLP()
    {
        if ($this->lp_active === null) {
            require_once 'Modules/IndividualAssessment/classes/LearningProgress/class.ilIndividualAssessmentLPInterface.php';
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
    public function getParentContainerIdByType($id, array $types)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $node = $tree->getParentNodeData($id);

        while ($node['type'] !== "root") {
            if (in_array($node['type'], $types)) {
                return $node['ref_id'];
            }
            $node = $tree->getParentNodeData($node['ref_id']);
        }
        return 0;
    }

    protected function getDic() : Pimple\Container
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

    public function getMembersGUI() : ilIndividualAssessmentMembersGUI
    {
        return $this->getDic()['ilIndividualAssessmentMembersGUI'];
    }

    public function getSettingsGUI() : ilIndividualAssessmentSettingsGUI
    {
        return $this->getDic()['ilIndividualAssessmentSettingsGUI'];
    }
}
