<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlStructureHelper
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlStructureHelper
{
    /**
     * @var array<string, mixed>
     */
    private array $ctrl_structure;

    /**
     * @var array<string, mixed>
     */
    private array $plugin_structure;

    /**
     * ilCtrlStructureHelper Constructor
     *
     * @param array      $ctrl_structure
     * @param array|null $plugin_structure
     */
    public function __construct(array $ctrl_structure, array $plugin_structure = null)
    {
        $this->plugin_structure = $plugin_structure ?? [];
        $this->ctrl_structure   = $ctrl_structure;
    }

    /**
     * Fluent mapper method that adds vise-versa references for
     * each entry in the current ctrl structure - e.g. if a class
     * has several parent classes, this class is added as a child
     * class to each parent, and vise-versa.
     *
     * This method doesn't necessarily need to be called, as it's
     * performance heavy, but for example when reading the ctrl
     * structure the mappings are wished to be complete.
     *
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
     * Fluent merging method that adds all provided plugin structure
     * entries to the current ctrl structure.
     *
     * @return self
     */
    public function mergePluginStructure() : self
    {
        if (empty($this->plugin_structure)) {
            return $this;
        }

        foreach ($this->plugin_structure as $plugin_data) {
            if (!empty($plugin_data)) {
                foreach ($this->plugin_structure as $class_name => $data) {
                    $this->ctrl_structure[$class_name] = $data;
                }
            }
        }

        return $this;
    }

    /**
     * Returns the current plugin structure.
     *
     * @return array<string, mixed>
     */
    public function getPluginStructure() : array
    {
        return $this->plugin_structure;
    }

    /**
     * Returns the current ctrl structure.
     *
     * @return array<string, mixed>
     */
    public function getStructure() : array
    {
        return $this->ctrl_structure;
    }
}