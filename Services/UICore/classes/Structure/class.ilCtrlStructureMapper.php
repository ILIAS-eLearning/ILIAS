<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlStructureMapper
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlStructureMapper
{
    /**
     * @var array<string, mixed>
     */
    private array $ctrl_structure;

    /**
     * ilCtrlStructureMapper Constructor
     *
     * @param array<string, mixed> $structure
     */
    public function __construct(array $structure)
    {
        $this->ctrl_structure = $this->mapStructure($structure);
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
     * @param array  $structure
     * @param string $class_name
     * @param string $key_ref_from
     * @param string $key_ref_to
     */
    private function addViseVersaMappingByClass(
        array &$structure,
        string $class_name,
        string $key_ref_from,
        string $key_ref_to
    ) : void {
        if (!empty($structure[$class_name][$key_ref_from])) {
            foreach ($structure[$class_name][$key_ref_from] as $reference) {
                // only add vise-versa mapping if it doesn't already exist.
                if (isset($structure[$reference]) && !in_array($class_name, $structure[$reference][$key_ref_to], true)) {
                    $structure[$reference][$key_ref_to][] = $class_name;
                }
            }
        }
    }

    /**
     * Returns the mapped structure.
     *
     * @param array<string, mixed> $structure
     * @return array
     */
    private function mapStructure(array $structure) : array
    {
        if (empty($structure)) {
            return [];
        }

        foreach ($structure as $class_name => $data) {
            $this->addViseVersaMappingByClass(
                $structure,
                $class_name,
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN,
                ilCtrlStructureInterface::KEY_CLASS_PARENTS,
            );

            $this->addViseVersaMappingByClass(
                $structure,
                $class_name,
                ilCtrlStructureInterface::KEY_CLASS_PARENTS,
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN,
            );
        }

        return $structure;
    }
}