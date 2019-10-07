<?php

namespace ILIAS\GlobalScreen\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\PluginIdentificationProvider;
use ilPlugin;

/**
 * Class PluginProviderHelper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait PluginProviderHelper
{

    /**
     * @var ilPlugin
     */
    private $plugin;
    /**
     * @var PluginIdentificationProvider
     */
    protected $if;


    /**
     * @inheritDoc
     */
    final public function __construct(Container $dic, ilPlugin $plugin)
    {
        parent::__construct($dic);
        $this->plugin = $plugin;
        $this->if = $this->globalScreen()->identification()->plugin($plugin->getId(), $this);
    }


    /**
     * @return string
     */
    final public function getProviderNameForPresentation() : string
    {
        return $this->plugin->getPluginName();
    }


    /**
     * @inheritDoc
     */
    final public function getPluginID() : string
    {
        return $this->plugin->getId();
    }


    /**
     * @inheritDoc
     */
    final public function id() : PluginIdentificationProvider
    {
        return $this->if;
    }
}
