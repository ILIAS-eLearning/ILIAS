<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Types\SingleType;

/**
 * Description of class class
 *
 * @author killing@leifos.de
 *
 */
class ilCheckSumOfWorkspaceFileSizesJob extends AbstractJob {

	/**
	 * @var |null
	 */
	private $logger = null;
	/**
	 * @var ilSetting
	 */
	protected $settings; // [ilSetting]

	/**
	 * @var ilWorkspaceTree
	 */
	protected $tree;

	/**
	 * Construct
	 */
	public function __construct() {
		global $DIC;

		$user = $DIC->user();

		$this->logger = ilLoggerFactory::getLogger("pwsp");
		$this->settings = new ilSetting("fold");
		$this->tree = new ilWorkspaceTree($user->getId());
	}


	/**
	 * @inheritDoc
	 */
	public function getInputTypes() {
		return
			[
				new SingleType(ilWorkspaceCopyDefinition::class),
			];
	}


	/**
	 * @inheritDoc
	 */
	public function getOutputType() {
		return new SingleType(ilWorkspaceCopyDefinition::class);
	}


	/**
	 * @inheritDoc
	 */
	public function isStateless() {
		return true;
	}


	/**
	 * @inheritDoc
	 * @todo use filesystem service
	 */
	public function run(array $input, \ILIAS\BackgroundTasks\Observer $observer) {
		$this->logger->debug('Start checking adherence to maxsize!');
		$this->logger->dump($input);
		$definition = $input[0];
		$object_wps_ids = $definition->getObjectWspIds();

		// get global limit (max sum of individual file-sizes) from file settings
		$size_limit = (int)$this->settings->get("bgtask_download_limit", 0);
		$size_limit_bytes = $size_limit * 1024 * 1024;
		$this->logger->debug('Global limit (max sum of all file-sizes) in file-settings: ' . $size_limit_bytes . ' bytes');
		// get sum of individual file-sizes
		$total_bytes = 0;
		$this->calculateRecursive($object_wps_ids, $total_bytes);
		$this->logger->debug('Calculated sum of all file-sizes: ' . $total_bytes . 'MB');
		// check if calculated total size adheres top global limit
		$adheres_to_limit = new BooleanValue();
		$adheres_to_limit->setValue(true);
		if ($total_bytes > $size_limit_bytes) {
			$adheres_to_limit->setValue(false);
		}

		$definition->setSumFileSizes($total_bytes);
		$definition->setAdheresToLimit($adheres_to_limit);

		return $definition;
	}


	/**
	 * Calculates the number and size of the files being downloaded recursively.
	 *
	 * @param array $a_ref_ids
	 * @param int & $a_file_count
	 * @param int & $a_file_size
	 */
	protected function calculateRecursive($object_wps_ids, &$a_file_size) {
		global $DIC;
		$tree = $DIC['tree'];

		// parse folders
		foreach ($object_wps_ids as $object_wps_id) {
			if (!$this->validateAccess($object_wps_id)) {
				continue;
			}

			// we are only interested in folders and files
			$obj_id = $this->tree->lookupObjectId($object_wps_id);
			$type = ilObject::_lookupType($obj_id);
			switch ($type) {
				case "wfld":
					// get child objects
					$subtree = $tree->getChildsByTypeFilter($object_wps_id, array("wfld", "file"));
					if (count($subtree) > 0) {
						$child_ref_ids = array();
						foreach ($subtree as $child) {
							$child_wsp_ids[] = $child["child"];
						}
						$this->calculateRecursive($child_wsp_ids, $a_file_size);
					}
					break;

				case "file":
					$a_file_size += ilObjFileAccess::_lookupFileSize($obj_id);
					break;
			}
		}
	}


	/**
	 * Check file access
	 *
	 * @param int $wsp_id
	 *
	 * @return boolean
	 */
	protected function validateAccess($wsp_id) {
		$ilAccess = new ilWorkspaceAccessHandler($this->tree);

		if (!$ilAccess->checkAccess("read", "", $wsp_id)) {
			return false;
		}

		return true;
	}


	/**
	 * @inheritdoc
	 */
	public function getExpectedTimeOfTaskInSeconds() {
		return 30;
	}
}