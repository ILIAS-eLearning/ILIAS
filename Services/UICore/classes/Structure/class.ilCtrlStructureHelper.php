<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlStructureHelper
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureHelper
{
    /**
     * @var array<string, mixed>
     */
    protected array $ctrl_structure;

    /**
     * @var string[]
     */
    protected array $base_classes;

    /**
     * ilCtrlStructureHelper Constructor
     * @param array $base_classes
     * @param array $ctrl_structure
     */
    public function __construct(array $base_classes, array $ctrl_structure)
    {
        $this->ctrl_structure = $ctrl_structure;
        $this->base_classes = $base_classes;
    }

    /**
     * Fluent mapper method that adds vise-versa references for
     * each entry in the current ctrl structure - e.g. if a class
     * has several parent classes, this class is added as a child
     * class to each parent, and vise-versa.
     * This method doesn't necessarily need to be called, as it's
     * performance heavy, but for example when reading the ctrl
     * structure the mappings are wished to be complete.
     * @return self
     */
    public function mapStructureReferences() : self
    {
        $this->ctrl_structure = (new ilCtrlStructureMapper(
            $this->ctrl_structure
        ))->getStructure();

        return $this;
    }

    /**
     * Fluent filter method that removes structure entries, whose
     * parent- and child-references are empty. That means these
     * classes are not considered necessary GUI classes.
     * Such structure entries can be safely removed, because if they
     * have neither children nor parents, they will never be called
     * unless they are a baseclass itself.
     * @return self
     */
    public function filterUnnecessaryEntries() : self
    {
        $this->ctrl_structure = array_filter(
            $this->ctrl_structure,
            function (array $value, string $key) : bool {
                // if the entry is not a baseclass and has no
                // references, the entry will be removed.
                return !(
                    !in_array($key, $this->base_classes, true) &&
                    empty($value[ilCtrlStructureInterface::KEY_CLASS_CHILDREN]) &&
                    empty($value[ilCtrlStructureInterface::KEY_CLASS_PARENTS])
                );
            },
            ARRAY_FILTER_USE_BOTH
        );

        return $this;
    }

    /**
     * Returns the current ctrl structure.
     * @return array<string, mixed>
     */
    public function getStructure() : array
    {
        return $this->ctrl_structure;
    }
}
