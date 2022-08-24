<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Interface ilCtrlPathInterface is responsible for holding and
 * manipulating a valid ilCtrl class-path (abbreviated by CID).
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * A CID-path or class-path is a
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
     * @return string|null
     */
    public function getCidPath(): ?string;

    /**
     * Returns the CID that must currently be processed.
     *
     * @return string|null
     */
    public function getCurrentCid(): ?string;

    /**
     * Returns the next CID that must be processed.
     *
     * @param string $current_class
     * @return string|null
     */
    public function getNextCid(string $current_class): ?string;

    /**
     * Returns all individual paths for each cid position for the
     * given direction.
     *
     * For example, trace 'cid1:cid2:cid3' it would return:
     *      array(
     *          'cid1',
     *          'cid1:cid2',
     *          'cid1:cid2:cid3',
     *          ...
     *      );
     *
     * ASC  => from baseclass to command class.
     * DESC => from command class to baseclass.
     *
     * @param int $order (SORT_DESC|SORT_ASC)
     * @return string[]
     */
    public function getCidPaths(int $order = SORT_DESC): array;

    /**
     * Returns all cid's from the current path in the provided
     * directory/order.
     *
     * ASC  => from baseclass to command class.
     * DESC => from command class to baseclass.
     *
     * @param int $order (SORT_DESC|SORT_ASC)
     * @return string[]
     */
    public function getCidArray(int $order = SORT_DESC): array;

    /**
     * Returns the baseclass of the current cid path.
     *
     * @return string|null
     */
    public function getBaseClass(): ?string;

    /**
     * Returns the exception produced during the path-finding-
     * process.
     *
     * @return ilCtrlException|null
     */
    public function getException(): ?ilCtrlException;
}
