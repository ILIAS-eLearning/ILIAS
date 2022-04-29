<?php declare(strict_types=0);

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

    protected function getECSObjectType() : string
    {
        return '/campusconnect/courselinks';
    }

    protected function buildJson(ilECSSetting $a_server)
    {
        $json = $this->getJsonCore('application/ecs-course');

        // meta language
        $lang = ilMDLanguage::_lookupFirstLanguage(
            $this->content_obj->getId(),
            $this->content_obj->getId(),
            $this->content_obj->getType()
        );
        if (strlen($lang) !== 0) {
            $json->lang = $lang . '_' . strtoupper($lang);
        }
        $json->status = $this->content_obj->isActivated() ? 'online' : 'offline';

        $definition = ilECSUtils::getEContentDefinition($this->getECSObjectType());
        $this->addMetadataToJson($json, $a_server, $definition);
        $json->courseID = 'il_' . IL_INST_ID . '_' . $this->getContentObject()->getType() . '_' . $this->getContentObject()->getId();
        return $json;
    }
}
