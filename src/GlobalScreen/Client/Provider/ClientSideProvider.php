<?php namespace ILIAS\GlobalScreen\Client;

use ILIAS\GlobalScreen\Provider\Provider;

/**
 * Class ClientSideProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ClientSideProvider extends Provider
{

    /**
     * @return string
     */
    public function getClientSideProviderName() : string;
}
