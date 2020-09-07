<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Captcha/classes/class.ilSecurImageUtil.php';

/**
 * This class represents a captcha input in a property form.
 * @author     Alex Killing <alex.killing@gmx.de>
 * @author     Michael Jansen <mjansen@databay.de>
 * @version    $Id$
 * @ingroup    ServicesCaptcha
 */
class ilCaptchaInputGUI extends ilFormPropertyGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var int
     */
    protected $size = 20;

    /**
     * @var int
     */
    protected $image_width = 215;

    /**
     * @var int
     */
    protected $image_height = 80;

    /**
     * Constructor
     * @param    string $a_title      Title
     * @param    string $a_postvar    Post Variable
     */
    public function __construct($a_title = '', $a_postvar = '')
    {
        global $DIC;

        $this->lng = $DIC->language();
        $lng = $DIC->language();

        parent::__construct($a_title, $a_postvar);
        $this->setType('captcha');
        if ($lng instanceof ilLanguage) {
            $lng->loadLanguageModule('cptch');
        }
    }

    /**
     * Set Value.
     * @param    string $a_value    Value
     */
    public function setValue($a_value)
    {
        $this->value = $a_value;
    }

    /**
     * Get Value.
     * @return    string    Value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $image_height
     */
    public function setImageHeight($image_height)
    {
        $this->image_height = $image_height;
    }

    /**
     * @return int
     */
    public function getImageHeight()
    {
        return $this->image_height;
    }

    /**
     * @param int $image_width
     */
    public function setImageWidth($image_width)
    {
        $this->image_width = $image_width;
    }

    /**
     * @return int
     */
    public function getImageWidth()
    {
        return $this->image_width;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     * @return    boolean        Input ok, true/false
     */
    public function checkInput()
    {
        $lng = $this->lng;

        $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
        if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == '') {
            if ($lng instanceof ilLanguage) {
                $this->setAlert($lng->txt('msg_input_is_required'));
            }

            return false;
        }

        include_once('./Services/Captcha/classes/class.ilSecurImage.php');
        $si = new ilSecurImage();
        if (!$si->check($_POST[$this->getPostVar()])) {
            if ($lng instanceof ilLanguage) {
                $this->setAlert($lng->txt('cptch_wrong_input'));
            }

            return false;
        }

        return true;
    }

    /**
     * Render output
     */
    public function render()
    {
        /**
         * @var $lng ilLanguage
         */
        $lng = $this->lng;

        $tpl = new ilTemplate('tpl.prop_captchainput.html', true, true, 'Services/Captcha');

        if (strlen($this->getValue())) {
            $tpl->setCurrentBlock('prop_text_propval');
            $tpl->setVariable('PROPERTY_VALUE', ilUtil::prepareFormOutput($this->getValue()));
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable('POST_VAR', $this->getPostVar());
        $tpl->setVariable('CAPTCHA_ID', $this->getFieldId());
        $tpl->setVariable('SIZE', $this->getSize());

        $script = ilSecurImageUtil::getImageScript();
        $script = ilUtil::appendUrlParameterString($script, 'height=' . (int) $this->getImageHeight(), true);
        $script = ilUtil::appendUrlParameterString($script, 'width=' . (int) $this->getImageWidth(), true);
        $tpl->setVariable('IMAGE_SCRIPT', $script);
        $tpl->setVariable('AUDIO_SCRIPT', ilSecurImageUtil::getAudioScript());
        $tpl->setVariable('SRC_RELOAD', ilSecurImageUtil::getDirectory() . '/images/refresh.png');

        $tpl->setVariable('TXT_CAPTCHA_ALT', $lng->txt('captcha_alt_txt'));
        $tpl->setVariable('TXT_RELOAD', $lng->txt('captcha_code_reload'));
        $tpl->setVariable('TXT_CAPTCHA_AUDIO_TITLE', $lng->txt('captcha_audio_title'));
        $tpl->setVariable('TXT_CONSTR_PROP', $lng->txt('cont_constrain_proportions'));
        $tpl->setVariable('TXT_CAPTCHA_INFO', $lng->txt('captcha_code_info'));

        return $tpl->get();
    }

    /**
     * @param ilTemplate $a_tpl
     */
    public function insert(ilTemplate $a_tpl)
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $html);
        $a_tpl->parseCurrentBlock();
    }

    /**
     * Set value by array
     * @param    array $a_values    value array
     */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }
}
