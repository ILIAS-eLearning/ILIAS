<?php namespace ILIAS\Collector;

use Composer\Script\Event;
use ILIAS\Collector\IO\ComposerIO;
use ILIAS\Collector\IO\IOInterface;

/**
 * Class AbstractComposerEventHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractComposerEventHandler implements EventHandler {

	/**
	 * @var
	 */
	private $io;
	/**
	 * @var Event
	 */
	private $composer_event;


	/**
	 * AbstractComposerEventHandler constructor.
	 *
	 * @param Event $composer_event
	 */
	public function __construct(Event $composer_event) {
		$this->composer_event = $composer_event;
		$this->io = new ComposerIO($composer_event->getIO());
	}


	/**
	 * @inheritDoc
	 */
	public function IO(): IOInterface {
		return $this->io;
	}
}
