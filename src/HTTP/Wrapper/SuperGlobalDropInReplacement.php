<?php declare(strict_types=1);

namespace ILIAS\HTTP\Wrapper;

use ILIAS\Refinery\Factory;
use ILIAS\Refinery\KeyValueAccess;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class SuperGlobalDropInReplacement
 * This Class wraps SuperGlobals such as $_GET and $_POST to prevent modifying them in a future version.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SuperGlobalDropInReplacement extends KeyValueAccess
{

    /**
     * DirectValueAccessDropInReplacement constructor.
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
        /** @noRector */
        // this is currently possible, throw new \OutOfBoundsException("Modifying global Request-Array such as \$_GET is not allowed!"); if you want to prevent from overriding a value here
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
