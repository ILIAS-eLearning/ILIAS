<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Provider;

use ILIAS\GlobalScreen\Provider\StaticProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;

/**
 * Interface StaticMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StaticMainMenuProvider extends StaticProvider, MainMenuProviderInterface {

	const PURPOSE_MAINBAR = 'mainmenu';


	/**
	 * @return TopParentItem[] These are Slates which will be
	 * available for configuration and will be collected once during a
	 * StructureReload.
	 */
	public function getStaticTopItems(): array;


	/**
	 * @return isItem[] These are Entries which will be available for
	 * configuration and will be collected once during a StructureReload
	 */
	public function getStaticSubItems(): array;


	/**
	 * This method can be used to add new types of Main Menu entries.
	 * E.g.:
	 * $c = new TypeInformationCollection();
	 * $c->add(new TypeInformation(ilExampleType::class, "Example Description",new ilExampleRenderer(),
	 * 		new ilExampleTypeHandler($this->if->identifier("parent_role_entry")))
	 * );
	 * Adds an new Example Type the list of available Main menu entries in the admistration.
	 *
	 * @return TypeInformationCollection
	 */
	public function provideTypeInformation(): TypeInformationCollection;
}
