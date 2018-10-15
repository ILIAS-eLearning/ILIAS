<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\StaticProvider\AbstractStaticMainMenuProvider;

/**
 * Class ilLPGlobalScreenProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilLPGlobalScreenProvider extends AbstractStaticMainMenuProvider {

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
		return [$this->mainmenu->link($this->if->identifier('mm_pd_lp'))
			        ->withTitle($this->dic->language()->txt("learning_progress"))
			        ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToLP")
			        ->withParent($this->getTopItem())
			        ->withAvailableCallable(
				        function () {
					        return (bool)(ilObjUserTracking::_enabledLearningProgress()
						        && (ilObjUserTracking::_hasLearningProgressOtherUsers()
							        || ilObjUserTracking::_hasLearningProgressLearner()));
				        }
			        )];
	}
}
