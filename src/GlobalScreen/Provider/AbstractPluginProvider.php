<?php namespace ILIAS\GlobalScreen\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\PluginIdentificationProvider;

/**
 * Class AbstractProvider
 *
 * @package ILIAS\GlobalScreen\Provider
 */
abstract class AbstractPluginProvider extends AbstractProvider implements PluginProvider
{

    /**
     * @var PluginIdentificationProvider
     */
    private $identification_provider;


    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->identification_provider = $dic->globalScreen()->identification()->plugin($this->getPluginID(), $this);
    }


    /**
     * @inheritDoc
     */
    abstract public function getPluginID() : string;


    /**
     * @inheritDoc
     */
    public function id() : PluginIdentificationProvider
    {
        return $this->identification_provider;
    }
}