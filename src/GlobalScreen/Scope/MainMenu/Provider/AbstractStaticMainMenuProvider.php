<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationProviderInterface;
use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;

/**
 * Interface StaticMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractStaticMainMenuProvider extends AbstractProvider implements StaticMainMenuProvider
{

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
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->mainmenu = $this->globalScreen()->mainmenu();
        $this->if = $this->globalScreen()->identification()->core($this);
    }


    /**
     * @inheritDoc
     */
    public function getAllIdentifications() : array
    {
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
     * @inheritDoc
     */
    public function provideTypeInformation() : TypeInformationCollection
    {
        return new TypeInformationCollection();
    }
}
