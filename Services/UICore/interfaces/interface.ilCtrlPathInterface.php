<?php

/**
 * Interface ilCtrlPathInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlPathInterface
{
    /**
     * @var string separator used for CID paths.
     */
    public const CID_PATH_SEPARATOR = ':';

    /**
     * Returns the CID path for the target class of the
     * current instance.
     *
     * Null is returned when there's no valid path.
     *
     * @return string
     */
    public function getCidPath() : string;

    /**
     * Returns the CID that must currently be processed.
     *
     * @return string|null
     */
    public function getCurrentCid() : ?string;

    /**
     * Returns the next CID that must be processed.
     *
     * @return string|null
     */
    public function getNextCid() : ?string;
}