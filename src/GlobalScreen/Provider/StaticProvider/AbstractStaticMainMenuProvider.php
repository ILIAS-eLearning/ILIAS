<?php namespace ILIAS\GlobalScreen\Provider\StaticProvider;

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
	public function inject(\ILIAS\GlobalScreen\Services $services) {
		parent::inject($services);
		$this->mainmenu = $this->globalScreen()->mainmenu();
		$this->if = $this->globalScreen()->identification()->core($this);
	}


	/**
	 * @inheritDoc
	 */
	public function getAllIdentifications(): array {
		$ids = [];
		foreach ($this->getStaticSlates() as $slate) {
			$ids[] = $slate->getProviderIdentification();
		}
		foreach ($this->getStaticEntries() as $entry) {
			$ids[] = $entry->getProviderIdentification();
		}

		return $ids;
	}
}
