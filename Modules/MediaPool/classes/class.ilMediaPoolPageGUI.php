<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilMediaPoolPage GUI class
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilMediaPoolPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilMediaPoolPageGUI: ilPublicUserProfileGUI
 */
class ilMediaPoolPageGUI extends ilPageObjectGUI
{
    protected ilTabsGUI $tabs;

    public function __construct(
        int $a_id = 0,
        int $a_old_nr = 0,
        bool $a_prevent_get_id = false,
        string $a_lang = ""
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        
        parent::__construct("mep", $a_id, $a_old_nr, $a_prevent_get_id, $a_lang);

        $this->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(0));

        $this->setEditPreview(true);
    }
    
    public function setMediaPoolPage(
        ilMediaPoolPage $a_media_pool_page
    ) : void {
        $this->setPageObject($a_media_pool_page);
    }

    public function getMediaPoolPage() : ilMediaPoolPage
    {
        /** @var ilMediaPoolPage $p */
        $p = $this->getPageObject();
        return $p;
    }

    public function showPage(
        bool $a_no_title = false
    ) : string {
        $tpl = $this->tpl;

        // get raw page content is used for including into other pages
        if (!$this->getRawPageContent()) {
            $tpl->setCurrentBlock("ContentStyle");
            $tpl->setVariable(
                "LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath(0)
            );
            $tpl->parseCurrentBlock();
        }

        $this->setTemplateOutput(false);
        if (!$a_no_title) {
            $this->setPresentationTitle(ilMediaPoolItem::lookupTitle($this->getMediaPoolPage()->getId()));
        }
        $output = parent::showPage();
        
        return $output;
    }

    public function getRawContent() : string
    {
        $this->setRawPageContent(true);
        $this->setLinkXml("");
        return $this->showPage(true);
    }

    public function setTemplate(ilGlobalTemplateInterface $tpl) : void
    {
        $this->tpl = $tpl;
    }
}
