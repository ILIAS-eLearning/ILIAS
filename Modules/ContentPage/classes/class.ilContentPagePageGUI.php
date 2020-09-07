<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPagePageGUI
 * @ilCtrl_Calls ilContentPagePageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilContentPagePageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilContentPagePageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 */
class ilContentPagePageGUI extends \ilPageObjectGUI implements \ilContentPageObjectConstants
{
    /** @var bool */
    protected $isEmbeddedMode = false;

    /**
     * ilContentPagePageGUI constructor.
     * @param int $a_id
     * @param int $a_old_nr
     * @param bool $isEmbeddedMode
     */
    public function __construct($a_id = 0, $a_old_nr = 0, $isEmbeddedMode = false)
    {
        parent::__construct(self::OBJ_TYPE, $a_id, $a_old_nr);
        $this->setTemplateTargetVar('ADM_CONTENT');
        $this->setTemplateOutput(false);
        $this->isEmbeddedMode = $isEmbeddedMode;
    }

    /**
     * @inheritdoc
     */
    public function getProfileBackUrl()
    {
        if ($this->isEmbeddedMode) {
            return '';
        }

        return parent::getProfileBackUrl();
    }

    /**
     * @inheritdoc
     */
    public function setDefaultLinkXml()
    {
        parent::setDefaultLinkXml();

        if ($this->isEmbeddedMode) {
            $linkXml = $this->getLinkXML();

            $domDoc = new \DOMDocument();
            $domDoc->loadXML('<?xml version="1.0" encoding="UTF-8"?>' . $linkXml);

            $xpath = new \DOMXPath($domDoc);
            $links = $xpath->query('//IntLinkInfos/IntLinkInfo');

            if ($links->length > 0) {
                foreach ($links as $link) {
                    /** @var $link \DOMNode */
                    $link->attributes->getNamedItem('LinkTarget')->nodeValue = '_blank';
                }
            }

            $linkXmlWithBlankTargets = $domDoc->saveXML();

            $this->setLinkXML(str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $linkXmlWithBlankTargets));
            return;
        }
    }
}
