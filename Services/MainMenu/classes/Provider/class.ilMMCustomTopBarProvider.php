<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;

/**
 * Class ilMMCustomTopBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMCustomTopBarProvider extends AbstractStaticMetaBarProvider implements StaticMetaBarProvider {

	/**
	 * @inheritDoc
	 */
	public function getMetaBarItems(): array {
		$f = $this->dic->ui()->factory();
		$txt = function ($id) {
			return $this->dic->language()->txt($id);
		};
		$mb = $this->globalScreen()->metaBar();
		$id = function ($id): IdentificationInterface {
			return $this->if->identifier($id);
		};

		$item[] = $mb->topLegacyItem($id('help'))
			->withLegacyContent($f->legacy("NOT PROVIDED"))
			->withGlyph($f->glyph()->help())
			->withTitle("Help")
			->withPosition(2);

		$item[] = $mb->topLegacyItem($id('notifications'))
			->withLegacyContent($f->legacy("NOT PROVIDED"))
			->withGlyph($f->glyph()->notification()->withCounter($f->counter()->novelty(3)))
			->withTitle("Notifications")
			->withVisibilityCallable(
				function () {
					return !$this->dic->user()->isAnonymous();
				}
			)
			->withPosition(3);

		$children = array();
		$children[] = $mb->linkItem($id('personal_profile'))
			->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToProfile")
			->withTitle($txt("personal_profile"))
			->withPosition(1)
			->withGlyph($f->glyph()->user());

		$children[] = $mb->linkItem($id('personal_settings'))
			->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSettings")
			->withTitle($txt("personal_settings"))
			->withPosition(2)
			->withGlyph($f->glyph()->settings());

		$children[] = $mb->linkItem($id('logout'))
			->withAction("logout.php?lang=" . $this->dic->user()->getCurrentLanguage())
			->withPosition(3)
			->withTitle($txt("logout"))
			->withGlyph($f->glyph()->remove());

		$item[] = $mb
			->topParentItem($id('user'))
			->withGlyph($f->glyph()->user())
			->withTitle("User")
			->withPosition(4)
			->withVisibilityCallable(
				function () {
					return !$this->dic->user()->isAnonymous();
				}
			)
			->withChildren($children);

		return $item;
	}
}
