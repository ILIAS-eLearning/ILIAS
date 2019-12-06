<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope;

use Closure;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Interface isGlobalScreenItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isGlobalScreenItem
{

    /**
     * @return IdentificationInterface
     */
    public function getProviderIdentification() : IdentificationInterface;


    /**
     * @param Closure $component_decorator
     *
     * @return isGlobalScreenItem
     */
    public function addComponentDecorator(Closure $component_decorator) : isGlobalScreenItem;


    /**
     * @return Closure|null
     */
    public function getComponentDecorator() : ?Closure;
}
