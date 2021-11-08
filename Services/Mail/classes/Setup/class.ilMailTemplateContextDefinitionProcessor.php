<?php declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilMailTemplateContextDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected ilDBInterface $db;
    protected ?string $component;
    protected bool $in_mailtemplates = false;

    /**
     * @var string[]
     */
    protected array $mail_templates = [];

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function purge() : void
    {
    }

    public function beginComponent(string $component, string $type) : void
    {
        $this->component = $type . '/' . $component;
        $this->in_mailtemplates = false;
        $this->mail_templates = [];
    }

    public function endComponent(string $component, string $type) : void
    {
        $this->component = null;
        $this->in_mailtemplates = false;
        $this->mail_templates = [];
    }

    public function beginTag(string $name, array $attributes) : void
    {
        if ($name === 'mailtemplates') {
            $this->in_mailtemplates = true;
            return;
        }
        if ($name !== 'context' || !$this->in_mailtemplates) {
            return;
        }

        $component = $attributes['component'] ?? null;
        if (!$component) {
            $component = $this->component;
        }

        ilMailTemplateContextService::insertFromXML(
            $component,
            $attributes['id'],
            $attributes['class'],
            $attributes['path'] ?? null
        );
        $this->mail_templates[] = $attributes['id'];
    }

    public function endTag(string $name) : void
    {
        if ($name === 'mailtemplates') {
            $this->in_mailtemplates = false;
            return;
        }

        if ($name !== 'module' && $name !== 'service') {
            return;
        }

        ilMailTemplateContextService::clearFromXml($this->component, $this->mail_templates);
    }
}
