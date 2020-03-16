<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Show glossary terms
 *
 * @author Stefan Meyer <meyer@leifos.com>
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

            $src_string = ilUserSearchCache::_getInstance($ilUser->getId())->getUrlEncodedQuery();
            
            $this->tpl->setVariable('LINK', ilLink::_getLink(
                $this->getRefId(),
                'git',
                array(
                    'target' => 'git_' . $sub_item . '_' . $this->getRefId(),
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
