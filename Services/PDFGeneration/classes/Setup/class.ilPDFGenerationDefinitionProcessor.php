<?php declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilPDFGenerationDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected \ilDBInterface $db;
    protected ?string $component;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function purge() : void
    {
    }

    public function beginComponent(string $component, string $type) : void
    {
        $this->component = $type . "/" . $component;
    }

    public function endComponent(string $component, string $type) : void
    {
        $this->component = null;
    }

    public function beginTag(string $name, array $attributes) : void
    {
        if ($name !== "pdfpurpose") {
            return;
        }

        ilPDFCompInstaller::updateFromXML($this->component, $attributes['name'], $attributes['preferred']);
    }

    public function endTag(string $name) : void
    {
    }
}
