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
include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
include_once './Modules/WebResource/classes/class.ilParameterAppender.php';

include_once './Services/Link/classes/class.ilLink.php';

/**
* Show glossary terms
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ModulesGlossary
*/
class ilObjLinkResourceSubItemListGUI extends ilSubItemListGUI
{
    /**
     * get html
     * @return
     */
    public function getHTML()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule('webr');
        foreach ($this->getSubItemIds(true) as $sub_item) {
            if (is_object($this->getHighlighter()) and strlen($this->getHighlighter()->getContent($this->getObjId(), $sub_item))) {
                $this->tpl->setCurrentBlock('sea_fragment');
                $this->tpl->setVariable('TXT_FRAGMENT', $this->getHighlighter()->getContent($this->getObjId(), $sub_item));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('subitem');
            $this->tpl->setVariable('SUBITEM_TYPE', $lng->txt('webr'));
            $this->tpl->setVariable('SEPERATOR', ':');
            
            $link_data = ilLinkResourceItems::lookupItem($this->getObjId(), $sub_item);
            $link_data = ilParameterAppender::_append($link_data);

            // handle internal links (#10620)
            if (stristr($link_data["target"], "|")) {
                $parts = explode("|", $link_data["target"]);
                if ($parts[0] == "page") {
                    $parts[0] = "pg";
                }
                if ($parts[0] == "term") {
                    $parts[0] = "git";
                }
                $link_data["target"] = ilLink::_getStaticLink($parts[1], $parts[0]);
            }
            
            #$this->getItemListGUI()->setChildId($sub_item);
            $this->tpl->setVariable('LINK', $link_data['target']);
            $this->tpl->setVariable('TARGET', '_blank');
            $this->tpl->setVariable('TITLE', $link_data['title']);
            
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
