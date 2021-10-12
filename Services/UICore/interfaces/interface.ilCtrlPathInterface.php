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
     * @param string $current_class
     * @return string|null
     */
    public function getNextCid(string $current_class) : ?string;

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
     * @param int $order (SORT_DESC|SORT_ASC)
     * @return string[]
     */
    public function getCidPaths(int $order = SORT_DESC) : array;

    /**
     * Returns all cid's from the current path in the provided
     * directory/order.
     *
     * @param int $order (SORT_DESC|SORT_ASC)
     * @return string[]
     */
    public function getCidArray(int $order = SORT_DESC) : array;
}