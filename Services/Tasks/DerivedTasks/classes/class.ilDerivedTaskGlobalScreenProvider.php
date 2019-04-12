<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;


/**
 * Main menu entry for derived tasks
 *
 * @author <killing@leifos.de>
 */
class ilDerivedTaskGlobalScreenProvider extends AbstractStaticMainMenuProvider {

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

		$dic->language()->loadLanguageModule("task");

		// derived tasks list
		$entries[] = $this->mainmenu->link($this->if->identifier('mm_derived_task_list'))
			->withTitle($this->dic->language()->txt("task_derived_tasks"))
			->withAction($dic->ctrl()->getLinkTargetByClass(["ilDerivedTasksGUI"], ""))
			->withParent($this->getTopItem())
			->withVisibilityCallable(
				function () use ($dic) {
					return true;
				}
			);
		return $entries;
	}
}
