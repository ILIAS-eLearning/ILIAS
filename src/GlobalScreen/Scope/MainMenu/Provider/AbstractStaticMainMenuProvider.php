<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationProviderInterface;
use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Interface StaticMainMenuProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractStaticMainMenuProvider extends AbstractProvider implements StaticMainMenuProvider
{
    protected Container $dic;
    protected IdentificationProviderInterface $if;
    protected MainMenuItemFactory $mainmenu;
    
    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->mainmenu = $this->globalScreen()->mainBar();
        $this->if       = $this->globalScreen()->identification()->core($this);
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
