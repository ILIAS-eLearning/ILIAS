<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlStructureMapper
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureMapper
{
    /**
     * @var array<string, mixed>
     */
    private array $ctrl_structure;

    /**
     * ilCtrlStructureMapper Constructor
     *
     * @param array<string, mixed> $ctrl_structure
     */
    public function __construct(array $ctrl_structure)
    {
        $this->ctrl_structure = $ctrl_structure;
        $this->mapStructure();
    }

    /**
     * Returns the current structure with mapped vise-versa
     * references of each parent-child relation.
     *
     * @return array<string, mixed>
     */
    public function getStructure() : array
    {
        return $this->ctrl_structure;
    }

    /**
     * If a class has referenced another one as child or parent,
     * this method adds a vise-versa mapping if it doesn't already
     * exist.
     *
     * @param string $class_name
     * @param string $key_ref_from
     * @param string $key_ref_to
     */
    private function addViseVersaMappingByClass(string $class_name, string $key_ref_from, string $key_ref_to) : void
    {
        if (!empty($this->ctrl_structure[$class_name][$key_ref_from])) {
            foreach ($this->ctrl_structure[$class_name][$key_ref_from] as $index => $reference) {
                $is_reference_available = isset($this->ctrl_structure[$reference]);
                $is_reference_valid = $this->isStructureEntryValid($reference);

                // the vise-versa mapping must only be processed if the
                // reference is available and a valid structure entry.
                if ($is_reference_available && $is_reference_valid) {
                    // create reference list if not yet initialized.
                    if (!isset($this->ctrl_structure[$reference][$key_ref_to])) {
                        $this->ctrl_structure[$reference][$key_ref_to] = [];
                    }

                    // only add vise-versa mapping if it doesn't already exist.
                    if (!in_array($class_name, $this->ctrl_structure[$reference][$key_ref_to], true)) {
                        $this->ctrl_structure[$reference][$key_ref_to][] = $class_name;
                    }
                }

                // if the referenced class does not exist within the current
                // structure, the reference is removed from the reference list.
                if (!$is_reference_available || !$is_reference_valid) {
                    $this->removeReference($this->ctrl_structure[$class_name][$key_ref_from], $index);
                }
            }
        }
    }

    /**
     * Maps the current structures references.
     */
    private function mapStructure() : void
    {
        if (!empty($this->ctrl_structure)) {
            foreach ($this->ctrl_structure as $class_name => $data) {
                if ($this->isStructureEntryValid($class_name)) {
                    $this->addViseVersaMappingByClass(
                        $class_name,
                        ilCtrlStructureInterface::KEY_CLASS_CHILDREN,
                        ilCtrlStructureInterface::KEY_CLASS_PARENTS,
                    );

                    $this->addViseVersaMappingByClass(
                        $class_name,
                        ilCtrlStructureInterface::KEY_CLASS_PARENTS,
                        ilCtrlStructureInterface::KEY_CLASS_CHILDREN,
                    );
                } else {
                    // remove/unset invalid structure entries.
                    unset($this->ctrl_structure[$class_name]);
                }
            }
        }
    }

    /**
     * Removes an entry within the given reference list for the
     * given index and re-indexes the reference list afterwards.
     *
     * @param array      $reference_list
     * @param string|int $index
     */
    private function removeReference(array &$reference_list, $index) : void
    {
        // remove the reference of the current index.
        unset($reference_list[$index]);

        // re-index the reference list.
        $reference_list = array_values($reference_list);
    }

    /**
     * Helper function that returns whether an entry in the current
     * structure is valid or not.
     *
     * @param string|int $index
     * @return bool
     */
    private function isStructureEntryValid($index) : bool
    {
        // structure entry is not a classname.
        if (!is_string($index)) {
            return false;
        }

        // index is not contained in the structure.
        if (!isset($this->ctrl_structure[$index])) {
            return false;
        }

        // structure value is not an array
        if (!is_array($this->ctrl_structure[$index])) {
            return false;
        }

        return true;
    }
}
