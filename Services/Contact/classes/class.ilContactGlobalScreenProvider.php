<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\StaticProvider\AbstractStaticMainMenuProvider;

/**
 * Class ilContactGlobalScreenProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilContactGlobalScreenProvider extends AbstractStaticMainMenuProvider {

	/**
	 * @var IdentificationInterface
	 */
	protected $top_item;


	public function __construct(\ILIAS\DI\Container $dic) {
		parent::__construct($dic);
		$this->top_item = (new ilPDGlobalScreenProvider($dic))->getTopItem();
	}


	/**
	 * Some other components want to provide Items for the main menu which are
	 * located at the PD TopTitem by default. Therefore we have to provide our
	 * TopTitem Identification for others
	 *
	 * @return IdentificationInterface
	 */
	public function getTopItem(): IdentificationInterface {
		return $this->top_item;
	}


	/**
	 * @inheritDoc
	 */
	public function getStaticTopItems(): array {
		return [];
	}


	/**
	 * @inheritDoc
	 */
	public function getStaticSubItems(): array {
		return [$this->mainmenu->link($this->if->identifier('mm_pd_contacts'))
			        ->withTitle($this->dic->language()->txt("mail_addressbook"))
			        ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToContacts")
			        ->withParent($this->getTopItem())
			        ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
			        ->withAvailableCallable(
				        function () {
					        return (bool)(ilBuddySystem::getInstance()->isEnabled());
				        }
			        )];
	}
}
