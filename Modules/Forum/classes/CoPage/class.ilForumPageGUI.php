<?php declare(strict_types=1);

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
 ********************************************************************
 */

/**
 * @ilCtrl_Calls ilForumPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilForumPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilForumPageGUI: ilPropertyFormGUI, ilInternalLinkGUI, ilPageMultiLangGUI
 */
class ilForumPageGUI extends ilPageObjectGUI implements ilForumObjectConstants
{
    protected bool $isEmbeddedMode = false;
    protected string $language = '-';

    public function __construct($a_id = 0, $a_old_nr = 0, $isEmbeddedMode = false, $language = '')
    {
        parent::__construct(self::OBJ_TYPE, $a_id, $a_old_nr, false, $language);
        $this->setTemplateTargetVar('ADM_CONTENT');
        $this->setTemplateOutput(false);
        $this->isEmbeddedMode = $isEmbeddedMode;
    }

    public function getProfileBackUrl() : string
    {
        if ($this->isEmbeddedMode) {
            return '';
        }

        return parent::getProfileBackUrl();
    }

    public function finishEditing() : void
    {
        $this->ctrl->redirectByClass(ilObjForumGUI::class, 'showThreads');
    }

    public function setDefaultLinkXml() : void
    {
        parent::setDefaultLinkXml();

        if ($this->isEmbeddedMode) {
            $linkXml = $this->getLinkXML();

            try {
                $linkXml = str_replace('<LinkTargets></LinkTargets>', '', $linkXml);
                
                $domDoc = new DOMDocument();
                $domDoc->loadXML('<?xml version="1.0" encoding="UTF-8"?>' . $linkXml);

                $xpath = new DOMXPath($domDoc);
                $links = $xpath->query('//IntLinkInfos/IntLinkInfo');

                if ($links->length > 0) {
                    foreach ($links as $link) {
                        /** @var $link DOMNode */
                        $link->attributes->getNamedItem('LinkTarget')->nodeValue = '_blank';
                    }
                }

                $linkXmlWithBlankTargets = $domDoc->saveXML();

                $this->setLinkXML(str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $linkXmlWithBlankTargets));
            } catch (Throwable $e) {
                $this->log->error(sprintf(
                    'Could not manipulate page editor link XML: %s / Error Message: %s',
                    $linkXml,
                    $e->getMessage()
                ));
            }
        }
    }
}
