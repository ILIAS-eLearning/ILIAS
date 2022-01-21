<?php declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilGeneralComponentDefinitionProcessor
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGeneralComponentDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected \ilDBInterface $db;
    protected string $table;
    protected string $component;
    protected string $table_column;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function purge() : void
    {
        $this->db->manipulate("DELETE FROM service_class");
        $this->db->manipulate("DELETE FROM module_class");
    }

    /**
     * @inheritDoc
     */
    public function beginComponent(string $component, string $type) : void
    {
        $this->table = $type === 'Service' ? 'service_class' : 'module_class';
        $this->table_column = $type === 'Service' ? 'service' : 'module';
        $this->component = $component;
    }

    /**
     * @inheritDoc
     */
    public function endComponent(string $component, string $type) : void
    {
        // TODO: Implement endComponent() method.
    }

    /**
     * @inheritDoc
     */
    public function beginTag(string $name, array $attributes) : void
    {
        if ($name === 'baseclass') {
            $component_class = $attributes['name'];
            if (class_exists($component_class)) {
                $this->db->insert($this->table, [
                    'class' => ['text', $component_class],
                    $this->table_column => ['text', $this->component],
                    'dir' => ['text', $attributes['dir']],
                ]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function endTag(string $name) : void
    {
    }

}
