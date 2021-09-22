<?php

/**
 * ilCtrlTraceInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlTraceInterface
{
    /**
     * @param string $class_name
     */
    public function appendClass(string $class_name) : void;

    /**
     * Yields all CIDs of the current trace in the given direction.
     *
     * @param int $order (SORT_ASC|SORT_DESC)
     * @return Generator
     */
    public function getCidPieces(int $order = SORT_ASC) : Generator;

    /**
     * @return string
     */
    public function getCurrentCid() : string;

    /**
     * @return bool
     */
    public function isValid() : bool;
}