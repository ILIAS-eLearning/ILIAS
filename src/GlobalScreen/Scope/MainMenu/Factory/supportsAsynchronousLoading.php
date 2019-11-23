<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

/**
 * Interface supportsAsynchronousLoading
 *
 * Types, which implement this interface, can load their content asynchronously
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface supportsAsynchronousLoading extends isItem
{

    /**
     * @param bool $supported
     *
     * @return supportsAsynchronousLoading
     */
    public function withSupportsAsynchronousLoading(bool $supported) : supportsAsynchronousLoading;


    /**
     * @return bool
     */
    public function supportsAsynchronousLoading() : bool;
}
