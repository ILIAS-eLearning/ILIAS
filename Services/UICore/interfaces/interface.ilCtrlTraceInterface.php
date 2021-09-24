<?php

/**
 * ilCtrlTraceInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlTraceInterface
{
    /**
     * Returns the current CID trace as a string.
     *
     * @return string
     */
    public function getCidTrace() : string;

    /**
     * Adds another command class to the current trace.
     *
     * @param string $class_name
     */
    public function appendByClass(string $class_name) : void;

    /**
     * Adds another trace generated from a path of classes.
     * E.g. array('BaseClass', 'FurtherClass', 'CmdClass');
     *
     * @param array $classes
     */
    public function replaceByClassPath(array $classes) : void;

    /**
     * Yields all CIDs of the current trace in the given direction.
     *
     * @param int $order (SORT_ASC|SORT_DESC)
     * @return Generator
     */
    public function getCidPieces(int $order = SORT_ASC) : Generator;

    /**
     * Returns all CIDs of the current trace in the given direction.
     *
     * @return array<int, string>
     */
    public function getAllCidPieces(int $order = SORT_ASC) : array;

    /**
     * Returns whether the current trace is valid or not.
     *
     * @return bool
     */
    public function isValid() : bool;
}