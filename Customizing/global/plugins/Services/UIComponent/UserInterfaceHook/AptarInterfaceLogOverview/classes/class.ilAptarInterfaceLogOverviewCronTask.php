<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

ilAptarInterfaceLogOverviewPlugin::getInstance()->includeClass('class.ilAptarInterfaceLogOverviewSettings.php');


/**
 * Class class.ilAptarInterfaceLogOverviewCronTask.php
 */
class ilAptarInterfaceLogOverviewCronTask
{
	protected $save_path;

	/**
	 *
	 */
	public function __construct()
	{
		$this->save_path = ilUtil::getDataDir() . '/' .ilAptarInterfaceLogOverviewPlugin::PNAME . '/';
	}

	/**
	 *
	 */
	public function run()
	{
		$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewCronTask: cleanup cron started...');
		$this->cleanup();
	}

	/**
	 *
	 */
	protected function cleanup()
	{
		if(ilAptarInterfaceLogOverviewSettings::getInstance()->isCleanupEnabled())
		{
			$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewCronTask: cleanup enabled...');
			if(is_dir($this->save_path))
			{
				$this->cleanupFiles($this->save_path, '/(.*\.log)/');
			}
			else
			{
				$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewCronTask: log dir does not exists...');
			}
		}
		else
		{
			$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewCronTask: cleanup disabled...');
		}
		$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewCronTask: cleanup cron ended.');
	}

	/**
	 * @param string $folder
	 * @param string $regexp
	 */
	protected function cleanupFiles($folder, $regexp)
	{
		$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewCronTask: Searching for expired files in: '.realpath($folder));
		ilAptarInterfaceLogOverviewPlugin::getInstance()->includeClass('class.ilAptarInterfaceLogOverviewExpiredFilesFilterIterator.php');
		$iter = new ilAptarInterfaceLogOverviewExpiredFilesFilterIterator(
			new RegexIterator(
				new DirectoryIterator(realpath($folder)),
				$regexp
			),
			strtotime(
				'-' . ilAptarInterfaceLogOverviewSettings::getInstance()->getCleanupBoundaryValue(). ' ' . ilAptarInterfaceLogOverviewSettings::getInstance()->getCleanupBoundaryUnit(),
				time()
			)
		);
		$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewCronTask: Found: '. sizeof($iter) . ' file.');
		$found = false;
		foreach($iter as $fileinfo)
		{
			/**
			 * @var $fileinfo SplFileInfo
			 */
			if(!$fileinfo->isFile())
			{
				continue;
			}
			$found = true;

			if(@unlink($fileinfo->getPathname()))
			{
				$log_id = ilAptarInterfaceLogOverviewSettings::getInstance()->getLogIdFromFilename($fileinfo->getPathname());
				$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewCronTask: Deleted file: '.$fileinfo->getPathname());
				ilAptarInterfaceLogOverviewSettings::getInstance()->removeDataFromDb(array($log_id));
				$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewCronTask: Removed entry id: '.$log_id . ' from db.');
			}
			else
			{
				$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewCronTask: Could not delete file: '.$fileinfo->getPathname());
			}
		}
		if(!$found)
		{
			$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewCronTask: No files found for cleanup');
		}
	}
}