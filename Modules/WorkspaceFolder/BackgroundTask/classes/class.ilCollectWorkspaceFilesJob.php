<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;

/**
 * Description of class class
 *
 * @author killing@leifos.de
 *
 */
class ilCollectWorkspaceFilesJob extends AbstractJob {
	private $logger = null;

	/**
	 * @var ilWorkspaceTree
	 */
	protected $tree;

	/**
	 * Construct
	 */
	public function __construct()
	{
		global $DIC;

		$user = $DIC->user();

		$this->logger = ilLoggerFactory::getLogger("pwsp");
		$this->tree = new ilWorkspaceTree($user->getId());
	}

	/**
	 * @inheritDoc
	 */
	public function getInputTypes()
	{
		return
			[
				new SingleType(ilWorkspaceCopyDefinition::class),
				new SingleType(BooleanValue::class)
			];
	}

	/**
	 * @inheritDoc
	 */
	public function getOutputType()
	{
		return new SingleType(ilWorkspaceCopyDefinition::class);
	}

	/**
	 * @inheritDoc
	 */
	public function isStateless()
	{
		return true;
	}

	/**
	 * @inheritDoc
	 * @todo use filsystem service
	 */
	public function run(array $input, \ILIAS\BackgroundTasks\Observer $observer)
	{
		$this->logger->debug('Start collecting files!');
		$this->logger->dump($input);
		$definition = $input[0];
		$initiated_by_folder_action = $input[1]->getValue();
		$object_wps_ids = $definition->getObjectWspIds();
		$files = array();

		foreach ($object_wps_ids as $object_wps_id)
		{
			$obj_id  = $this->tree->lookupObjectId($object_wps_id);
			$object_type = ilObject::_lookupType($obj_id);
			$object_name = ilObject::_lookupTitle($obj_id);
			$object_temp_dir = ""; // empty as content will be added in recurseFolder and getFileDirs

			if ($object_type == "wfld")
			{
				$num_recursions = 0;
				$files_from_folder = $this->recurseFolder($object_wps_id, $object_name, $object_temp_dir, $num_recursions, $initiated_by_folder_action);
				$files = array_merge($files, $files_from_folder);
			}
			else if ( ($object_type == "file") AND ($this->getFileDirs($object_wps_id, $object_name, $object_temp_dir) != false))
			{
				$files[] = $this->getFileDirs($object_wps_id, $object_name, $object_temp_dir);
			}
		}
		$this->logger->debug('Collected files:');
		$this->logger->dump($files);

		$num_files = 0;
		foreach($files as $file)
		{
			$definition->addCopyDefinition($file['source_dir'], $file['target_dir']);
			$this->logger->debug('Added new copy definition: ' . $file['source_dir'] . ' -> '. $file['target_dir']);

			// count files only (without empty directories)
			$is_empty_folder = preg_match_all("/\/$/", $file['target_dir']);
			if(!$is_empty_folder)
			{
				$num_files++;
			}
		}
		$definition->setObjectWspIds($object_wps_ids);
		$definition->setNumFiles($num_files);

		return $definition;
	}

	private function getFileDirs($a_wsp_id, $a_file_name, $a_temp_dir)
	{
		global $DIC;

		$user = $DIC->user();
		$ilAccess = new ilWorkspaceAccessHandler($this->tree);
		if ($ilAccess->checkAccessOfUser($this->tree, $user->getId(), "read", "", $a_wsp_id))
		{
			$file = new ilObjFile($this->tree->lookupObjectId($a_wsp_id), false);
			$source_dir = $file->getDirectory($file->getVersion())."/".$file->getFileName();
			if (@!is_file($source_dir))
			{
				$source_dir = $file->getDirectory()."/".$file->getFileName();
			}
			$target_dir = $a_temp_dir . '/' . ilUtil::getASCIIFilename($a_file_name);

			return $file_dirs = [
				"source_dir" => $source_dir,
				"target_dir" => $target_dir
			];
		}
		return false;
	}

	/**
	 * @param $a_wsp_id
	 * @param $a_folder_name
	 * @param $a_temp_dir
	 * @param $a_num_recursions
	 * @param $a_initiated_by_folder_action
	 * @return array
	 */
	private function recurseFolder($a_wsp_id, $a_folder_name, $a_temp_dir, $a_num_recursions, $a_initiated_by_folder_action)
	{
		$num_recursions = $a_num_recursions + 1;
		$tree = $this->tree;
		$ilAccess = new ilWorkspaceAccessHandler($this->tree);
		$files = array();

		// Avoid the duplication of the uppermost folder when the download is initiated via a folder's action drop-down
		// by not including said folders name in the temp_dir path.
		if( ($num_recursions <= 1) AND ($a_initiated_by_folder_action) )
		{
			$temp_dir = $a_temp_dir;
		}
		else
		{
			$temp_dir = $a_temp_dir . '/' . ilUtil::getASCIIFilename($a_folder_name);
		}


		$subtree = $tree->getChildsByTypeFilter($a_wsp_id, array("wfld","file"));

		foreach ($subtree as $child)
		{
			if (!$ilAccess->checkAccess("read", "", $child["child"]))
			{
				continue;
			}
			if ($child["type"] == "wfld")
			{
				$files_from_folder = $this->recurseFolder($child["child"], $child['title'], $temp_dir, $num_recursions, $a_initiated_by_folder_action);
				$files = array_merge($files, $files_from_folder);
			}
			else if ( ($child["type"] == "file") AND ($this->getFileDirs($child["child"], $child['title'], $temp_dir) != false) )
			{
				$files[] = $this->getFileDirs($child["ref_id"], $child['title'], $temp_dir);
			}
		}
		// ensure that empty folders are also contained in the downloaded zip
		if(empty($subtree))
		{
			$files[] = [
				"source_dir" => "",
				"target_dir" => $temp_dir . '/'
			];
		}
		return $files;
	}

	/**
	 * @inheritdoc
	 */
	public function getExpectedTimeOfTaskInSeconds() {
		return 30;
	}
}