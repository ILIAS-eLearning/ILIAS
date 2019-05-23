<?php

namespace SimpleBus\Message\Name\Exception;

use LogicException;

class CouldNotResolveMessageName extends LogicException
{
    public static function forMessage($message, $exceptionMessage)
    {
        return new self(
            sprintf(
                'Could not determine the name of a message of type "%s": %s',
                get_class($message),
                $exceptionMessage
            )
        );
    }
}
