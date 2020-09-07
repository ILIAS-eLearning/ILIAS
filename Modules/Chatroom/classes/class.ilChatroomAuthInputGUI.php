<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomAuthInputGUI
 * @author            Michael Jansen <mjansen@databay.de>
 * @author            Thomas Joußen <tjoussen@databay.de>
 * @ilCtrl_IsCalledBy ilChatroomAuthInputGUI: ilFormPropertyDispatchGUI
 */
class ilChatroomAuthInputGUI extends ilSubEnabledFormPropertyGUI
{
    const NAME_AUTH_PROP_1 = 'key';
    const NAME_AUTH_PROP_2 = 'secret';

    /**
     * @var array An array of ilCtrl nodes
     */
    protected $ctrl_path = array();

    /**
     * @var int
     */
    protected $size = 10;
    /**
     * @var array
     */
    protected $values = array(
        self::NAME_AUTH_PROP_1 => '',
        self::NAME_AUTH_PROP_2 => ''
    );

    /**
     *
     */
    protected function getRandomValues()
    {
        $response = new stdClass();

        $response->{self::NAME_AUTH_PROP_1} = $this->uuidV4();
        $response->{self::NAME_AUTH_PROP_2} = $this->uuidV4();

        echo json_encode($response);
        exit();
    }

    /**
     * Generates a pseudo random string following the RFC 4122
     * @return string
     */
    private function uuidV4()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_high_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * @return array
     */
    public function getCtrlPath()
    {
        return $this->ctrl_path;
    }

    /**
     * @param array $ctrl_path
     */
    public function setCtrlPath($ctrl_path)
    {
        $this->ctrl_path = $ctrl_path;
    }

    /**
     * @param array $a_values
     */
    public function setValueByArray(array $a_values)
    {
        $this->values = array(
            self::NAME_AUTH_PROP_1 => $a_values[$this->getPostVar()][self::NAME_AUTH_PROP_1],
            self::NAME_AUTH_PROP_2 => $a_values[$this->getPostVar()][self::NAME_AUTH_PROP_2]
        );

        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkInput()
    {
        global $DIC;

        $_POST[$this->getPostVar()][self::NAME_AUTH_PROP_1] = ilUtil::stripSlashes($_POST[$this->getPostVar()][self::NAME_AUTH_PROP_1]);
        $_POST[$this->getPostVar()][self::NAME_AUTH_PROP_2] = ilUtil::stripSlashes($_POST[$this->getPostVar()][self::NAME_AUTH_PROP_2]);

        $post = $_POST[$this->getPostVar()];

        if ($this->getRequired() && 2 > count(array_filter(array_map('trim', $post)))) {
            $this->setAlert($DIC->language()->txt('msg_input_is_required'));
            return false;
        }

        return $this->checkSubItemsInput();
    }

    /**
     * {@inheritdoc}
     */
    public function insert(ilTemplate $a_tpl)
    {
        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $this->render());
        $a_tpl->parseCurrentBlock();
    }

    /**
     * @return string
     */
    public function render()
    {
        global $DIC;

        $tpl = new ilTemplate('tpl.chatroom_auth_input.html', true, true, 'Modules/Chatroom');

        for ($i = 1; $i <= count($this->values); $i++) {
            $const = 'NAME_AUTH_PROP_' . $i;
            $const_val = constant('self::' . $const);

            $tpl->setVariable('TXT_AUTH_PROP_' . $i, $DIC->language()->txt('chatroom_auth_' . $const_val));
            $tpl->setVariable('ID_AUTH_PROP_' . $i, $const_val);
            $tpl->setVariable('NAME_AUTH_PROP_' . $i, $const_val);
            $tpl->setVariable('VALUE_AUTH_PROP_' . $i, $this->values[$const_val]);
        }

        $DIC->ctrl()->setParameterByClass('ilformpropertydispatchgui', 'postvar', $this->getPostVar());
        $tpl->setVariable('URL', $DIC->ctrl()->getLinkTargetByClass($this->ctrl_path, 'getRandomValues', '', true, false));
        $tpl->setVariable('ID_BTN', $this->getFieldId() . '_btn');
        $tpl->setVariable('TXT_BTN', $DIC->language()->txt('chatroom_auth_btn_txt'));
        $tpl->setVariable('POST_VAR', $this->getPostVar());
        $tpl->setVariable('SIZE', $this->getSize());
        if ($this->getDisabled()) {
            $tpl->setVariable('DISABLED', ' disabled="disabled"');
        }

        return $tpl->get();
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }
}
