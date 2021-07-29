<?php declare(strict_types=1);

namespace ILIAS\HTTP\Wrapper;

use ILIAS\Refinery\Factory;
use ILIAS\Refinery\KeyValueAccess;

/**
 * Class SuperGlobalDropInReplacement
 * This Class wraps SuperGlobals such as $_GET and $_POST to prevent modifying them in a future version.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SuperGlobalDropInReplacement extends KeyValueAccess
{

    /**
     * DirectValueAccessDropInReplacement constructor.
     * @param Factory $factory
     * @param array   $raw_values
     */
    public function __construct(Factory $factory, array $raw_values)
    {
        parent::__construct($raw_values, $factory->kindlyTo()->string());
    }

    /**
     * @deprecated Please note that this will throw an exception in a future version
     * @inheritDoc
     */
    public function offsetSet($offset, $value) : void
    {
//        throw new \OutOfBoundsException("Modifying global Request-Array such as \$_GET is not allowed!");
        parent::offsetSet($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset) : void
    {
        throw new \LogicException("Modifying global Request-Array such as \$_GET is not allowed!");
    }

}
