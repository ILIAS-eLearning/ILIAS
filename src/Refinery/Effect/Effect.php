<?php declare(strict_types=1);

/**
 * @author Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Refinery\Effect;

/**
 * This interface represents a semantic measure to indicate an intentional side effect.
 * This interface can be used as a return value from Transformation::transform(...) to keep a neccessary side effect out of the transformation itself.
 * The interface is NOT required to have a side effect.
 */
interface Effect
{
    /**
     * @return mixed // can return everything
     */
    public function value();
}
