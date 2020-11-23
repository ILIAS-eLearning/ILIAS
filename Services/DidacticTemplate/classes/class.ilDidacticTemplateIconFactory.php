<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Filesystem\Exception\IllegalStateException;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Location;

/**
 * Icon factory for didactic template custom icons
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */

class ilDidacticTemplateIconFactory
{
    private static $instance = null;

    /**
     * @var ilObjectDefinition
     */
    private $definition;

    /**
     * @var ilDidacticTemplateSettings
     */
    private $settings;

    /**
     * @var string[]
     */
    private $icon_types = [];

    /**
     * @var array
     */
    private $assignments = [];

    private $logger;

    /**
     * ilDidacticTemplateIconFactory constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->otpl();
        $this->definition = $DIC['objDefinition'];
        $this->initTemplates();
    }

    /**
     * @return ilDidacticTemplateIconFactory
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param int $ref_id
     */
    public function getIconPathForReference(int $ref_id) : string
    {
        $obj_id = ilObject::_lookupObjId($ref_id);
        $type = ilObject::_lookupType($obj_id);

        if (strlen($type) || !$this->supportsCustomIcon($type)) {
            return '';
        }
        $assigned_template = $this->findAssignedTemplate($ref_id);
        if (!$assigned_template) {
            return '';
        }
        $path = $this->getIconPathForTemplate($assigned_template);
        return $path;
    }

    /**
     * @param int $template_id
     * @return string
     */
    protected function getIconPathForTemplate(int $template_id) : string
    {
        foreach ($this->settings->getTemplates() as $template) {
            if ($template->getId() == $template_id) {
                return $template->getIconHandler()->getAbsolutePath();
            }
        }
    }

    /**
     * @param int $ref_id
     * @return int
     */
    protected function findAssignedTemplate(int $ref_id) : int
    {
        foreach ($this->assignments as $tpl_id => $assignments) {
            if (in_array($ref_id, $assignments)) {
                return (int) $tpl_id;
            }
        }
        return 0;
    }

    /**
     * Get icon path for object
     * not applicable to non container objects, use getIconPathForReference instead
     * @param int $obj_id
     * @return string
     */
    public function getIconPathForObject(int $obj_id) : string
    {
        // no support for referenced objects
        if (!$this->definition->isContainer(ilObject::_lookupType($obj_id))) {
            return '';
        }
        $refs = ilObject::_getAllReferences($obj_id);
        $ref_id = end($refs);
        return $this->getIconPathForReference((int) $ref_id);
    }

    /**
     * @param string $type
     * @return bool
     */
    protected function supportsCustomIcon(string $type) : bool
    {
        return in_array($type, $this->icon_types);
    }
    
    private function initTemplates()
    {
        $this->settings = ilDidacticTemplateSettings::getInstance();
        $this->icon_types = [];

        $templates = [];
        foreach ($this->settings->getTemplates() as $tpl) {
            if ($tpl->getIconIdentifier() != '') {
                $templates[] = $tpl->getId();
                foreach ($tpl->getAssignments() as $assignment) {
                    if (!in_array($assignment, $this->icon_types)) {
                        $this->icon_types[] = $assignment;
                    }
                }
            }
        }
        $this->assignments = ilDidacticTemplateObjSettings::getAssignmentsForTemplates($templates);
    }

}
