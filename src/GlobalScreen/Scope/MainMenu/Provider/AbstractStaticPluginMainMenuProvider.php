<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Provider;

use ILIAS\DI\Container;
use ilPlugin;

/**
 * Class AbstractStaticPluginMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractStaticPluginMainMenuProvider extends AbstractStaticMainMenuProvider implements StaticMainMenuProvider
{

    /**
     * @var ilPlugin
     */
    protected $plugin;


    /**
     * @inheritDoc
     */
    public function __construct(Container $dic, ilPlugin $plugin)
    {
        parent::__construct($dic);
        $this->plugin = $plugin;
        $this->if = $this->globalScreen()->identification()->plugin($plugin, $this);
    }


    /**
     * @return string
     */
    final public function getProviderNameForPresentation() : string
    {
        return $this->plugin->getPluginName();
    }
}
