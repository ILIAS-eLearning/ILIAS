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
 * Show wiki pages
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjWikiSubItemListGUI extends ilSubItemListGUI
{
    protected ilObjUser $user;

    public function __construct(
        string $a_cmd_class
    ) {
        global $DIC;

        parent::__construct($a_cmd_class);
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
    }

    public function getHTML(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('content');
        foreach ($this->getSubItemIds(true) as $sub_item) {
            if (is_object($this->getHighlighter()) &&
                $this->getHighlighter()->getContent($this->getObjId(), $sub_item) !== '') {
                $this->tpl->setCurrentBlock('sea_fragment');
                $this->tpl->setVariable('TXT_FRAGMENT', $this->getHighlighter()->getContent($this->getObjId(), $sub_item));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock('subitem');

            // TODO: subitem type must returned from lucene
            if (($title = ilWikiPage::lookupTitle($sub_item)) !== false) {
                // Page
                $this->tpl->setVariable('SUBITEM_TYPE', $lng->txt('obj_pg'));
                $this->tpl->setVariable('SEPERATOR', ':');

                $link = '&srcstring=1';
                $link = ilObjWikiGUI::getGotoLink($this->getRefId(), $title) . $link;

                $this->tpl->setVariable('LINK', $link);
                $this->tpl->setVariable('TARGET', $this->getItemListGUI()->getCommandFrame(''));
                $this->tpl->setVariable('TITLE', $title);
            } else {
                $this->tpl->setVariable('SUBITEM_TYPE', $lng->txt('obj_file'));
                $this->tpl->setVariable('SEPERATOR', ':');

                // File
                $this->getItemListGUI()->setChildId('il__file_' . $sub_item);
                $link = $this->getItemListGUI()->getCommandLink('downloadFile');
                $this->tpl->setVariable('LINK', $link);
                $this->tpl->setVariable('TITLE', ilObject::_lookupTitle($sub_item));
            }
            $this->tpl->parseCurrentBlock();

            if (count($this->getSubItemIds(true)) > 1) {
                $this->parseRelevance($sub_item);
            }

            $this->tpl->parseCurrentBlock();
        }

        $this->showDetailsLink();

        return $this->tpl->get();
    }
}
