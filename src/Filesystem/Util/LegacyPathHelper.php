<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Util;

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\FilesystemsAware;

/**
 * Class LegacyPathHelper
 *
 * The legacy path helper provides convenient functions for the integration of the filesystem service within legacy components.
 * This class should be deprecated with ILIAS 5.5 or earlier.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 */
final class LegacyPathHelper {

	use FilesystemsAware;

	/**
	 * Tries to fetch the filesystem responsible for the absolute path.
	 * Please note that the function is case sensitive.
	 *
	 * Relative paths are also detected for the ILIAS web storage like './data/default'
	 *
	 *
	 * @param string $absolutePath          The absolute used for the filesystem search.
	 * @return Filesystem                   The responsible filesystem for the given path.
	 *
	 * @throws \InvalidArgumentException    Thrown if no filesystem is responsible for the given path.
	 */
	public static function deriveFilesystemFrom(string $absolutePath): Filesystem {

		switch (true) {
			case strpos($absolutePath, CLIENT_DATA_DIR . "/temp") === 0:
			case strpos($absolutePath, realpath(CLIENT_DATA_DIR . "/temp")) === 0:
				return self::filesystems()->temp();

			//ILIAS has a lot of cases were a relative web path is used eg ./data/default
			case strpos($absolutePath, ILIAS_WEB_DIR . '/' . CLIENT_ID) === 0:
			case strpos($absolutePath, realpath(ILIAS_WEB_DIR . '/' . CLIENT_ID)) === 0:
			case strpos($absolutePath, './' . ILIAS_WEB_DIR . '/' . CLIENT_ID) === 0:
			case strpos($absolutePath, CLIENT_WEB_DIR) === 0:
			case strpos($absolutePath, realpath(CLIENT_WEB_DIR)) === 0:
				return self::filesystems()->web();
			case strpos($absolutePath, CLIENT_DATA_DIR) === 0:
			case strpos($absolutePath, realpath(CLIENT_DATA_DIR)) === 0:
				return self::filesystems()->storage();
			case strpos($absolutePath, ILIAS_ABSOLUTE_PATH . '/Customizing') === 0:
			case strpos($absolutePath, realpath(ILIAS_ABSOLUTE_PATH . '/Customizing')) === 0:
				return self::filesystems()->customizing();
			default:
				throw new \InvalidArgumentException("Invalid path supplied. Path must start with the web, storage, temp, customizing or libs storage location. Path given: {$absolutePath}");
		}
	}


	/**
	 * Creates a relative path from an absolute path which starts with a valid storage location.
	 * The primary use case for this method is to trim the path after the filesystem was fetch via the deriveFilesystemFrom method.
	 *
	 * @param string $absolutePath          The path which should be trimmed.
	 * @return string                       The trimmed relative path.
	 *
	 * @throws \InvalidArgumentException    Thrown if the path does not start with a valid storage location.
	 *
	 * @see LegacyPathHelper::deriveFilesystemFrom()
	 */
	public static function createRelativePath(string $absolutePath): string {

		$web = CLIENT_WEB_DIR;
		$webRelativeWithLeadingDot = './' . ILIAS_WEB_DIR . '/' . CLIENT_ID;
		$webRelativeWithoutLeadingDot = ILIAS_WEB_DIR . '/' . CLIENT_ID;
		$storage = CLIENT_DATA_DIR;
		$customizing = ILIAS_ABSOLUTE_PATH . '/Customizing';
		$temp = CLIENT_DATA_DIR . "/temp";

		switch (true) {
			//ILIAS has a lot of cases were a relative web path is used eg ./data/default
			case $webRelativeWithoutLeadingDot === $absolutePath:
			case realpath($webRelativeWithoutLeadingDot) === $absolutePath:
				return "";
			case strpos($absolutePath, $webRelativeWithoutLeadingDot) === 0:
				return substr($absolutePath, strlen($webRelativeWithoutLeadingDot)  + 1);           //also remove the trailing slash
			case strpos($absolutePath, realpath($webRelativeWithoutLeadingDot)) === 0:
				return substr($absolutePath, strlen(realpath($webRelativeWithoutLeadingDot))  + 1);           //also remove the trailing slash
			case $webRelativeWithLeadingDot === $absolutePath:
			case realpath($webRelativeWithLeadingDot) === $absolutePath:
				return "";
			case strpos($absolutePath, $webRelativeWithLeadingDot) === 0:
				return substr($absolutePath, strlen($webRelativeWithLeadingDot)  + 1);              //also remove the trailing slash
			case strpos($absolutePath, realpath($webRelativeWithLeadingDot)) === 0:
				return substr($absolutePath, strlen(realpath($webRelativeWithLeadingDot))  + 1);              //also remove the trailing slash
			case $web === $absolutePath:
			case realpath($web) === $absolutePath:
				return "";
			case strpos($absolutePath, $web) === 0:
				return substr($absolutePath, strlen($web)  + 1);                                    //also remove the trailing slash
			case strpos($absolutePath, realpath($web)) === 0:
				return substr($absolutePath, strlen(realpath($web))  + 1);                                    //also remove the trailing slash
			case $temp === $absolutePath:
			case realpath($temp) === $absolutePath:
				return "";
			case strpos($absolutePath, $temp) === 0:
				return substr($absolutePath, strlen($temp) + 1);                                    //also remove the trailing slash
			case strpos($absolutePath, realpath($temp)) === 0:
				return substr($absolutePath, strlen(realpath($temp)) + 1);                                    //also remove the trailing slash
			case $storage === $absolutePath:
			case realpath($storage) === $absolutePath:
				return "";
			case strpos($absolutePath, $storage) === 0:
				return substr($absolutePath, strlen($storage) + 1);                                 //also remove the trailing slash
			case strpos($absolutePath, realpath($storage)) === 0:
				return substr($absolutePath, strlen(realpath($storage)) + 1);                                 //also remove the trailing slash
			case $customizing === $absolutePath:
			case realpath($customizing) === $absolutePath:
				return "";
			case strpos($absolutePath, $customizing) === 0:
				return substr($absolutePath, strlen($customizing) + 1);                             //also remove the trailing slash
			case strpos($absolutePath, realpath($customizing)) === 0:
				return substr($absolutePath, strlen(realpath($customizing)) + 1);                             //also remove the trailing slash
			default:
				throw new \InvalidArgumentException("Invalid path supplied. Path must start with the web, storage, temp, customizing or libs storage location. Path given: {$absolutePath}");
		}
	}


}