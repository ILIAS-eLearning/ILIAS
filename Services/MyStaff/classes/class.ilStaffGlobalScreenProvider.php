<?php

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;

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


	/**
	 * @param Container $dic
	 */
	public function __construct(Container $dic) {
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
		return [
			$this->mainmenu->link($this->if->identifier('mm_pd_mst'))->withTitle($this->dic->language()->txt("my_staff"))
				->withAction("ilias.php?baseClass=" . ilPersonalDesktopGUI::class . "&cmd=jumpToMyStaff")->withParent($this->getTopItem())
				->withAvailableCallable(function (): bool {
					return boolval($this->dic->settings()->get("enable_my_staff"));
				})->withVisibilityCallable(function (): bool {
					return ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff();
				})->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
		];
	}
}
