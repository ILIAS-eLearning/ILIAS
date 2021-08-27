<?php declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilCtrlBaseclassDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected const SERVICE_CLASS = 'service_class';
    protected const MODULE_CLASS = 'module_class';
    protected const SERVICE = 'Services';
    protected const MODULE = 'Modules';
    protected \ilDBInterface $db;
    protected ?string $component;
    protected ?string $type;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function purge() : void
    {
        $this->db->manipulate("DELETE FROM " . self::SERVICE_CLASS);
        $this->db->manipulate("DELETE FROM " . self::MODULE_CLASS);

    }

    public function beginComponent(string $component, string $type) : void
    {
        $this->type = $type;
        $this->component = $component;
    }

    public function endComponent(string $component, string $type) : void
    {
    }

    public function beginTag(string $name, array $attributes) : void
    {
        if ($name !== "baseclass") {
            return;
        }
        $table = $this->type === self::SERVICE ? self::SERVICE_CLASS : self::MODULE_CLASS;
        $field = $this->type === self::SERVICE ? 'service' : 'module';

        $this->db->insert($table,
            [
                'class' => ['text', $attributes['name']],
                $field => ['text', $this->component],
                'dir' => ['text', 'classes'],
            ]);
    }

    public function endTag(string $name) : void
    {
    }
}
