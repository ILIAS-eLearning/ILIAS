<?php

/**
 * Interface ilCtrlIteratorInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This interface describes how an ilCtrl iterator must behave
 * like. It extends the original Iterator interface but overrides
 * the public functions current() and key(), as the Iterators
 * must always return class-paths mapped to the object name.
 *
 * This means, that Iterators implementing this interface have
 * rather complex valid() methods, as they need to check if
 * the current data and key provided by the source are strings.
 */
interface ilCtrlIteratorInterface extends Iterator
{
    /**
     * @inheritDoc
     *
     * @return string
     */
    public function current() : ?string;

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function key() : ?string;
}
