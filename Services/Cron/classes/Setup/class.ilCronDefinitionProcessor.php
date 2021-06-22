<?php declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilCronDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected \ilDBInterface $db;
    protected ?string $component;
    /**
     * @var string[]
     */
    protected array $has_cron;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
        $this->has_cron = [];
    }

    public function purge() : void
    {
    }

    public function beginComponent(string $component, string $type) : void
    {
        $this->component = $type . "/" . $component;
        $this->has_cron = [];
    }

    public function endComponent(string $component, string $type) : void
    {
        $this->component = null;
        $this->has_cron = [];
    }

    public function beginTag(string $name, array $attributes) : void
    {
        if ($name !== "cron") {
            return;
        }

        $component = $attributes["component"] ?? null;
        if (!$component) {
            $component = $this->component;
        }
        ilCronManager::updateFromXML($component, $attributes["id"], $attributes["class"], ($attributes["path"] ?? null));
        $this->has_cron[] = $attributes["id"];
    }

    public function endTag(string $name) : void
    {
        if ($name !== "module" && $name !== "service") {
            return;
        }

        ilCronManager::clearFromXML(
            $this->component,
            $this->has_cron
        );
    }
}
