<?php

namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Identification\PluginIdentificationProvider;

/**
 * Interface PluginProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface PluginProvider extends Provider
{

    /**
     * @return string
     */
    public function getPluginID() : string;


    /**
     * @return PluginIdentificationProvider
     */
    public function id() : PluginIdentificationProvider;
}
