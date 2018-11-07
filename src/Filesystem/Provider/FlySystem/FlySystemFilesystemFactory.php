<?php

namespace ILIAS\Filesystem\Provider\FlySystem;

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\Provider\FilesystemFactory;

/**
 * Class FlySystemFilesystemFactory
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 */
class FlySystemFilesystemFactory implements FilesystemFactory {

	/**
	 * @inheritDoc
	 */
	public function getLocal(LocalConfig $config, $read_only = false) {
		$localFactory = new FlySystemLocalFilesystemFactory();
		return $localFactory->getInstance($config);
	}
}