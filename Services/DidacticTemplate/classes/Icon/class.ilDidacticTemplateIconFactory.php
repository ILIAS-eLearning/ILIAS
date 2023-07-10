<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Filesystem\Exception\IllegalStateException;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Location;

/**
 * Icon factory for didactic template custom icons
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateIconFactory
{
    private static ?ilDidacticTemplateIconFactory $instance = null;

    private ilObjectDefinition $definition;
    private ilDidacticTemplateSettings $settings;
    /** @var string[] */
    private array $icon_types = [];
    /** @var array<int, int[]> */
    private array $assignments = [];

    public function __construct()
    {
        global $DIC;

        $this->definition = $DIC['objDefinition'];
        $this->initTemplates();
    }

    public static function getInstance(): ilDidacticTemplateIconFactory
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getIconPathForReference(int $ref_id): string
    {
        $obj_id = ilObject::_lookupObjId($ref_id);
        $type = ilObject::_lookupType($obj_id);

        if (!$type || !$this->supportsCustomIcon($type)) {
            return '';
        }
        $assigned_template = $this->findAssignedTemplate($ref_id);
        if (!$assigned_template) {
            return '';
        }

        $path = $this->getIconPathForTemplate($assigned_template);
        return $path;
    }

    protected function getIconPathForTemplate(int $template_id): ?string
    {
        foreach ($this->settings->getTemplates() as $template) {
            if ($template->getId() === $template_id) {
                return $template->getIconHandler()->getAbsolutePath();
            }
        }

        return null;
    }

    protected function findAssignedTemplate(int $ref_id): int
    {
        foreach ($this->assignments as $tpl_id => $assignments) {
            if (in_array($ref_id, $assignments, true)) {
                return $tpl_id;
            }
        }

        return 0;
    }

    /**
     * Get icon path for object
     * Not applicable to non container objects, use getIconPathForReference instead
     * @param int $obj_id
     * @return string
     */
    public function getIconPathForObject(int $obj_id): string
    {
        // no support for referenced objects
        if (!$this->definition->isContainer(ilObject::_lookupType($obj_id))) {
            return '';
        }
        $refs = ilObject::_getAllReferences($obj_id);
        $ref_id = end($refs);

        return $this->getIconPathForReference((int) $ref_id);
    }

    protected function supportsCustomIcon(string $type): bool
    {
        return in_array($type, $this->icon_types, true);
    }

    private function initTemplates(): void
    {
        $this->settings = ilDidacticTemplateSettings::getInstance();
        $this->icon_types = [];

        $templates = [];
        foreach ($this->settings->getTemplates() as $tpl) {
            if ($tpl->getIconIdentifier() !== '') {
                $templates[] = $tpl->getId();
                foreach ($tpl->getAssignments() as $assignment) {
                    if (!in_array($assignment, $this->icon_types, true)) {
                        $this->icon_types[] = $assignment;
                    }
                }
            }
        }
        $this->assignments = ilDidacticTemplateObjSettings::getAssignmentsForTemplates($templates);
    }
}
