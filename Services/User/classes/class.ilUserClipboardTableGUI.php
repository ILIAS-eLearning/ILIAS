<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/User/classes/class.ilObjUser.php';
include_once './Services/User/classes/class.ilUserClipboard.php';
include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Show administrate clipboard content
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilUserClipboardTableGUI extends ilTable2GUI
{
    /**
     * @var ilUserClipboard
     */
    private $clipboard;
    
    /**
     * Constructor
     * @param type $a_parent_obj
     * @param type $a_parent_cmd
     * @param int $a_id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_id)
    {
        $this->setId('obj_table_' . $a_id);
        parent::__construct($a_parent_obj, $a_parent_cmd, '');
        
        $this->clipboard = ilUserClipboard::getInstance($a_id);
        $this->lng->loadLanguageModule('user');
    }
    
    /**
     * init table
     */
    public function init()
    {
        $this->setTitle($this->lng->txt('clipboard_table_title'));
        
        $this->addColumn('', 'id', '5px');
        $this->addColumn($this->lng->txt('name'), 'name', '70%');
        $this->addColumn($this->lng->txt('login'), 'login', '30%');
        
        $this->setOrderColumn('name');
        $this->setRowTemplate('tpl.usr_clipboard_table_row.html', 'Services/User');
        
        $this->setSelectAllCheckbox('uids');
        
        $this->addMultiCommand(
            'addFromClipboard',
            $this->lng->txt('add')
        );
        
        $this->addMultiCommand(
            'removeFromClipboard',
            $this->lng->txt('clipboard_remove_btn')
        );
        
        $this->addCommandButton('emptyClipboard', $this->lng->txt('clipboard_empty_btn'));
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }

    /**
     * Fill row
     * @param type $a_set
     */
    public function fillRow($a_set)
    {
        $this->tpl->setVariable('VAL_POSTNAME', 'uids');
        $this->tpl->setVariable('VAL_ID', $a_set['usr_id']);
        $this->tpl->setVariable('VAL_NAME', $a_set['name']);
        $this->tpl->setVariable('VAL_LOGIN', $a_set['login']);
    }
    
    /**
     * Parse clipboard content
     */
    public function parse()
    {
        $content = array();
        foreach ($this->clipboard->getValidatedContent() as $user_id) {
            $row['usr_id'] = $user_id;
            $name_arr = ilObjUser::_lookupName($user_id);
            
            $row['name'] = ($name_arr['lastname'] . ', ' . $name_arr['firstname']);
            $row['login'] = ilObjUser::_lookupLogin($user_id);
            
            $content[] = $row;
        }
        $this->setMaxCount(count($this->clipboard->getValidatedContent()));
        $this->setData($content);
    }
}
