<?php

/**
 * Class ilCtrlPath is responsible for finding target classes
 * within the currently read control structure.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * To instantiate or generate a new path to a target class it
 * must be provided with the control structure and the ilCtrl's
 * current context information.
 *
 * Paths are dependent on the context information, for they have
 * to be called from a baseclass. If no baseclass is known the
 * path cannot be determined.
 */
final class ilCtrlPath implements ilCtrlPathInterface
{
    /**
     * Holds the currently read control structure.
     *
     * @var ilCtrlStructure
     */
    private ilCtrlStructure $structure;

    /**
     * Holds the current context information.
     *
     * @var ilCtrlContextInterface
     */
    private ilCtrlContextInterface $context;

    /**
     * Holds the whole cid path.
     *
     * @var string
     */
    private string $cid_path;

    /**
     * ilCtrlPath Constructor
     *
     * @param ilCtrlStructure        $structure
     * @param ilCtrlContextInterface $context
     * @param string|string[]        $target
     *
     * @throws ilCtrlException if no path can be found.
     */
    public function __construct(ilCtrlStructureInterface $structure, ilCtrlContextInterface $context, $target)
    {
        $this->structure = $structure;
        $this->context   = $context;
        $this->cid_path  = (is_array($target)) ?
            $this->getCidPathByArray($target) :
            $this->getCidPathByClass($target)
        ;
    }

    /**
     * @inheritDoc
     */
    public function getCidPath() : string
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
     * Returns a cid path that reaches from the current context's
     * baseclass to the given class.
     * If the given class cannot be reached from the context's
     * baseclass this instance must be given a class array instead.
     * @param string $target_class
     * @return string
     * @throws ilCtrlException if the class has no relations or cannot
     *                         reach the baseclass of this context.
     */
    private function getCidPathByClass(string $target_class) : string
    {
        $target_cid = $this->structure->getClassCidByName($target_class);

        // abort if the given class cannot be found.
        if (null === $target_cid) {
            throw new ilCtrlException("Class '$target_class' was not found in the control structure, try `composer du` to read artifacts.");
        }

        // the class cid can be returned, if the given class
        // is a baseclass itself.
        if ($this->structure->isBaseClass($target_class)) {
            return $target_cid;
        }

        // if the given class is already the current cid
        // of the current context, this path can be returned.
        if ($target_cid === $this->context->getPath()->getCurrentCid()) {
            return $this->context->getPath()->getCidPath();
        }

        // check if the target is related to a class within
        // the current context's path.
        $cid_paths = $this->context->getPath()->getCidPaths();
        foreach ($this->context->getPath()->getCidArray() as $index => $cid) {
            $current_class = $this->structure->getClassNameByCid($cid);
            if ($this->isClassChildOf($target_class, $current_class)) {
                return $this->appendCid($target_cid, $cid_paths[$index]);
            }
        }

        throw new ilCtrlException("ilCtrl cannot find a path for '$target_class' that reaches '{$this->context->getBaseClass()}'");
    }

    /**
     * Generates a cid path from the given class array.
     *
     * If the given class array does not contain a valid
     * path an according exception will be thrown.
     *
     * @param string[] $class_path
     * @return string
     *
     * @throws ilCtrlException if classes within the classes array
     *                         are not related.
     */
    private function getCidPathByArray(array $class_path) : string
    {
        // abort if the target class (array) is empty or
        // the baseclass of the class array is unknown.
        if (empty($class_path) || !$this->structure->isBaseClass($class_path[0])) {
            throw new ilCtrlException("First class provided in array must be a known baseclass.");
        }

        $cid_path = $previous_class = null;
        foreach ($class_path as $current_class) {
            $current_cid = $this->structure->getClassCidByName($current_class);

            // abort if the current class cannot be found.
            if (null === $current_cid) {
                throw new ilCtrlException("Class '$current_class' was not found in the control structure, try `composer du` to read artifacts.");
            }

            // abort if the current and previous classes are
            // not related.
            if (null !== $previous_class && !$this->isClassParentOf($previous_class, $current_class)) {
                throw new ilCtrlException("Class '$current_class' is not a child of '$previous_class'.");
            }

            $cid_path = $this->appendCid($current_cid, $cid_path);
            $previous_class = $current_class;
        }

        return $cid_path;
    }

    /**
     * Returns whether the given target class is a child of the
     * other given class.
     *
     * @param string $child_class
     * @param string $parent_class
     * @return bool
     */
    private function isClassChildOf(string $child_class, string $parent_class) : bool
    {
        return in_array($child_class, $this->structure->getChildrenByName($parent_class), true);
    }

    /**
     * Returns whether the given target class is a parent of the
     * other given class.
     *
     * @param string $parent_class
     * @param string $child_class
     * @return bool
     */
    private function isClassParentOf(string $parent_class, string $child_class) : bool
    {
        return in_array($parent_class, $this->structure->getParentsByName($child_class), true);
    }

    /**
     * Helper function to add CIDs to a given path.
     *
     * @param string      $cid
     * @param string|null $path
     * @return string
     */
    private function appendCid(string $cid, string $path = null) : string
    {
        if (null === $path) {
            return $cid;
        }

        return $path . self::CID_PATH_SEPARATOR . $cid;
    }
}