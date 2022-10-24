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

                default:

            }


            $this->tpl->setVariable('SUBITEM_TYPE', $lng->txt('obj_' . ilMediaPoolItem::lookupType($sub_item)));
            $this->tpl->setVariable('TITLE', ilMediaPoolItem::lookupTitle($sub_item));
            #$this->getItemListGUI()->setChildId($sub_item);

            // begin-patch mime_filter

            if (!$this->parseImage($sub_item)) {
                $this->tpl->setVariable('SUBITEM_TYPE', $lng->txt('obj_' . ilMediaPoolItem::lookupType($sub_item)));
                $this->tpl->setVariable('SEPERATOR', ':');
            }


            if (count($this->getSubItemIds(true)) > 1) {
                $this->parseRelevance($sub_item);
            }

            $this->tpl->parseCurrentBlock();
        }

        $this->showDetailsLink();

        return $this->tpl->get();
    }

    protected function parseImage(int $a_sub_id): bool
    {
        $sub_id = ilMediaPoolItem::lookupForeignId($a_sub_id);
        // output thumbnail (or mob icon)
        if (ilObject::_lookupType($sub_id) === "mob") {
            $mob = new ilObjMediaObject($sub_id);
            $med = $mob->getMediaItem("Standard");
            $target = $med->getThumbnailTarget();

            if ($target != "") {
                // begin-patch mime_filter
                $this->tpl->setVariable(
                    'LINKED_LINK',
                    ilLink::_getLink(
                        $this->getRefId(),
                        'mep',
                        array('action' => 'showMedia', 'mob_id' => $sub_id,'mepitem_id' => $a_sub_id)
                    )
                );
                $this->tpl->setVariable('LINKED_TARGET', '_blank');
                $this->tpl->setVariable("LINKED_IMAGE", ilUtil::img($target));
            // end-patch mime_filter
            } else {
                $this->tpl->setVariable("SUB_ITEM_IMAGE", ilUtil::img(ilUtil::getImagePath("icon_" . "mob" . ".gif")));
            }
            if (ilUtil::deducibleSize($med->getFormat()) && $med->getLocationType() === "Reference") {
                $size = getimagesize($med->getLocation());
                if ($size[0] > 0 && $size[1] > 0) {
                    $wr = $size[0] / 80;
                    $hr = $size[1] / 80;
                    $r = max($wr, $hr);
                    $w = (int) ($size[0] / $r);
                    $h = (int) ($size[1] / $r);
                    $this->tpl->setVariable("SUB_ITEM_IMAGE", ilUtil::img($med->getLocation(), "", $w, $h));
                    return true;
                }
            }
        }
        return false;
    }
}
