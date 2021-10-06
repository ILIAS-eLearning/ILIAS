<?php

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * Class GenericNameSource
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class GenericNameSource implements NameSource
{
    /**
     * @var int
     */
    private static int $count = 0;

    /**
     * @inheritDoc
     */
    public function getNewName() : string
    {
        self::$count++;

        return 'form_input_' . self::$count;
    }
}