<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class MainMenuProviderInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface MainMenuProviderInterface
{

    /**
     * @return IdentificationInterface[]
     */
    public function getAllIdentifications() : array;
}
