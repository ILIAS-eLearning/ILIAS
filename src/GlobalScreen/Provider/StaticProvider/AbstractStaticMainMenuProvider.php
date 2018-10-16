<?php namespace ILIAS\GlobalScreen\Provider\StaticProvider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationProviderInterface;
use ILIAS\GlobalScreen\MainMenu\MainMenuItemFactory;
use ILIAS\GlobalScreen\Provider\AbstractProvider;

/**
 * Interface StaticMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractStaticMainMenuProvider extends AbstractProvider implements StaticMainMenuProvider {

	/**
	 * @var Container
	 */
	protected $dic;
	/**
	 * @var IdentificationProviderInterface
	 */
	protected $if;
	/**
	 * @var MainMenuItemFactory
	 */
	protected $mainmenu;


	/**
	 * @inheritDoc
	 */
	public function __construct(Container $dic) {
		parent::__construct($dic);
		$this->mainmenu = $this->globalScreen()->mainmenu();
		$this->if = $this->globalScreen()->identification()->core($this);
	}


	/**
	 * @inheritDoc
	 */
	public function getAllIdentifications(): array {
		$ids = [];
		foreach ($this->getStaticTopItems() as $slate) {
			$ids[] = $slate->getProviderIdentification();
		}
		foreach ($this->getStaticSubItems() as $entry) {
			$ids[] = $entry->getProviderIdentification();
		}

		return $ids;
	}


	/**
	 * @return string
	 */
	public function getProviderNameForPresentation(): string {
		$reflector = new \ReflectionClass($this);

		$re = '/.*\/(?P<provider>(Services|Modules)\/.*)\/classes/m';

		preg_match($re, $reflector->getFileName(), $matches);

		return $matches[1];
	}
}
