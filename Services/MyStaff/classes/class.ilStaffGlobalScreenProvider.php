<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\StaticProvider\AbstractStaticMainMenuProvider;

/**
 * Class ilStaffGlobalScreenProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilStaffGlobalScreenProvider extends AbstractStaticMainMenuProvider {

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
		$dic = $this->dic;

		return [$this->mainmenu->link($this->if->identifier('mm_pd_mst'))
			        ->withTitle($this->dic->language()->txt("my_staff"))
			        ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToMyStaff")
			        ->withParent($this->getTopItem())
			        ->withAvailableCallable(
				        function () use ($dic) {
					        return (bool)($dic->settings()->get("enable_my_staff"));
				        }
			        )
			        ->withVisibilityCallable(
				        function () {
					        return (bool)ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff();
				        }
			        )->withNonAvailableReason($dic->ui()->factory()->legacy("{$dic->language()->txt('component_not_active')}"))];
	}
}
