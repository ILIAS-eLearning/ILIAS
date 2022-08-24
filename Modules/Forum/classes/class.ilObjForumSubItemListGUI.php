<?php

declare(strict_types=1);

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
 * Show forum threads
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesForum
 */
class ilObjForumSubItemListGUI extends ilSubItemListGUI
{
    public function getHTML(): string
    {
        global $DIC;

        foreach ($this->getSubItemIds(true) as $sub_item) {
            if (is_object($this->getHighlighter()) && $this->getHighlighter()->getContent(
                $this->getObjId(),
                $sub_item
            ) !== '') {
                $this->tpl->setCurrentBlock('sea_fragment');
                $this->tpl->setVariable(
                    'TXT_FRAGMENT',
                    $this->getHighlighter()->getContent($this->getObjId(), $sub_item)
                );
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('subitem');
            $this->tpl->setVariable('SUBITEM_TYPE', $DIC->language()->txt('thread'));
            $this->tpl->setVariable('SEPERATOR', ':');

            $this->getItemListGUI()->setChildId($sub_item);
            $this->tpl->setVariable('LINK', $this->getItemListGUI()->getCommandLink('thread'));
            $this->tpl->setVariable('TARGET', $this->getItemListGUI()->getCommandFrame(''));
            $this->tpl->setVariable('TITLE', ilObjForum::_lookupThreadSubject($sub_item));

            // begin-patch mime_filter
            if (count($this->getSubItemIds(true)) > 1) {
                $this->parseRelevance($sub_item);
            }
            // end-patch mime_filter

            $this->tpl->parseCurrentBlock();
        }

        $this->showDetailsLink();

        return $this->tpl->get();
    }
}
