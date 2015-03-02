<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Administration/interfaces/interface.ilVersionControlInformation.php';

/**
 * Class ilGitInformation
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilGitInformation implements ilVersionControlInformation
{
	/**
	 * @var string
	 */
	private static $revision_information = null;

	/**
	 *
	 */
	private static function detect()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		if(null !== self::$revision_information)
		{
			return self::$revision_information;
		}

		$info = array();

		// https://gist.github.com/reiaguilera/82d164c7211e299d63ac
		if(!ilUtil::isWindows())
		{
			$version_mini_hash = ilUtil::execQuoted('git describe --always');
			$version_number    = ilUtil::execQuoted('git rev-list HEAD | wc -l');
			$line              = ilUtil::execQuoted('git log -1');

			if($version_number[0])
			{
				$version_number = $version_number[0];
			}

			if($version_mini_hash[0])
			{
				$version_mini_hash = $version_mini_hash[0];
			}

			if($line && array_filter($line))
			{
				$line = implode(' | ', array_filter($line));
			}
		}
		else
		{
			$version_mini_hash = trim(exec('git describe --always'));
			$version_number    = exec('git rev-list HEAD | find /v /c ""');
			$line              = trim(exec('git log -1'));
		}

		if($version_number)
		{
			$info[] = sprintf($lng->txt('git_revision'), $version_number);
		}

		if($version_mini_hash)
		{
			$info[] = sprintf($lng->txt('git_hash_short'), $version_mini_hash);
		}

		if($line)
		{
			$info[] = sprintf($lng->txt('git_last_commit'), $line);
		}

		self::$revision_information = $info;
	}

	/**
	 * @return string
	 */
	public function getInformationAsHtml()
	{
		self::detect();

		return implode("<br />", self::$revision_information);
	}
}
