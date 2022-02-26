<?php declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilECSCourseSettings
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesCourse
 */
class ilECSCourseSettings extends ilECSObjectSettings
{
    protected ilLogger $logger;

    public function __construct(ilObject $a_content_object)
    {
        global $DIC;

        $this->logger = $DIC->logger()->crs();
        parent::__construct($a_content_object);
    }

    protected function getECSObjectType()
    {
        return '/campusconnect/courselinks';
    }

    protected function buildJson(ilECSSetting $a_server)
    {
        $json = $this->getJsonCore('application/ecs-course');

        // meta language
        $lang = ilMDLanguage::_lookupFirstLanguage($this->content_obj->getId(), $this->content_obj->getId(),
            $this->content_obj->getType());
        if (strlen($lang)) {
            $json->lang = $lang . '_' . strtoupper($lang);
        }
        $json->status = $this->content_obj->isActivated() ? 'online' : 'offline';

        $definition = ilECSUtils::getEContentDefinition($this->getECSObjectType());
        $this->addMetadataToJson($json, $a_server, $definition);
        $json->courseID = 'il_' . IL_INST_ID . '_' . $this->getContentObject()->getType() . '_' . $this->getContentObject()->getId();
        return $json;
    }
}
