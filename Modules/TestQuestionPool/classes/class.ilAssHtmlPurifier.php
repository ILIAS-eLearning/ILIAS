<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Html/classes/class.ilHtmlPurifierAbstractLibWrapper.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
abstract class ilAssHtmlPurifier extends ilHtmlPurifierAbstractLibWrapper
{
    protected function getPurifierType()
    {
        return 'assessment';
    }

    /**
     * @return	HTMLPurifier_Config Instance of HTMLPurifier_Config
     */
    protected function getPurifierConfigInstance() : HTMLPurifier_Config
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.DefinitionID', $this->getPurifierType());
        $config->set('HTML.DefinitionRev', 1);
        $config->set('Cache.SerializerPath', ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory());
        $config->set('HTML.Doctype', 'XHTML 1.0 Strict');
        $config->set('HTML.AllowedElements', $this->getAllowedElements());
        $config->set('HTML.ForbiddenAttributes', 'div@style');
        $config->autoFinalize = false;
        $config->set(
            'URI.AllowedSchemes',
            array_merge(
                $config->get('URI.AllowedSchemes'),
                ['data' => true]
            )
        );
        $config->autoFinalize = true;
        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        }

        return $config;
    }

    private function getAllowedElements()
    {
        $allowedElements = $this->getElementsUsedForAdvancedEditing();

        $allowedElements = $this->makeElementListTinyMceCompliant($allowedElements);
        $allowedElements = $this->removeUnsupportedElements($allowedElements);

        return $allowedElements;
    }

    private function getElementsUsedForAdvancedEditing()
    {
        include_once 'Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';
        return ilObjAdvancedEditing::_getUsedHTMLTags($this->getPurifierType());
    }
}
