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
 * Show media pool items
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjMediaPoolSubItemListGUI extends ilSubItemListGUI
{
    public function getHTML(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('content');
        foreach ($this->getSubItemIds(true) as $sub_item) {
            if (
                is_object($this->getHighlighter()) &&
                $this->getHighlighter()->getContent($this->getObjId(), $sub_item) !== ''
            ) {
                $this->tpl->setCurrentBlock('sea_fragment');
                $this->tpl->setVariable('TXT_FRAGMENT', $this->getHighlighter()->getContent($this->getObjId(), $sub_item));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('subitem');
            $this->tpl->setVariable('SEPERATOR', ':');

            switch (ilMediaPoolItem::lookupType($sub_item)) {
                case 'fold':
                    $this->tpl->setVariable('LINK', ilLink::_getLink($this->getRefId(), 'mep', array(), '_' . $sub_item));
                    $this->tpl->setVariable('TARGET', $this->getItemListGUI()->getCommandFrame(''));
                    break;

                case 'mob':
                    $this->tpl->setVariable(
                        'LINK',
                        $this->getItemListGUI()->getCommandLink('allMedia') .
                        '&force_filter=' . $sub_item
                    );
                    $this->tpl->setVariable('TARGET', $this->getItemListGUI()->getCommandFrame(''));
                    break;

                case 'pg':
                    $pool = new ilObjMediaPool($this->getRefId());
                    $parent_id = $pool->getParentId($sub_item);
                    if ($parent_id !== null) {
                        $this->tpl->setVariable('LINK', ilLink::_getLink($this->getRefId(), 'mep', [], '_' . $parent_id));
                        $this->tpl->setVariable('TARGET', $this->getItemListGUI()->getCommandFrame(''));
                    } else {
                        $this->tpl->setVariable('LINK', ilLink::_getLink($this->getRefId(), 'mep', []));
                        $this->tpl->setVariable('TARGET', $this->getItemListGUI()->getCommandFrame(''));
                    }
                    break;
            }


            $this->tpl->setVariable('SUBITEM_TYPE', $lng->txt('obj_' . ilMediaPoolItem::lookupType($sub_item)));
            $this->tpl->setVariable('TITLE', ilMediaPoolItem::lookupTitle($sub_item));
            #$this->getItemListGUI()->setChildId($sub_item);

            // begin-patch mime_filter

            if (!$this->parseImage($sub_item)) {
                $this->tpl->setVariable('SUBITEM_TYPE', $lng->txt('obj_' . ilMediaPoolItem::lookupType($sub_item)));
                $this->tpl->setVariable('SEPERATOR', ':');
            }

            $this->tpl->parseCurrentBlock();
        }

        $this->showDetailsLink();

        return $this->tpl->get();
    }

    protected function parseImage(int $a_sub_id): bool
    {
        global $DIC;
        $thumbs_gui = $DIC->mediaObjects()->internal()->gui()->thumbs();

        $sub_id = ilMediaPoolItem::lookupForeignId($a_sub_id);
        // output thumbnail (or mob icon)
        if (ilObject::_lookupType($sub_id) === "mob") {
            $mob = new ilObjMediaObject($sub_id);
            $this->tpl->setVariable(
                "SUB_ITEM_IMAGE",
                $thumbs_gui->getThumbHtml($sub_id)
            );
        }
        return false;
    }
}
