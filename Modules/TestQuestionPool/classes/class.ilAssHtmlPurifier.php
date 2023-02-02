<?php

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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
abstract class ilAssHtmlPurifier extends ilHtmlPurifierAbstractLibWrapper
{
    protected function getPurifierType(): string
    {
        return 'assessment';
    }

    /**
     * @return	HTMLPurifier_Config Instance of HTMLPurifier_Config
     */
    protected function getPurifierConfigInstance(): HTMLPurifier_Config
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.DefinitionID', $this->getPurifierType());
        $config->set('HTML.DefinitionRev', 1);
        $config->set('Cache.SerializerPath', ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory());
        $config->set('HTML.Doctype', 'XHTML 1.0 Strict');
        $config->set('HTML.AllowedElements', $this->getAllowedElements());
        $config->set('HTML.ForbiddenAttributes', 'div@style');
        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        }

        return $config;
    }

    private function getAllowedElements(): array
    {
        $allowedElements = $this->getElementsUsedForAdvancedEditing();

        $allowedElements = $this->makeElementListTinyMceCompliant($allowedElements);
        $allowedElements = $this->removeUnsupportedElements($allowedElements);

        return $allowedElements;
    }

    private function getElementsUsedForAdvancedEditing(): array
    {
        include_once 'Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';
        return ilObjAdvancedEditing::_getUsedHTMLTags($this->getPurifierType());
    }
}
