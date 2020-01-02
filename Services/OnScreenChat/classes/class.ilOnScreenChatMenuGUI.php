<?php

/**
 * Class ilOnScreenChatMenuGUI
 *
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @since   03.08.16
 */
class ilOnScreenChatMenuGUI
{
    /**
     * @var integer
     */
    protected $pub_ref_id;

    /**
     * @var bool
     */
    protected $accessible = false;

    /**
     * @var bool
     */
    protected $publicChatRoomAccess = false;

    /**
     * @var bool
     */
    protected $oscAccess = false;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * ilOnScreenChatMenuGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->init();
        $this->ui = $DIC->ui();
    }

    /**
     * @return bool
     */
    protected function init()
    {
        global $DIC;

        require_once 'Modules/Chatroom/classes/class.ilObjChatroom.php';
        $this->pub_ref_id = ilObjChatroom::_getPublicRefId();

        if (!$DIC->user() || $DIC->user()->isAnonymous()) {
            $this->accessible = false;
            return;
        }

        $chatSettings = new ilSetting('chatroom');

        $this->publicChatRoomAccess = $DIC->rbac()->system()->checkAccessOfUser($DIC->user()->getId(), 'read', $this->pub_ref_id);
        $this->oscAccess            = $chatSettings->get('enable_osc');

        $this->accessible = $chatSettings->get('chat_enabled') && ($this->oscAccess || $this->publicChatRoomAccess);
    }

    /**
     * @return string
     */
    public function getMainMenuHTML()
    {
        global $DIC;

        if (!$this->accessible) {
            return '';
        }

        require_once 'Services/Link/classes/class.ilLinkifyUtil.php';
        ilLinkifyUtil::initLinkify();

        require_once 'Services/JSON/classes/class.ilJsonUtil.php';
        $DIC->language()->loadLanguageModule('chatroom');

        $config = array(
            'conversationTemplate' => (new ilTemplate('tpl.chat-menu-item.html', false, false, 'Services/OnScreenChat'))->get(),
            'roomTemplate'         => (new ilTemplate('tpl.chat-menu-item-room.html', false, false, 'Services/OnScreenChat'))->get(),
            'infoTemplate'         => (new ilTemplate('tpl.chat-menu-item-info.html', false, false, 'Services/OnScreenChat'))->get(),
            'userId'               => $DIC->user()->getId()
        );

        $config['rooms'] = array();

        if ($this->publicChatRoomAccess) {
            $config['rooms'][] = array(
                'name' => $DIC['ilObjDataCache']->lookupTitle($DIC['ilObjDataCache']->lookupObjId($this->pub_ref_id)),
                'url'  => './ilias.php?baseClass=ilRepositoryGUI&amp;cmd=view&amp;ref_id=' . $this->pub_ref_id,
                'icon' => ilObject::_getIcon($DIC['ilObjDataCache']->lookupObjId($this->pub_ref_id), 'small', 'chtr')
            );
        }

        $config['showAcceptMessageChange'] = (
            !ilUtil::yn2tf($DIC->user()->getPref('chat_osc_accept_msg')) &&
            !(bool) $DIC['ilSetting']->get('usr_settings_hide_chat_osc_accept_msg', false) &&
            !(bool) $DIC['ilSetting']->get('usr_settings_disable_chat_osc_accept_msg', false)
        );
        $config['showOnScreenChat'] = $this->oscAccess;

        $DIC->language()->loadLanguageModule('chatroom');
        $DIC->language()->toJS(array(
            'chat_osc_conversations', 'chat_osc_section_head_other_rooms',
            'chat_osc_sure_to_leave_grp_conv', 'chat_osc_user_left_grp_conv',
            'confirm', 'cancel', 'chat_osc_leave_grp_conv', 'chat_osc_no_conv'
        ));
        $DIC->language()->toJSMap(array(
            'chat_osc_dont_accept_msg' => sprintf(
                $DIC->language()->txt('chat_osc_dont_accept_msg'),
                $DIC->ctrl()->getLinkTargetByClass(array('ilPersonalDesktopGUI', 'ilPersonalSettingsGUI', 'ilPersonalChatSettingsFormGUI'), 'showChatOptions')
            )
        ));

        $DIC['tpl']->addJavascript('./Services/OnScreenChat/js/onscreenchat-menu.js');
        $DIC['tpl']->addJavascript('./Services/UIComponent/Modal/js/Modal.js');

        $tpl = new ilTemplate('tpl.chat-menu.html', true, true, 'Services/OnScreenChat');

        $f        = $this->ui->factory();
        $renderer = $this->ui->renderer();

        $glyph = $f->glyph()->comment();
        $glyph = $glyph->withCounter($f->counter()->status(0))->withCounter($f->counter()->novelty(0))->withOnLoadCode(function ($id) use (&$glyph_id) {
            $glyph_id = $id;
            return '';
        });
        $glyph_html = $renderer->render($glyph);

        $config['triggerId'] = $glyph_id;
        $config['conversationNoveltyCounter'] = $renderer->render($f->counter()->novelty(0));

        $DIC['tpl']->addOnLoadCode("il.OnScreenChatMenu.setConfig(" . ilJsonUtil::encode($config) . ");");
        $DIC['tpl']->addOnLoadCode("il.OnScreenChatMenu.init();");

        $tpl->setVariable('GLYPH', $glyph_html);
        $tpl->setVariable('LOADER', ilUtil::getImagePath('loader.svg'));
        return $tpl->get();
    }
}
