<?php declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilComponentInfoDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected array $data = [];
    protected ?string $component;
    protected ?string $type;

    public function getData() : array
    {
        return $this->data;
    }

    public function purge() : void
    {
        $this->data = [
            \ilArtifactComponentDataDB::BY_ID => [],
            \ilArtifactComponentDataDB::BY_TYPE_AND_NAME => []
        ];
    }

    public function beginComponent(string $component, string $type) : void
    {
        $this->component = $component;
        $this->type = $type;
    }

    public function endComponent(string $component, string $type) : void
    {
        $this->component = null;
        $this->type = null;
    }

    public function beginTag(string $name, array $attributes) : void
    {
        if ($name === "module") {
            $type = "Modules";
        } elseif ($name === "service") {
            $type = "Services";
        } else {
            return;
        }

        if (!isset($attributes["id"])) {
            throw new \InvalidArgumentException(
                "Expected attribute 'id' for tag '$name' in {$this->component}"
            );
        }

        if ($type !== $this->type) {
            throw new \InvalidArgumentException(
                "Type {$this->type} and tag don't match for component {$this->component}"
            );
        }

        if (!isset($this->data[\ilArtifactComponentDataDB::BY_TYPE_AND_NAME][$this->type])) {
            $this->data[\ilArtifactComponentDataDB::BY_TYPE_AND_NAME][$this->type] = [];
        }

        $this->data[\ilArtifactComponentDataDB::BY_TYPE_AND_NAME][$this->type][$this->component] = $attributes["id"];
        $this->data[\ilArtifactComponentDataDB::BY_ID][$attributes["id"]] = [$this->type, $this->component];
    }

    public function endTag(string $name) : void
    {
    }
}
