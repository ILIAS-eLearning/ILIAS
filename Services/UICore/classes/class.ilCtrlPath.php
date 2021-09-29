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
     * Holds the current cid.
     *
     * @var string|null
     */
    private ?string $current_cid = null;

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

        $this->current_cid = $this->getCurrentCid();
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
        // if the current cid has not been set, the baseclass
        // cid is set.
        if (null === $this->current_cid) {
            $cid_array = $this->getCidArray($this->cid_path);
            $this->current_cid = $cid_array[0];
        }

        return $this->current_cid;
    }

    /**
     * @inheritDoc
     */
    public function getNextCid() : ?string
    {
        $cid_array = $this->getCidArray($this->cid_path);
        $cid_count = count($cid_array);

        foreach ($cid_array as $index => $cid) {
            if ($this->current_cid === $cid) {
                if (($index + 1) < $cid_count) {
                    // update current cid and return the next one.
                    $next_cid = $cid_array[$index + 1];
                    $this->current_cid = $next_cid;
                    return $next_cid;
                }

                return null;
            }
        }

        return null;
    }

    /**
     * Returns a cid path that reaches from the current context's
     * baseclass to the given class.
     *
     * If the given class cannot be reached from the context's
     * baseclass this instance must be given a class array instead.
     *
     * @param string $class_name
     * @return string
     *
     * @throws ilCtrlException if the class has no relations or cannot
     *                         reach the baseclass of this context.
     */
    private function getCidPathByClass(string $class_name) : string
    {
        $parents = $this->getParentsRecursively($class_name);
        if (empty($parents)) {
            if (!$this->structure->isBaseClass($class_name)) {
                throw new ilCtrlException("Class '$class_name' is no baseclass and not related to another one.");
            }

            // null will not be returned because isBaseClass()
            // already returned true.
            return $this->structure->getClassCidByName($class_name);
        }

        foreach ($parents as $path => $direct_parents) {
            $cid_array = $this->getCidArray($path);
            if ($this->context->getBaseClass() === $cid_array[count($cid_array) - 1]) {
                // the match must be reverted, as the path is
                // found backwards (from target to baseclass).
                return strrev($path);
            }
        }

        throw new ilCtrlException("ilCtrl cannot find a path for '$class_name' that reaches '{$this->context->getBaseClass()}'");
    }

    /**
     * Generates a cid path from the given class array.
     *
     * If the given class array does not contain a valid
     * path an according exception will be thrown.
     *
     * @param string[] $class_path
     * @return string|null
     *
     * @throws ilCtrlException if classes within the classes array
     *                         are not related.
     */
    private function getCidPathByArray(array $class_path) : ?string
    {
        // abort if the target class (array) is empty or
        // the baseclass of the class array is unknown.
        if (empty($class_path) || !$this->structure->isBaseClass($class_path[0])) {
            throw new ilCtrlException("First class provided in array must be a known baseclass.");
        }

        $path = $previous_class_name = null;
        foreach ($class_path as $class_name) {
            $class_cid = $this->structure->getClassCidByName($class_name);

            // abort if one of the class cid's cannot be found.
            if (null === $class_cid) {
                throw new ilCtrlException("ilCtrl can't find cid for target class '$class_name'.");
            }

            // abort if the previous class is not a parent of
            // the current one.
            if (null !== $previous_class_name && !$this->isClassChildOf($class_name, $previous_class_name)) {
                throw new ilCtrlException("Classes '$class_name' and '$previous_class_name' are not related.");
            }

            $path = (null !== $path) ?
                $this->appendCid($path, $class_cid) :
                $class_cid
            ;

            $previous_class_name = $class_name;
        }

        return $path;
    }

    /**
     * Returns all direct and derived parent classes for a given
     * class. The parents are mapped to their cid path in order
     * to know how it can be reached.
     *
     * Return value might look like this for example:
     *
     *      array(
     *          'cid1'           => 'foo',
     *          'cid1:cid2'      => 'fooBar',
     *          'cid1:cid2:cid3' => null,
     *          ...
     *      );
     *
     * If no parents are found null will be mapped to the path.
     *
     * @param string      $target_class
     * @param string|null $current_path
     * @return array<string, string[]>
     */
    private function getParentsRecursively(string $target_class, string $current_path = null) : array
    {
        $target_class_parents = $this->structure->getParentsByName($target_class);
        $target_class_cid     = $this->structure->getClassCidByName($target_class);

        // abort if the target cid cannot be found or
        // if the target class is an orphan.
        if (null === $target_class_cid || null === $target_class_parents) {
            return [];
        }

        // initialize the current path if not provided,
        // else append the target class cid.
        $current_path = (null !== $current_path) ?
            $this->appendCid($current_path, $target_class_cid) :
            $target_class_cid
        ;

        // map the target classes parents to the current path
        $parents[$current_path] = $target_class_parents;

        // fetch derived parents for all parent objects of the
        // current target class.
        foreach ($parents[$current_path] as $parent_class) {
            // only process parent class if it exists (in the
            // control structure).
            $parent_class_cid = $this->structure->getClassCidByName($parent_class);
            if (null !== $parent_class_cid) {
                $parent_class_parents = $this->getParentsRecursively($parent_class, $current_path);
                if (!empty($parent_class_parents)) {
                    // if the parent has further parents, map them to their path.
                    foreach ($parent_class_parents as $parent_path => $parent) {
                        $parents[$parent_path] = $parent;
                    }
                } else {
                    // if the parent class is an orphan, set null mapped
                    // to the parent's path.
                    $parents[$this->appendCid($current_path, $parent_class_cid)] = null;
                }
            }
        }

        return $parents;
    }

    /**
     * Returns whether a given cid path is valid or not.
     *
     * @param string $path
     * @return bool
     */
    private function isPathValid(string $path) : bool
    {
        $cid_array = $this->getCidArray($path);
        $cid_count = count($cid_array);

        if (0 === $cid_count) {
            return false;
        }

        $base_class = $this->structure->getClassNameByCid($cid_array[0]);

        // check if the first class is a known baseclass.
        if (null === $base_class || !$this->structure->isBaseClass($base_class)) {
            return false;
        }

        // check if each class is related to it's child.
        foreach ($cid_array as $index => $cid) {
            if (($index + 1) < $cid_count) {
                $current_class = $this->structure->getClassNameByCid($cid);
                $next_class    = $this->structure->getClassNameByCid($cid_array[$index + 1]);

                // the trace is invalid if there are classes chained
                // together that are not related to each other.
                if (!$this->isClassParentOf($current_class, $next_class)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns whether the given target class is a child of the
     * other given class.
     *
     * @param string $target_class
     * @param string $other_class
     * @return bool
     */
    private function isClassChildOf(string $target_class, string $other_class) : bool
    {
        return in_array($target_class, $this->structure->getChildrenByName($other_class), true);
    }

    /**
     * Returns whether the given target class is a parent of the
     * other given class.
     *
     * @param string $target_class
     * @param string $other_class
     * @return bool
     */
    private function isClassParentOf(string $target_class, string $other_class) : bool
    {
        return in_array($target_class, $this->structure->getParentsByName($other_class), true);
    }

    /**
     * Helper function to add CIDs to a given path.
     *
     * @param string $path
     * @param string $cid
     * @return string
     */
    private function appendCid(string $path, string $cid) : string
    {
        return $path . self::CID_PATH_SEPARATOR . $cid;
    }

    /**
     * Helper function that returns all cid's from a given path.
     *
     * @param string $path
     * @return array
     */
    private function getCidArray(string $path) : array
    {
        return explode(self::CID_PATH_SEPARATOR, $path);
    }
}