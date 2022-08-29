<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

class ilMailTemplateContextDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected ?string $component;
    protected bool $in_mailtemplates = false;

    /**
     * @var string[]
     */
    protected array $mail_templates = [];

    public function __construct(protected ilDBInterface $db)
    {
    }

    public function purge(): void
    {
    }

    public function beginComponent(string $component, string $type): void
    {
        $this->component = $type . '/' . $component;
        $this->in_mailtemplates = false;
        $this->mail_templates = [];
    }

    public function endComponent(string $component, string $type): void
    {
        $this->component = null;
        $this->in_mailtemplates = false;
        $this->mail_templates = [];
    }

    public function beginTag(string $name, array $attributes): void
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

    public function endTag(string $name): void
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
