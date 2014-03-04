<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Administration/interfaces/interface.ilVersionControlInformation.php';

/**
 * Class ilSubversionInformation
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSubversionInformation implements ilVersionControlInformation
{
	/**
	 * @var string
	 */
	const SVN_GET_17_FILE = '.svn/wc.db';

	/**
	 * @var string
	 */
	const SVN_LT_17_FILE  = '.svn/entries';
	
	/**
	 * @var string
	 */
	private static $revision_information = null;

	/**
	 * @param $revision
	 * @return bool
	 */
	private static function isSvnRevision($revision)
	{
		return (bool)preg_match('/^\d+(:\d+)*[MSP]*$/', $revision);
	}

	/**
	 * @return bool
	 */
	private static function isProbablySubversion17()
	{
		return file_exists(self::SVN_GET_17_FILE) && is_file(self::SVN_GET_17_FILE) && is_readable(self::SVN_GET_17_FILE);
	}

	/**
	 * @return bool
	 */
	private static function isProbablySubversionLower17()
	{
		return file_exists(self::SVN_LT_17_FILE) && is_file(self::SVN_LT_17_FILE) && is_readable(self::SVN_LT_17_FILE);
	}

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

		if(self::isProbablySubversion17())
		{
			if(extension_loaded('PDO') && extension_loaded('pdo_sqlite'))
			{
				try
				{
					$wcdb = new PDO('sqlite:' . self::SVN_GET_17_FILE);

					$result = $wcdb->query('SELECT MAX("revision") current_rev FROM "NODES"');
					if($result)
					{
						foreach($result as $row)
						{
							$revision = $row['current_rev'];
							if(self::isSvnRevision($revision))
							{
								$info[] = sprintf($lng->txt('svn_revision_current'), $revision);
							}
							break;
						}
					}

					$result = $wcdb->query('SELECT "changed_revision" last_changed_revision FROM "NODES" ORDER BY changed_revision DESC LIMIT 1');
					if($result)
					{
						foreach($result as $row)
						{
							$revision = $row['last_changed_revision'];
							if(self::isSvnRevision($revision))
							{
								$info[] = sprintf($lng->txt('svn_revision_last_change'), $revision);
							}
							break;
						}
					}

					$result = $wcdb->query('SELECT * FROM REPOSITORY ');
					if($result)
					{
						foreach($result as $row)
						{
							$info[] = sprintf($lng->txt('svn_root'), $row['root']);
						}
					}

					$result = $wcdb->query('SELECT * FROM "NODES" WHERE local_relpath LIKE "%inc.ilias_version.php"');
					if($result)
					{
						foreach($result as $row)
						{
							$path = dirname(dirname($row['repos_path']));
							if($path)
							{
								$info[] = sprintf($lng->txt('svn_path'), $path);
							}
						}
					}
				}
				catch(Exception $e) {}
			}
		}
		else
		{
			if(function_exists('shell_exec') && is_callable('shell_exec'))
			{
				$revision = trim(shell_exec('svnversion ' . realpath(getcwd())));
				if(self::isSvnRevision($revision))
				{
					$info[] = sprintf($lng->txt('svn_revision_current'), $revision);
				}
			}
			if(self::isProbablySubversionLower17())
			{
				$svnfile  = file(self::SVN_LT_17_FILE);
				$revision = $svnfile[3];
				if(self::isSvnRevision($revision))
				{
					$info[] = sprintf($lng->txt('svn_revision_last_change'), $revision);
				}
			}
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
