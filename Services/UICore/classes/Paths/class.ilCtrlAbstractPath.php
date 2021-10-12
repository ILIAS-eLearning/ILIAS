<?php

/**
 * Class ilCtrlAbstractPath
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
abstract class ilCtrlAbstractPath implements ilCtrlPathInterface
{
    /**
     * @var ilCtrlStructureInterface
     */
    protected ilCtrlStructureInterface $structure;

    /**
     * @var ilCtrlException|null
     */
    protected ?ilCtrlException $exception = null;

    /**
     * @var string|null
     */
    protected ?string $cid_path;

    /**
     * ilCtrlAbstractPath Constructor
     *
     * @param ilCtrlStructureInterface $structure
     */
    public function __construct(ilCtrlStructureInterface $structure)
    {
        $this->structure = $structure;
    }

    /**
     * @inheritDoc
     */
    public function getCidPath() : ?string
    {
        return $this->cid_path;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentCid() : ?string
    {
        $cid_array = $this->getCidArray();

        return $cid_array[count($cid_array) - 1];
    }

    /**
     * @inheritDoc
     */
    public function getNextCid(string $current_class) : ?string
    {
        $current_cid = $this->structure->getClassCidByName($current_class);
        $cid_array   = $this->getCidArray();
        $cid_count   = count($cid_array);

        foreach ($cid_array as $index => $cid) {
            if ($current_cid === $cid && ($index + 1) < $cid_count) {
                return $cid_array[$index + 1];
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getCidPaths(int $order = SORT_DESC) : array
    {
        // cid array must be ascending, because the
        // paths should begin at the baseclass.
        $cid_array = $this->getCidArray(SORT_ASC);
        $cid_paths = [];

        foreach ($cid_array as $index => $cid) {
            $cid_paths[] = (0 !== $index) ?
                $this->appendCid($cid, $cid_paths[$index - 1]) :
                $cid
            ;
        }

        if (SORT_DESC === $order) {
            $cid_paths = array_reverse($cid_paths);
        }

        return $cid_paths;
    }

    /**
     * @inheritDoc
     */
    public function getCidArray(int $order = SORT_DESC) : array
    {
        $cid_array = explode(self::CID_PATH_SEPARATOR, $this->cid_path);
        if (SORT_DESC === $order) {
            $cid_array = array_reverse($cid_array);
        }

        return $cid_array;
    }

    /**
     * @inheritDoc
     */
    public function getException() : ?ilCtrlException
    {
        return $this->exception;
    }

    /**
     * Returns whether the given target class is a child of the
     * other given class.
     *
     * @param string $child_class
     * @param string $parent_class
     * @return bool
     */
    protected function isClassChildOf(string $child_class, string $parent_class) : bool
    {
        return in_array(strtolower($child_class), $this->structure->getChildrenByName($parent_class), true);
    }

    /**
     * Returns whether the given target class is a parent of the
     * other given class.
     *
     * @param string $parent_class
     * @param string $child_class
     * @return bool
     */
    protected function isClassParentOf(string $parent_class, string $child_class) : bool
    {
        return in_array(strtolower($parent_class), $this->structure->getParentsByName($child_class), true);
    }

    /**
     * Helper function to add CIDs to a given path.
     *
     * @param string      $cid
     * @param string|null $path
     * @return string
     */
    protected function appendCid(string $cid, string $path = null) : string
    {
        if (null === $path) {
            return $cid;
        }

        return $path . self::CID_PATH_SEPARATOR . $cid;
    }
}