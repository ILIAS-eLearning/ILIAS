<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Symbol\Glyph\Factory as GlyphFactory;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 */
abstract class ilMultipleTextsInputGUI extends ilIdentifiedMultiValuesInputGUI
{
    /**
     * @var bool
     */
    protected $editElementOccuranceEnabled = false;

    /**
     * @var bool
     */
    protected $editElementOrderEnabled = false;

    protected GlyphFactory $glyph_factory;
    protected Renderer $renderer;
    protected ilGlobalTemplateInterface $tpl;

    /**
     * Constructor
     *
     * @param	string	$a_title	Title
     * @param	string	$a_postvar	Post Variable
     */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);

        global $DIC;
        $this->lng = $DIC['lng'];
        $this->glyph_factory = $DIC->ui()->factory()->symbol()->glyph();
        $this->renderer = $DIC['ui.renderer'];
        $this->tpl = $DIC['tpl'];

        $this->validationRegexp = "";
    }

    /**
     * @return	boolean $editElementOccuranceEnabled
     */
    public function isEditElementOccuranceEnabled(): bool
    {
        return $this->editElementOccuranceEnabled;
    }

    /**
     * @param	boolean	$editElementOccuranceEnabled
     */
    public function setEditElementOccuranceEnabled($editElementOccuranceEnabled): void
    {
        $this->editElementOccuranceEnabled = $editElementOccuranceEnabled;
    }

    /**
     * @return boolean
     */
    public function isEditElementOrderEnabled(): bool
    {
        return $this->editElementOrderEnabled;
    }

    /**
     * @param boolean $editElementOrderEnabled
     */
    public function setEditElementOrderEnabled($editElementOrderEnabled): void
    {
        $this->editElementOrderEnabled = $editElementOrderEnabled;
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     *fetchImageTitle
     * @return	boolean		Input ok, true/false
     */
    public function onCheckInput(): bool
    {
        $lng = $this->lng;

        $submittedElements = $this->getInput();

        if ($submittedElements === [] && $this->getRequired()) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }

        foreach ($submittedElements as $submittedValue) {
            $submittedContentText = $this->fetchContentTextFromValue($submittedValue);

            if ($this->getRequired() && trim((string) $submittedContentText) === "") {
                $this->setAlert($lng->txt('msg_input_is_required'));
                return false;
            }

            if ($this->getValidationRegexp() !== '') {
                if (!preg_match($this->getValidationRegexp(), (string) $submittedContentText)) {
                    $this->setAlert($lng->txt('msg_wrong_format'));
                    return false;
                }
            }
        }

        return $this->checkSubItemsInput();
    }

    /**
     * @param string $mode
     * @return string
     */
    public function render(string $a_mode = ""): string
    {
        $tpl = new ilTemplate("tpl.prop_multi_text_inp.html", true, true, "components/ILIAS/TestQuestionPool");
        $i = 0;
        foreach ($this->getIdentifiedMultiValues() as $identifier => $value) {
            if ($value !== null) {
                $tpl->setCurrentBlock("prop_text_propval");
                $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($value));
                $tpl->parseCurrentBlock();
            }
            if ($this->isEditElementOrderEnabled()) {
                $tpl->setCurrentBlock("move");
                $tpl->setVariable("ID_UP", $this->getMultiValuePosIndexedSubFieldId($identifier, 'up', $i));
                $tpl->setVariable("ID_DOWN", $this->getMultiValuePosIndexedSubFieldId($identifier, 'down', $i));
                $tpl->setVariable("ID", $this->getMultiValuePosIndexedFieldId($identifier, $i));
                $tpl->setVariable("UP_BUTTON", $this->renderer->render(
                    $this->glyph_factory->up()->withAction('#')
                ));
                $tpl->setVariable("DOWN_BUTTON", $this->renderer->render(
                    $this->glyph_factory->down()->withAction('#')
                ));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("row");
            $tpl->setVariable("POST_VAR", $this->getMultiValuePostVarPosIndexed($identifier, $i));
            $tpl->setVariable("ID", $this->getMultiValuePosIndexedFieldId($identifier, $i));
            $tpl->setVariable("SIZE", $this->getSize());
            $tpl->setVariable("MAXLENGTH", $this->getMaxLength());

            if ($this->getDisabled()) {
                $tpl->setVariable(
                    "DISABLED",
                    " disabled=\"disabled\""
                );
            } elseif ($this->isEditElementOccuranceEnabled()) {
                $tpl->setVariable("ID_ADD", $this->getMultiValuePosIndexedSubFieldId($identifier, 'add', $i));
                $tpl->setVariable("ID_REMOVE", $this->getMultiValuePosIndexedSubFieldId($identifier, 'remove', $i));
                $tpl->setVariable("ADD_BUTTON", $this->renderer->render(
                    $this->glyph_factory->add()->withAction('#')
                ));
                $tpl->setVariable("REMOVE_BUTTON", $this->renderer->render(
                    $this->glyph_factory->remove()->withAction('#')
                ));
            }

            $tpl->parseCurrentBlock();
            $i++;
        }
        $tpl->setVariable("ELEMENT_ID", $this->getFieldId());

        if (!$this->getDisabled()) {
            $config = '{'
                . '"fieldContainerSelector": ".ilWzdContainerText", '
                . '"reindexingRequiredElementsSelectors": ["input:text", "button"], '
                . '"handleRowCleanUpCallback": function(rowElem) {$(rowElem).find("input:text").val("")}'
                . '}';
            $this->tpl->addJavascript("asserts/js/answerwizardinput.js");
            $this->tpl->addJavascript("asserts/js/identifiedwizardinput.js");
            $this->tpl->addOnLoadCode("$.extend({}, AnswerWizardInput, IdentifiedWizardInput).init({$config});");
        }

        return $tpl->get();
    }

    /**
     * @param $value
     * @return bool
     */
    protected function valueHasContentText($value): bool
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return false;
        }

        return (bool) strlen($value);
    }

    /**
     * @param mixed $value
     * @return string|ilAssOrderingElement|null
     */
    protected function fetchContentTextFromValue($value)
    {
        if ($this->valueHasContentText($value)) {
            return $value;
        }

        return null;
    }
}
