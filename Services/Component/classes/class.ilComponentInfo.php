<?php declare(strict_types=1);

/**
 * Simple value class for basic information about a component.
 */
class ilComponentInfo
{
    // TODO: to be replaced with an enum for PHP 8.1...
    public const TYPES = ["Modules", "Services"];
    public const TYPE_MODULES = "Modules";
    public const TYPE_SERVICES = "Services";

    protected string $id;
    protected string $type;
    protected string $name;

    public function __construct(
        string $id,
        string $type,
        string $name
    ) {
        if (!in_array($type, self::TYPES)) {
            throw new \InvalidArgumentException(
                "Invalid component type: $type"
            );
        }

        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getName() : string
    {
        return $this->name;
    }
}
