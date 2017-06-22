<?php

namespace ILIAS\FileUpload\Processor;

/**
 * Class PreProcessorManagerFactory
 *
 * The main reason of this factory is to decouple other parts of ILIAS from the PreProcessorManager
 * implementation.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 */
interface PreProcessorManagerFactory {

	/**
	 * Returns a fresh instance of the pre processor manager.
	 *
	 * @return PreProcessorManager
	 * @since 5.3
	 *
	 * @public
	 */
	public function getInstance();
}