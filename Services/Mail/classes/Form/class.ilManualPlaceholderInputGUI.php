<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php';

/**
 * Class ilManualPlaceholderInputGUI
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilManualPlaceholderInputGUI extends ilSubEnabledFormPropertyGUI
{
    /**
     * @var array
     */
    protected $placeholders = array();

    /**
     * @var string
     */
    protected $rerenderUrl;

    /**
     * @var string
     */
    protected $rerenderTriggerElementName;

    /**
     * @var string
     */
    protected $dependencyElementId;

    /**
     * @var string
     */
    protected $instructionText = '';

    /**
     * @var string
     */
    protected $adviseText = '';

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * ilManualPlaceholderInputGUI constructor.
     * @param string $dependencyElementId
     */
    public function __construct($dependencyElementId)
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();

        parent::__construct('');

        $this->dependencyElementId = $dependencyElementId;

        $this->tpl->addJavaScript('Services/Mail/js/ilMailComposeFunctions.js');
    }

    /**
     * @return string
     */
    public function getRerenderUrl()
    {
        return $this->rerenderUrl;
    }

    /**
     * @return string
     */
    public function getRerenderTriggerElementName()
    {
        return $this->rerenderTriggerElementName;
    }

    /**
     * @param string $elementId
     * @param string $url
     */
    public function supportsRerenderSignal($elementId, $url)
    {
        $this->rerenderTriggerElementName = $elementId;
        $this->rerenderUrl = $url;
    }

    /**
     * @return string
     */
    public function getAdviseText()
    {
        return $this->adviseText;
    }

    /**
     * @param string $adviseText
     */
    public function setAdviseText($adviseText)
    {
        $this->adviseText = $adviseText;
    }

    /**
     * @return string
     */
    public function getInstructionText()
    {
        return $this->instructionText;
    }

    /**
     * @param string $instructionText
     */
    public function setInstructionText($instructionText)
    {
        $this->instructionText = $instructionText;
    }

    /**
     * @param string $placeholder
     * @param string $title
     */
    public function addPlaceholder($placeholder, $title)
    {
        $this->placeholders[$placeholder]['placeholder'] = $placeholder;
        $this->placeholders[$placeholder]['title'] = $title;
    }

    /**
     * @param $a_tpl
     */
    public function insert($a_tpl)
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    /**
     * @param bool $ajax
     * @return string|void
     */
    public function render($ajax = false)
    {
        $subtpl = new ilTemplate("tpl.mail_manual_placeholders.html", true, true, "Services/Mail");
        $subtpl->setVariable('TXT_USE_PLACEHOLDERS', $this->lng->txt('mail_nacc_use_placeholder'));
        if ($this->getAdviseText()) {
            $subtpl->setVariable('TXT_PLACEHOLDERS_ADVISE', $this->getAdviseText());
        }

        if (count($this->placeholders) > 0) {
            foreach ($this->placeholders as $placeholder) {
                $subtpl->setCurrentBlock('man_placeholder');
                $subtpl->setVariable('DEPENDENCY_ELM_ID', $this->dependencyElementId);
                $subtpl->setVariable('MANUAL_PLACEHOLDER', $placeholder['placeholder']);
                $subtpl->setVariable('TXT_MANUAL_PLACEHOLDER', $placeholder['title']);
                $subtpl->parseCurrentBlock();
            }
        }

        if ($this->getRerenderTriggerElementName() && $this->getRerenderUrl()) {
            $subtpl->setVariable('RERENDER_URL', $this->getRerenderUrl());
            $subtpl->setVariable('RERENDER_TRIGGER_ELM_NAME', $this->getRerenderTriggerElementName());
        }

        if ($ajax) {
            echo $subtpl->get();
            exit();
        }

        return $subtpl->get();
    }

    /**
     * Set value by array
     *
     * @param	array	$a_values	value array
     */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }
    /**
     * Set Value.
     *
     * @param	string	$a_value	Value
     */
    public function setValue($a_value)
    {
        if ($this->getMulti() && is_array($a_value)) {
            $this->setMultiValues($a_value);
            $a_value = array_shift($a_value);
        }
        $this->value = $a_value;
    }
    
    public function checkInput()
    {
        return true;
    }
}
