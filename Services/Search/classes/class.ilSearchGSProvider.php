<?php

use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;

/**
 * Class ilSearchGSProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilSearchGSProvider extends AbstractStaticMetaBarProvider implements StaticMetaBarProvider {

	/**
	 * @inheritDoc
	 */
	public function getMetaBarItems(): array {
		$main_search = new ilMainMenuSearchGUI();
		$html = "";

		// user interface plugin slot + default rendering
		$uip = new ilUIHookProcessor(
			"Services/MainMenu", "main_menu_search",
			array("main_menu_gui" => $this, "main_menu_search_gui" => $main_search)
		);
		if (!$uip->replaced()) {
			$html = $main_search->getHTML();
		}
		$html = $uip->getHTML($html);

		$item = $this->globalScreen()
			->metaBar()
			->baseItem($this->if->identifier('search'))
			->withAvailableCallable(
				function () {
					return (bool)$this->dic->rbac()->system()->checkAccess('search', \ilSearchSettings::_getSearchSettingRefId());
				}
			)
			->withGlyph($this->dic->ui()->factory()->glyph()->search())
			->withTitle("Search")
			->withPosition(1)
			->withContent($this->dic->ui()->factory()->legacy($html));

		return [$item];
	}
}
