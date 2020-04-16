<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Mail/classes/class.ilPDMailBlockGUI.php");

/**
* BlockGUI class for System Messages block on personal desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPDSysMessageBlockGUI: ilColumnGUI
*/
class ilPDSysMessageBlockGUI extends ilPDMailBlockGUI
{
    public static $block_type = "pdsysmess";

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTitle($this->lng->txt("show_system_messages"));
        $this->setAvailableDetailLevels(3);
        $this->mail_mode = "system";
        $this->allow_moving = false;
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    public function getHTML()
    {
        if ($this->getCurrentDetailLevel() < 1) {
            $this->setCurrentDetailLevel(1);
        }

        $html = parent::getHTML();
        
        if (count($this->mails) == 0) {
            return "";
        } else {
            return $html;
        }
    }
    
    /**
    * Get Mails
    */
    public function getMails()
    {
        $umail = new ilMail($this->user->getId());
        $mbox = new ilMailbox($this->user->getId());
        $inbox = $mbox->getInboxFolder();

        $this->mails = $umail->getMailsOfFolder($inbox, array('status' => 'unread', 'type' => 'system'));
    }

    /**
    * Get overview.
    */
    public function getOverview()
    {
        return '<div class="small">' . ((int) count($this->mails)) . " " . $this->lng->txt("system_message") . "</div>";
    }
}
