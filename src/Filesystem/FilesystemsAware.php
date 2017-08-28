<?php

namespace ILIAS\Filesystem;

/**
 * Trait FilesystemsAware
 *
 * Trait which ease the filesystem integration within legacy ILIAS components.
 * This trait should not be used within new components.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 */
trait FilesystemsAware {

	/**
	 * @var Filesystems $filesystems
	 */
	private static $filesystems;

	/**
	 * Returns the loaded filesystems.
	 *
	 * @return Filesystems
	 */
	private static function filesystems() {
		if(is_null(self::$filesystems)) {
			global $DIC;
			self::$filesystems = $DIC->filesystem();
		}

		return self::$filesystems;
	}

}