<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Block/classes/class.ilBlockGUI.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomServerConnector.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomServerSettings.php';
require_once 'Services/JSON/classes/class.ilJsonUtil.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomBlock.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';

/**
 * Class ilChatroomBlockGUI
 * @author            Michael Jansen <mjansen@databay.de>
 * @version           $Id$
 * @ilCtrl_IsCalledBy ilChatroomBlockGUI: ilColumnGUI
 */
class ilChatroomBlockGUI extends ilBlockGUI
{
    /**
     * @var string
     * @static
     */
    public static $block_type = 'chatviewer';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->lng->loadLanguageModule('chatroom');

        $this->setImage(ilUtil::getImagePath('icon_chat.svg'));
        $this->setTitle($this->lng->txt('chat_chatviewer'));
        $this->setAvailableDetailLevels(1, 0);
        $this->allow_moving = true;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
     * @static
     * @return string
     */
    public static function getScreenMode()
    {
        switch ($_GET['cmd']) {
            default:
                return IL_SCREEN_SIDE;
                break;
        }
    }

    /**
     * @return string
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd('getHTML');

        return $this->$cmd();
    }

    /**
     * @return string
     */
    public function getHTML()
    {
        ilYuiUtil::initJson();

        $chatSetting = new ilSetting('chatroom');
        if ($this->getCurrentDetailLevel() == 0 || !$chatSetting->get('chat_enabled', 0) || !(bool) ilChatroomServerConnector::checkServerConnection()) {
            return '';
        } else {
            return parent::getHTML();
        }
    }

    /**
     * Fill data section
     */
    public function fillDataSection()
    {
        if ($this->ctrl->isAsynch() && isset($_GET['chatBlockCmd'])) {
            return $this->dispatchAsyncCommand($_GET['chatBlockCmd']);
        }

        $this->main_tpl->addJavascript('./Modules/Chatroom/js/chatviewer.js');
        $this->main_tpl->addCss('./Modules/Chatroom/templates/default/style.css');

        $body_tpl = new ilTemplate('tpl.chatroom_block_message_body.html', true, true, 'Modules/Chatroom');

        $body_tpl->setVariable('TXT_ENABLE_AUTOSCROLL', $this->lng->txt('chat_enable_autoscroll'));
        $body_tpl->setVariable('LOADER_PATH', ilUtil::getImagePath('loader.svg'));

        $this->ctrl->setParameterByClass('ilcolumngui', 'block_id', 'block_' . $this->getBlockType() . '_' . (int) $this->getBlockId());
        $this->ctrl->setParameterByClass('ilcolumngui', 'ref_id', '#__ref_id');
        $body_tpl->setVariable('CHATBLOCK_BASE_URL', $this->ctrl->getLinkTargetByClass('ilcolumngui', 'updateBlock', '', true));
        $this->ctrl->setParameterByClass('ilcolumngui', 'block_id', '');
        $this->ctrl->setParameterByClass('ilcolumngui', 'ref_id', '');

        $smilieys = array();
        $settings = ilChatroomServerSettings::loadDefault();
        if ($settings->getSmiliesEnabled()) {
            $smilies_array = ilChatroomSmilies::_getSmilies();
            foreach ($smilies_array as $smiley_array) {
                foreach ($smiley_array as $key => $value) {
                    if ($key == 'smiley_keywords') {
                        $new_keys = explode("\n", $value);
                    }

                    if ($key == 'smiley_fullpath') {
                        $new_val = $value;
                    }
                }

                foreach ($new_keys as $new_key) {
                    $smilieys[$new_key] = $new_val;
                }
            }
        } else {
            $smilieys = new stdClass();
        }
        $body_tpl->setVariable('SMILIES', json_encode($smilieys));

        $js_translations = array(
            'LBL_CONNECT' => 'chat_connection_established',
            'LBL_DISCONNECT' => 'chat_connection_disconnected',
            'LBL_TIMEFORMAT' => 'lang_timeformat_no_sec',
            'LBL_DATEFORMAT' => 'lang_dateformat'
        );
        foreach ($js_translations as $placeholder => $lng_variable) {
            $body_tpl->setVariable($placeholder, json_encode($this->lng->txt($lng_variable)));
        }

        $content = $body_tpl->get();
        $this->setDataSection($content);
    }

    /**
     * @param $cmd
     */
    protected function dispatchAsyncCommand($cmd)
    {
        switch ($cmd) {
            case 'getChatroomSelectionList':
                return $this->getChatroomSelectionList();
                break;

            case 'getMessages':
            default:
                return $this->getMessages();
                break;
        }
    }

    protected function getChatroomSelectionList()
    {
        $result = new stdClass();
        $result->ok = true;
        $result->has_records = false;

        $chatblock = new ilChatroomBlock();
        $result->html = $chatblock->getRoomSelect($result);

        include_once 'Services/JSON/classes/class.ilJsonUtil.php';
        echo ilJsonUtil::encode($result);
        exit();
    }

    /**
     */
    protected function getMessages()
    {
        global $DIC;

        $result = new stdClass();
        $result->ok = false;

        if (!(int) $_REQUEST['ref_id']) {
            echo ilJsonUtil::encode($result);
            exit();
        }

        /**
         * @var $object ilObjChatroom
         */
        $object = ilObjectFactory::getInstanceByRefId((int) $_REQUEST['ref_id'], false);
        if (!$object || !ilChatroom::checkUserPermissions('read', (int) $_REQUEST['ref_id'], false)) {
            ilObjUser::_writePref(
                $DIC->user()->getId(),
                'chatviewer_last_selected_room',
                0
            );

            $result->errormsg = $DIC->language()->txt('msg_no_perm_read');
            echo ilJsonUtil::encode($result);
            exit();
        }

        require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
        $room = ilChatroom::byObjectId($object->getId());

        $block = new ilChatroomBlock();
        $msg = $block->getMessages($room);

        $DIC->user()->setPref(
            'chatviewer_last_selected_room',
            $object->getRefId()
        );
        ilObjUser::_writePref(
            $DIC->user()->getId(),
            'chatviewer_last_selected_room',
            $object->getRefId()
        );

        $result->messages = array_reverse($msg);
        $result->ok = true;

        include_once 'Services/JSON/classes/class.ilJsonUtil.php';
        echo ilJsonUtil::encode($result);
        exit();
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }
}
