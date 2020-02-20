<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Provider;

/**
 * Class MetaBarProviderInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface MetaBarProviderInterface
{

    /**
     * @return string
     */
    public function getProviderNameForPresentation() : string;
}
