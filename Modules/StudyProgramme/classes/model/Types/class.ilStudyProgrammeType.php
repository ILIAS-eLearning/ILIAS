<?php declare(strict_types = 1);

/**
 * Class ilStudyProgrammeType
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilStudyProgrammeType
{

	/**
	 * Folder in ILIAS webdir to store the icons
	 */
	const WEB_DATA_FOLDER = 'prg_data';


	const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
	const DATE_FORMAT = 'Y-m-d';

	/**
	 * @var int
	 *
	 * @con_is_primary  true
	 * @con_sequence    true
	 * @con_is_unique   true
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $id = 0;

	/**
	 * @var string
	 *
	 * @con_has_field   true
	 * @con_fieldtype   text
	 * @con_length      4
	 */
	protected $default_lang = '';

	/**
	 * @var int
	 *
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $owner;

	/**
	 * @var DateTime
	 *
	 * @con_has_field   true
	 * @con_fieldtype   timestamp
	 * @con_is_notnull  true
	 */
	protected $create_date;

	/**
	 * @var string
	 *
	 * @con_has_field   true
	 * @con_fieldtype   timestamp
	 * @con_is_notnull  false
	 */
	protected $last_update;

	/**
	 * @var string
	 *
	 * @con_has_field   true
	 * @con_fieldtype   text
	 * @con_length      255
	 */
	protected $icon;


	/**
	 * @var array
	 */
	protected $amd_records_assigned;

	/**
	 * @var array
	 */
	protected static $amd_records_available;

	/**
	 * @var ilLog
	 */
	protected $log;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilPluginAdmin
	 */
	protected $plugin_admin;

	/**
	 * @var array
	 */
	protected $active_plugins;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var array
	 */
	protected $translations;
	/**
	 * @var array with the changed TypeTranslations
	 */
	protected $changed_translations = array();

	/**
	 * @param int $a_id
	 *
	 * @throws ilStudyProgrammeTypeException
	 */
	public function __construct(
		int $id,
		ilStudyProgrammeTypeRepository $type_repo,
		ILIAS\Filesystem\Filesystem $webdir,
		ilPluginAdmin $plugin_admin,
		ilLanguage $lng,
		ilObjUser $user
	)
	{
		$this->id = $id;
		$this->type_repo = $type_repo;
		$this->webdir = $webdir;
		$this->plugin_admin = $plugin_admin;
		$this->lng = $lng;
		$this->user = $user;
	}

	/**
	 * Get the title of an StudyProgramme type. If no language code is given, a translation in the user-language is
	 * returned. If no such translation exists, the translation of the default language is substituted.
	 * If a language code is provided, returns title for the given language or null.
	 *
	 * @param string $a_lang_code
	 *
	 * @return null|string
	 */
	public function getTitle(string $a_lang_code = '') : string
	{
		return (string)$this->getTranslation('title', $a_lang_code);
	}


	/**
	 * Set title of StudyProgramme type.
	 * If no lang code is given, sets title for default language.
	 *
	 * @param        $a_title
	 * @param string $a_lang_code
	 */
	public function setTitle(string $title, string $lang_code = '')
	{
		$lang = ($lang_code) ? $lang_code : $this->getDefaultLang();
		$this->setTranslation('title', $title, $lang);
	}


	/**
	 * Get the description of an StudyProgramme type. If no language code is given, a translation in the user-language is
	 * returned. If no such translation exists, the description of the default language is substituted.
	 * If a language code is provided, returns description for the given language or null.
	 *
	 * @param string $a_lang_code
	 *
	 * @return null|string
	 */
	public function getDescription(string $lang_code = '') : string
	{
		return (string)$this->getTranslation('description', $lang_code);
	}


	/**
	 * Set description of StudyProgramme type.
	 * If no lang code is given, sets description for default language.
	 *
	 * @param        $a_description
	 * @param string $a_lang_code
	 */
	public function setDescription(string $description, string $lang_code = '')
	{
		$lang = ($lang_code) ? $lang_code : $this->getDefaultLang();
		$this->setTranslation('description', $description, $lang);
	}


	/**
	 * Update the Icons of assigned objects.
	 *
	 * @return void
	 */
	public function updateAssignedStudyProgrammesIcons()
	{
		$obj_ids = $this->type_repo->readStudyProgrammeIdsByTypeId($this->getId());

		foreach ($obj_ids as $id) {
			$ref_id = ilObject::_getAllReferences($id);
			$osp = ilObjStudyProgramme::getInstanceByRefId(array_pop($ref_id));
			$osp->updateCustomIcon();
		}
	}


	/**
	 * Assign a given AdvancedMDRecord to this type.
	 * If the AMDRecord is already assigned, nothing is done. If the AMDRecord cannot be assigned to StudyProgrammes/Types,
	 * an Exception is thrown. Otherwise the AMDRecord is assigned (relation gets stored in DB).
	 *
	 * @param int $a_record_id
	 *
	 * @throws ilStudyProgrammeTypePluginException
	 * @throws ilStudyProgrammeTypeException
	 */
	public function assignAdvancedMDRecord(int $record_id)
	{
		$assigned_amd_records = $this->type_repo->readAssignedAMDRecordIdsByType($this->getId());
		if (!in_array($record_id, $assigned_amd_records)) {
			if (!in_array($record_id, $this->type_repo->readAllAMDRecords())) {
				throw new ilStudyProgrammeTypeException("AdvancedMDRecord with ID {$record_id} cannot be assigned to StudyProgramme types");
			}
			/** @var ilStudyProgrammeTypeHookPlugin $plugin */
			$disallowed = array();
			$titles = array();
			foreach ($this->getActivePlugins() as $plugin) {
				if (!$plugin->allowAssignAdvancedMDRecord($this->getId(), $record_id)) {
					$disallowed[] = $plugin;
					$titles[] = $plugin->getPluginName();
				}
			}
			if (count($disallowed)) {
				$msg = sprintf($this->lng->txt('prg_type_msg_assign_amd_prevented'), implode(', ', $titles));
				throw new ilStudyProgrammeTypePluginException($msg, $disallowed);
			}
			$record_ids = $assigned_amd_records;
			$record_ids[] = $record_id;

			$exists = array_shift($this->type_repo->readAMDRecordsByTypeIdAndRecordId($this->getId(),$record_id));

			if(!$exists) {
				$advanced_meta = $this->type_repo->createAMDRecord();
				$advanced_meta->setTypeId($this->getId());
				$advanced_meta->setRecId($record_id);
				$this->type_repo->updateAMDRecord($advanced_meta);
			}

			// We need to update each StudyProgramme from this type and map the selected records to object_id
			foreach ($this->type_repo->readAssignedAMDRecordIdsByType($this->getId()) as $prg_id) {
				ilAdvancedMDRecord::saveObjRecSelection($prg_id, 'prg_type', $record_ids);
			}
			$this->amd_records_assigned = null; // Force reload of assigned objects
		}
	}


	/**
	 * Deassign a given AdvancedMD record from this type.
	 *
	 * @param int $a_record_id
	 *
	 * @throws ilStudyProgrammeTypePluginException
	 */
	public function deassignAdvancedMdRecord(int $record_id)
	{
		$record_ids = $this->type_repo->readAssignedAMDRecordIdsByType($this->getId());
		$key = array_search($record_id, $record_ids);
		if ($key !== false) {
			/** @var ilStudyProgrammeTypeHookPlugin $plugin */
			$disallowed = array();
			$titles = array();
			foreach ($this->getActivePlugins() as $plugin) {
				if (!$plugin->allowDeassignAdvancedMDRecord($this->getId(), $record_id)) {
					$disallowed[] = $plugin;
					$titles[] = $plugin->getPluginName();
				}
			}
			if (count($disallowed)) {
				$msg = sprintf($this->lng->txt('prg_type_msg_deassign_amd_prevented'), implode(', ', $titles));
				throw new ilStudyProgrammeTypePluginException($msg, $disallowed);
			}
			unset($record_ids[$key]);

			foreach($this->type_repo->readAMDRecordsByTypeIdAndRecordId($this->getId(),$record_id) as $amd_record) {
				$this->type_repo->deleteAMDRecord($amd_record);
			}

			// We need to update each StudyProgramme from this type and map the selected records to object_id
			foreach ($this->type_repo->readStudyProgrammeIdsByTypeId($this->getId()) as $prg_id) {
				ilAdvancedMDRecord::saveObjRecSelection($prg_id, 'prg_type', $record_ids);
			}
			$this->amd_records_assigned = null; // Force reload of assigned objects
		}
	}


	/**
	 * Resize and store an icon file for this object
	 *
	 * @param array $file_data The array containing file information from the icon from PHPs $_FILES array
	 *
	 * @return bool
	 */
	public function processAndStoreIconFile(array $file_data) : bool
	{
		if (!$this->updateable()) {
			return false;
		}
		if (!count($file_data) || !$file_data['name']) {
			return false;
		}
		if (!$this->webdir->hasDir($this->getIconPath())) {
			$this->webdir->createDir($this->getIconPath());
		}

		$filename = $this->getIcon() ? $this->getIcon() : $file_data['name'];

		if($this->webdir->has($this->getIconPath(true))) {
			$this->webdir->delete($this->getIconPath(true));
		}

		$stream = ILIAS\Filesystem\Stream\Streams::ofResource(fopen($file_data["tmp_name"], "r"));
		$this->webdir->writeStream($this->getIconPath(true), $stream);

		return true;
	}


	/**
	 * Remove the icon file on disk
	 */
	public function removeIconFile()
	{
		if (!$this->updateable()) {
			return;
		}

		if (
			!is_null($this->getIcon()) &&
			$this->getIcon() !== ""
		) {
			$this->webdir->delete($this->getIconPath(true));
			$this->setIcon('');
		}
	}


	/**
	 * Protected
	 */

	/**
	 * Helper method to return a translation for a given member and language
	 *
	 * @param $a_member
	 * @param $a_lang_code
	 *
	 * @return null|string
	 */
	protected function getTranslation(string $member, string $lang_code)
	{
		$lang = ($lang_code) ? $lang_code : $this->user->getLanguage();

		$trans_obj = $this->loadTranslation($lang);
		if (!is_null($trans_obj)) {
			$translation = $trans_obj[$member];
			// If the translation does exist but is an empty string and there was no lang code given,
			// substitute default language anyway because an empty string provides no information
			if (!$lang_code && !$translation) {
				$trans_obj = $this->loadTranslation($this->getDefaultLang());

				return $trans_obj[$member];
			}

			return $translation;
		} else {
			// If no lang code was given and there was no translation found, return string in default language
			if (!$lang_code) {
				$trans_obj = $this->loadTranslation($this->getDefaultLang());

				return $trans_obj[$member];
			}

			return null;
		}
	}

	protected function loadTranslation(string $lang_code)
	{
		if (isset($this->translations[$lang_code])) {
			return $this->translations[$lang_code];
		} else {
			$trans_array = $this->type_repo->readTranslationsByTypeAndLang($this->getId(),$lang_code);

			//ilStudyProgrammeTypeTranslation::where(array('prg_type_id'=>$this->getId(), 'lang'=>$a_lang_code))->getArray('member', 'value');
			if (count($trans_array)) {
				$this->translations[$lang_code] = $trans_array;

				return $trans_array;
			}
		}

		return NULL;
	}

	/**
	 * Helper function to check if this type can be updated
	 *
	 * @return bool
	 */
	protected function updateable() : bool
	{
		foreach ($this->getActivePlugins() as $plugin) {
			if (!$plugin->allowUpdate($this->getId())) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Getters & Setters
	 */

	/**
	 * @param array $translations
	 */
	public function setTranslations(array $translations)
	{
		$this->translations = $translations;
	}


	protected function setTranslation($a_member, $a_value, $a_lang_code) {

		$a_value = trim($a_value);
		// If the value is identical, quit early and do not execute plugin checks
		$existing_translation = $this->getTranslation($a_member, $a_lang_code);
		if ($existing_translation == $a_value) {
			return;
		}
		// #19 Title should be unique per language
		//        if ($a_value && $a_member == 'title') {
		//            if (ilStudyProgrammeTypeTranslation::exists($this->getId(), 'title', $a_lang_code, $a_value)) {
		//                throw new ilStudyProgrammeTypeException($this->lng->txt('prg_type_msg_title_already_exists'));
		//            }
		//        }
		$disallowed = array();
		$titles = array();
		/** @var ilStudyProgrammeTypeHookPlugin $plugin */
		foreach ($this->getActivePlugins() as $plugin) {
			$allowed = true;
			if ($a_member == 'title') {
				$allowed = $plugin->allowSetTitle($this->getId(), $a_lang_code, $a_value);
			} else {
				if ($a_member == 'description') {
					$allowed = $plugin->allowSetDescription($this->getId(), $a_lang_code, $a_value);
				}
			}
			if (!$allowed) {
				$disallowed[] = $plugin;
				$titles[] = $plugin->getPluginName();
			}
		}
		if (count($disallowed)) {
			$msg = sprintf($this->lng->txt('prg_type_msg_setting_member_prevented'), $a_value, implode(', ', $titles));
			throw new ilStudyProgrammeTypePluginException($msg, $disallowed);
		}
		$trans_obj = $this->type_repo->readTranslationByTypeIdMemberLang(
			$this->getId(),
			$a_member,
			$a_lang_code
		);
		if(!$trans_obj) {
			$trans_obj = $this->type_repo->createTypeTranslation();
			$trans_obj->setPrgTypeId($this->getId());
			$trans_obj->setLang($a_lang_code);
			$trans_obj->setMember($a_member);
		}
		$trans_obj->setValue($a_value);
		$this->type_repo->updateTypeTranslation($trans_obj);
		$this->translations[$a_lang_code][$a_member] = $a_value;
		$this->changed_translations[$a_lang_code][] = $trans_obj;
	}


	/**
	 * Returns the loaded translation objects
	 *
	 * @return array
	 */
	public function getTranslations() : array
	{
		return $this->translations;
	}

	/**
	 * @param int $owner
	 */
	public function setOwner(int $owner)
	{
		$this->owner = $owner;
	}


	/**
	 * @return int
	 */
	public function getOwner()
	{
		return $this->owner;
	}


	/**
	 * @param string $last_update
	 */
	public function setLastUpdate($last_update)
	{
		$this->last_update = $last_update;
	}


	/**
	 * @return string
	 */
	public function getLastUpdate() {
		return $this->last_update;
	}


	/**
	 * @return int
	 */
	public function getId() : int
	{
		return $this->id;
	}


	/**
	 * Set new Icon filename.
	 *
	 * Note that if you did also send a new icon image file with a form, make sure to call
	 * ilStudyProgrammeType::processAndStoreIconFile() to store the file additionally on disk.
	 *
	 * If you want to delete the icon, set call ilStudyProgrammeType::removeIconFile() first and set an empty string here.
	 *
	 * @param string $icon
	 *
	 * @throws ilStudyProgrammeTypeException
	 */
	public function setIcon(string $icon)
	{
		if ($icon AND !preg_match('/\.(svg)$/', $icon)) {
			throw new ilStudyProgrammeTypeException('Icon must be set with file extension svg');
		}
		$this->icon = $icon;
	}


	/**
	 * @return string
	 */
	public function getIcon()
	{
		return $this->icon;
	}


	/**
	 * Return the path to the icon
	 *
	 * @param bool $append_filename If true, append filename of icon
	 *
	 * @return string
	 */
	public function getIconPath(bool $append_filename = false)
	{
		$path = self::WEB_DATA_FOLDER . '/' . 'type_' . $this->getId() . '/';
		if ($append_filename) {
			$path .= $this->getIcon();
		}

		return $path;
	}


	/**
	 * @param string $default_lang
	 *
	 * @throws ilStudyProgrammeTypePluginException
	 */
	public function setDefaultLang(string $default_lang)
	{
		$this->default_lang = $default_lang;
	}


	/**
	 * @return string
	 */
	public function getDefaultLang() : string
	{
		return $this->default_lang;
	}


	/**
	 * @param string $create_date
	 */
	public function setCreateDate(DateTime $create_date)
	{
		$this->create_date = $create_date;
	}


	/**
	 * @return string
	 */
	public function getCreateDate()
	{
		return $this->create_date;
	}

	public function getRepository() : ilStudyProgrammeTypeRepository
	{
		return $this->type_repo;
	}

	protected function getActivePlugins() {
		if ($this->active_plugins === NULL) {
			$active_plugins = $this->plugin_admin->getActivePluginsForSlot(IL_COMP_MODULE, 'StudyProgramme', 'prgtypehk');
			$this->active_plugins = array();
			foreach ($active_plugins as $pl_name) {
				/** @var ilStudyProgrammeTypeHookPlugin $plugin */
				$plugin = $this->plugin_admin->getPluginObject(IL_COMP_MODULE, 'StudyProgramme', 'prgtypehk', $pl_name);
				$this->active_plugins[] = $plugin;
			}
		}

		return $this->active_plugins;
	}

	public function changedTranslations() : array
	{
		return $this->changed_translations;
	}

}
