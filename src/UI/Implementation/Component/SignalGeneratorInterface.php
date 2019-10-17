<?php
namespace ILIAS\UI\Implementation\Component;

/**
 * Interface SignalGeneratorInterface
 *
 * @package ILIAS\UI\Component
 */
interface SignalGeneratorInterface
{

    /**
     * Create a signal, each created signal MUST have a unique ID.
     *
     * @param string $class Fully qualified class name (including namespace) of desired signal sub type
     * @return Signal
     */
    public function create($class = '');
}
