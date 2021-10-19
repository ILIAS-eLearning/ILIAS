<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\HTTP\Response\ResponseHeader;

/**
 * Class ilChatroomAuthInputGUI
 * @author            Michael Jansen <mjansen@databay.de>
 * @author            Thomas Joußen <tjoussen@databay.de>
 * @ilCtrl_IsCalledBy ilChatroomAuthInputGUI: ilFormPropertyDispatchGUI
 */
class ilChatroomAuthInputGUI extends ilSubEnabledFormPropertyGUI
{
    private const NAME_AUTH_PROP_1 = 'key';
    private const NAME_AUTH_PROP_2 = 'secret';

    protected \ILIAS\HTTP\Services $http;
    /** @var string[]  */
    protected array $ctrl_path = [];
    protected int $size = 10;
    protected array $values = [
        self::NAME_AUTH_PROP_1 => '',
        self::NAME_AUTH_PROP_2 => ''
    ];
    protected bool $isReadOnly = false;

    public function __construct(string $title, string $httpPostVariableName, \ILIAS\HTTP\Services $http)
    {
        parent::__construct($title, $httpPostVariableName);
        $this->http = $http;
    }

    public function setIsReadOnly(bool $isReadOnly) : void
    {
        $this->isReadOnly = $isReadOnly;
    }

    protected function getRandomValues() : void
    {
        $response = new stdClass();

        $response->{self::NAME_AUTH_PROP_1} = $this->uuidV4();
        $response->{self::NAME_AUTH_PROP_2} = $this->uuidV4();

        $responseStream = \ILIAS\Filesystem\Stream\Streams::ofString(json_encode($response));
        $this->http->saveResponse(
            $this->http->response()
                ->withBody($responseStream)
                ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    private function uuidV4() : string
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
     * @param string[] $ctrl_path
     */
    public function setCtrlPath(array $ctrl_path) : void
    {
        $this->ctrl_path = $ctrl_path;
    }

    public function setValueByArray(array $a_values) : void
    {
        $this->values = array(
            self::NAME_AUTH_PROP_1 => $a_values[$this->getPostVar()][self::NAME_AUTH_PROP_1],
            self::NAME_AUTH_PROP_2 => $a_values[$this->getPostVar()][self::NAME_AUTH_PROP_2]
        );

        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

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

    public function insert(ilTemplate $a_tpl)
    {
        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $this->render());
        $a_tpl->parseCurrentBlock();
    }

    public function render() : string
    {
        global $DIC;

        $tpl = new ilTemplate('tpl.chatroom_auth_input.html', true, true, 'Modules/Chatroom');

        for ($i = 1, $iMax = count($this->values); $i <= $iMax; $i++) {
            $const = 'NAME_AUTH_PROP_' . $i;
            $const_val = constant('self::' . $const);

            $tpl->setVariable('TXT_AUTH_PROP_' . $i, $DIC->language()->txt('chatroom_auth_' . $const_val));
            $tpl->setVariable('ID_AUTH_PROP_' . $i, $const_val);
            $tpl->setVariable('NAME_AUTH_PROP_' . $i, $const_val);
            $tpl->setVariable('VALUE_AUTH_PROP_' . $i, $this->values[$const_val]);
        }

        if (!$this->isReadOnly && !$this->getDisabled()) {
            for ($i = 1, $iMax = count($this->values); $i <= $iMax; $i++) {
                $const = 'NAME_AUTH_PROP_' . $i;
                $const_val = constant('self::' . $const);

                $tpl->setVariable('ID_AUTH_PROP_' . $i . '_BTN', $const_val);
            }

            $DIC->ctrl()->setParameterByClass('ilformpropertydispatchgui', 'postvar', $this->getPostVar());
            $tpl->setVariable(
                'URL',
                $DIC->ctrl()->getLinkTargetByClass($this->ctrl_path, 'getRandomValues', '', true, false)
            );
            $tpl->setVariable('ID_BTN', $this->getFieldId() . '_btn');
            $tpl->setVariable('TXT_BTN', $DIC->language()->txt('chatroom_auth_btn_txt'));
        }

        $tpl->setVariable('POST_VAR', $this->getPostVar());
        $tpl->setVariable('SIZE', $this->getSize());

        if ($this->getDisabled()) {
            $tpl->setVariable('DISABLED', ' disabled="disabled"');
        }

        return $tpl->get();
    }

    public function getSize() : int
    {
        return $this->size;
    }

    public function setSize(int $size) : void
    {
        $this->size = $size;
    }
}
