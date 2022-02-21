<?php namespace ILIAS\GlobalScreen\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\PluginIdentificationProvider;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class AbstractProvider
 * @package ILIAS\GlobalScreen\Provider
 */
abstract class AbstractPluginProvider extends AbstractProvider implements PluginProvider
{
    private PluginIdentificationProvider $identification_provider;
    
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
