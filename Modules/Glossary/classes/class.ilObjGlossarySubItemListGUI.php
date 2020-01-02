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
include_once './Modules/Glossary/classes/class.ilGlossaryTerm.php';
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
class ilObjGlossarySubItemListGUI extends ilSubItemListGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;


    /**
     * Constructor
     */
    public function __construct($a_cmd_class)
    {
        global $DIC;

        parent::__construct($a_cmd_class);
        $this->user = $DIC->user();
    }

    /**
     * get html
     * @return
     */
    public function getHTML()
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        
        $lng->loadLanguageModule('content');
        foreach ($this->getSubItemIds(true) as $sub_item) {
            if (is_object($this->getHighlighter()) and strlen($this->getHighlighter()->getContent($this->getObjId(), $sub_item))) {
                $this->tpl->setCurrentBlock('sea_fragment');
                $this->tpl->setVariable('TXT_FRAGMENT', $this->getHighlighter()->getContent($this->getObjId(), $sub_item));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('subitem');
            $this->tpl->setVariable('SUBITEM_TYPE', $lng->txt('cont_term'));
            $this->tpl->setVariable('SEPERATOR', ':');
            
            #$this->getItemListGUI()->setChildId($sub_item);
            
            include_once './Services/Search/classes/class.ilUserSearchCache.php';
            $src_string = ilUserSearchCache::_getInstance($ilUser->getId())->getUrlEncodedQuery();
            
            $this->tpl->setVariable('LINK', ilLink::_getLink(
                $this->getRefId(),
                'git',
                array(
                    'target' 	=> 'git_' . $sub_item . '_' . $this->getRefId(),
                    'srcstring' => 1
                )
            ));
            
            $this->tpl->setVariable('TARGET', $this->getItemListGUI()->getCommandFrame(''));
            $this->tpl->setVariable('TITLE', ilGlossaryTerm::_lookGlossaryTerm($sub_item));

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
