<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

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
    protected ?string $cid_path = null;

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
    public function getCidPath(): ?string
    {
        // cannot use empty(), since '0' would be considered
        // empty and that's an actual cid.
        if (null !== $this->cid_path && '' !== $this->cid_path) {
            return $this->cid_path;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentCid(): ?string
    {
        if (null !== $this->getCidPath()) {
            // use default order (command- to baseclass) and
            // retrieve the last command class (index 0).
            return $this->getCidArray()[0];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getNextCid(string $current_class): ?string
    {
        $current_cid = $this->structure->getClassCidByName($current_class);
        $cid_array = $this->getCidArray(SORT_ASC);
        $cid_count = count($cid_array);

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
    public function getCidPaths(int $order = SORT_DESC): array
    {
        if (null === $this->getCidPath()) {
            return [];
        }

        // cid array must be ascending, because the
        // paths should always begin at the baseclass.
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
    public function getCidArray(int $order = SORT_DESC): array
    {
        if (null === $this->getCidPath()) {
            return [];
        }

        $cid_array = explode(self::CID_PATH_SEPARATOR, $this->cid_path);
        if (SORT_DESC === $order) {
            $cid_array = array_reverse($cid_array);
        }

        return $cid_array;
    }

    /**
     * @inheritDoc
     */
    public function getBaseClass(): ?string
    {
        if (null !== $this->cid_path) {
            $cid_array = $this->getCidArray(SORT_ASC);
            $class_name = $this->structure->getClassNameByCid($cid_array[0]);
            if (null !== $class_name && $this->structure->isBaseClass($class_name)) {
                return $class_name;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getException(): ?ilCtrlException
    {
        return $this->exception;
    }

    /**
     * Returns the path to a class within the given contexts current path
     * that has a relation to the given target.
     *
     * @param ilCtrlContextInterface $context
     * @param string                 $target_class
     * @return string|null
     */
    protected function getPathToRelatedClassInContext(ilCtrlContextInterface $context, string $target_class): ?string
    {
        if (null !== $context->getPath()->getCidPath()) {
            foreach ($context->getPath()->getCidArray() as $index => $cid) {
                $current_class = $this->structure->getClassNameByCid($cid);
                if (null !== $current_class && $this->isClassChildOf($target_class, $current_class)) {
                    $cid_paths = $context->getPath()->getCidPaths();

                    // return the path to the class related to the
                    // target class.
                    return $cid_paths[$index];
                }
            }
        }

        return null;
    }

    /**
     * Returns whether the given target class is a child of the
     * other given class.
     *
     * @param string $child_class
     * @param string $parent_class
     * @return bool
     */
    protected function isClassChildOf(string $child_class, string $parent_class): bool
    {
        $children = $this->structure->getChildrenByName($parent_class);
        if (null !== $children) {
            return in_array(strtolower($child_class), $children, true);
        }

        return false;
    }

    /**
     * Returns whether the given target class is a parent of the
     * other given class.
     *
     * @param string $parent_class
     * @param string $child_class
     * @return bool
     */
    protected function isClassParentOf(string $parent_class, string $child_class): bool
    {
        $parents = $this->structure->getParentsByName($child_class);
        if (null !== $parents) {
            return in_array(strtolower($parent_class), $parents, true);
        }

        return false;
    }

    /**
     * Helper function to add CIDs to a given path.
     *
     * @param string      $cid
     * @param string|null $path
     * @return string
     */
    protected function appendCid(string $cid, string $path = null): string
    {
        if (null === $path) {
            return $cid;
        }

        return $path . self::CID_PATH_SEPARATOR . $cid;
    }
}
