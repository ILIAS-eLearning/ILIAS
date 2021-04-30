<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once './Services/Object/classes/class.ilSubItemListGUI.php';
include_once './Services/Link/classes/class.ilLink.php';

/**
* Show media pool items
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ModulesMediaPool
*/
class ilObjMediaPoolSubItemListGUI extends ilSubItemListGUI
{
    /**
     * get html
     * @return
     */
    public function getHTML()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule('content');
        foreach ($this->getSubItemIds(true) as $sub_item) {
            if (is_object($this->getHighlighter()) and strlen($this->getHighlighter()->getContent($this->getObjId(), $sub_item))) {
                $this->tpl->setCurrentBlock('sea_fragment');
                $this->tpl->setVariable('TXT_FRAGMENT', $this->getHighlighter()->getContent($this->getObjId(), $sub_item));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('subitem');
            $this->tpl->setVariable('SEPERATOR', ':');
            
            include_once './Modules/MediaPool/classes/class.ilMediaPoolItem.php';
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
                include_once './Modules/MediaPool/classes/class.ilMediaPoolItem.php';
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
    
    protected function parseImage($a_sub_id)
    {
        include_once './Modules/MediaPool/classes/class.ilMediaPoolItem.php';
        $sub_id = ilMediaPoolItem::lookupForeignId($a_sub_id);
        // output thumbnail (or mob icon)
        if (ilObject::_lookupType($sub_id) == "mob") {
            include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
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
                $this->tpl->setVariable("SUB_ITEM_IMAGE", ilUtil::img(ilUtil::getImagePath("icon_" . $a_set["type"] . ".gif")));
            }
            if (ilUtil::deducibleSize($med->getFormat()) && $med->getLocationType() == "Reference") {
                $size = @getimagesize($med->getLocation());
                if ($size[0] > 0 && $size[1] > 0) {
                    $wr = $size[0] / 80;
                    $hr = $size[1] / 80;
                    $r = max($wr, hr);
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
