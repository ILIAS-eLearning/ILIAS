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

use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\ilTestLegacyFormsHelper;

/**
* This class represents a key value pair wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilErrorTextWizardInputGUI extends ilTextInputGUI
{
    protected $values = [];
    protected $key_size = 20;
    protected $value_size = 20;
    protected $key_maxlength = 255;
    protected $value_maxlength = 150;
    protected $key_name = "";
    protected $value_name = "";

    protected ilTestLegacyFormsHelper $forms_helper;

    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->forms_helper = new ilTestLegacyFormsHelper();
    }

    public function setValue($a_value): void
    {
        $keys = $this->forms_helper->transformArray($a_value, 'key', $this->refinery->kindlyTo()->string());
        $points = $this->forms_helper->transformPoints($a_value);

        $this->values = [];
        foreach ($keys as $index => $key) {
            $this->values[] = new assAnswerErrorText(
                $key,
                $a_value['value'][$index] ?? '',
                $points[$index] ?? 0.0
            );
        }
    }

    /**
    * Set key size.
    *
    * @param	integer	$a_size	Key size
    */
    public function setKeySize($a_size): void
    {
        $this->key_size = $a_size;
    }

    /**
    * Get key size.
    *
    * @return	integer	Key size
    */
    public function getKeySize(): int
    {
        return $this->key_size;
    }

    /**
    * Set value size.
    *
    * @param	integer	$a_size	value size
    */
    public function setValueSize($a_size): void
    {
        $this->value_size = $a_size;
    }

    /**
    * Get value size.
    *
    * @return	integer	value size
    */
    public function getValueSize(): int
    {
        return $this->value_size;
    }

    /**
    * Set key maxlength.
    *
    * @param	integer	$a_size	Key maxlength
    */
    public function setKeyMaxlength($a_maxlength): void
    {
        $this->key_maxlength = $a_maxlength;
    }

    /**
    * Get key maxlength.
    *
    * @return	integer	Key maxlength
    */
    public function getKeyMaxlength(): int
    {
        return $this->key_maxlength;
    }

    /**
    * Set value maxlength.
    *
    * @param	integer	$a_size	value maxlength
    */
    public function setValueMaxlength($a_maxlength): void
    {
        $this->value_maxlength = $a_maxlength;
    }

    /**
    * Get value maxlength.
    *
    * @return	integer	value maxlength
    */
    public function getValueMaxlength(): int
    {
        return $this->value_maxlength;
    }

    /**
    * Set value name.
    *
    * @param	string	$a_name	value name
    */
    public function setValueName($a_name): void
    {
        $this->value_name = $a_name;
    }

    /**
    * Get value name.
    *
    * @return	string	value name
    */
    public function getValueName(): string
    {
        return $this->value_name;
    }

    /**
    * Set key name.
    *
    * @param	string	$a_name	value name
    */
    public function setKeyName($a_name): void
    {
        $this->key_name = $a_name;
    }

    /**
    * Get key name.
    *
    * @return	string	value name
    */
    public function getKeyName(): string
    {
        return $this->key_name;
    }

    /**
    * Set Values
    *
    * @param	array	$a_value	Value
    */
    public function setValues($a_values): void
    {
        $this->values = $a_values;
    }

    /**
    * Get Values
    *
    * @return	array	Values
    */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    * @return	boolean		Input ok, true/false
    */
    public function checkInput(): bool
    {
        $kindlyTo = $this->refinery->kindlyTo();
        $data = $this->raw($this->getPostVar());

        if (!is_array($data)) {
            $this->setAlert($this->lng->txt('errortext_info'));
            return false;
        }

        // check points
        $points = $this->forms_helper->checkPointsInput($data, $this->getRequired());
        if (!is_array($points)) {
            $this->setAlert($this->lng->txt($points));
            return false;
        }
        foreach ($points as $point) {
            if ($point <= 0) {
                $this->setAlert($this->lng->txt('positive_numbers_required'));
                return false;
            }
        }

        // check answers
        $keys = $this->forms_helper->transformArray($data, 'key', $kindlyTo->string());
        $values = $this->forms_helper->transformArray($data, 'value', $kindlyTo->string());
        if (empty($keys) || empty($values)) {
            $this->setAlert($this->lng->txt('msg_input_is_required'));
            return false;
        }
        foreach ([$keys, $values] as $array) {
            foreach ($array as $item) {
                if ($item === '' && $this->getRequired()) {
                    $this->setAlert($this->lng->txt('msg_input_is_required'));
                    return false;
                }
            }
        }

        return $this->checkSubItemsInput();
    }

    /**
    * Insert property html
    * @return	void	Size
    */
    public function insert(ilTemplate $a_tpl): void
    {
        global $DIC;
        $lng = $DIC['lng'];

        $tpl = new ilTemplate("tpl.prop_errortextwizardinput.html", true, true, "components/ILIAS/TestQuestionPool");
        $i = 0;
        foreach ($this->values as $value) {
            if (is_object($value)) {
                if (strlen($value->getTextWrong())) {
                    $tpl->setCurrentBlock("prop_key_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($value->getTextWrong()));
                    $tpl->parseCurrentBlock();
                }
                if (strlen($value->getTextCorrect())) {
                    $tpl->setCurrentBlock("prop_value_propval");
                    $tpl->setVariable(
                        "PROPERTY_VALUE",
                        ilLegacyFormElementsUtil::prepareFormOutput($value->getTextCorrect())
                    );
                    $tpl->parseCurrentBlock();
                }
                if (strlen($value->getPoints())) {
                    $tpl->setCurrentBlock("prop_points_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($value->getPoints()));
                    $tpl->parseCurrentBlock();
                }
            }

            $tpl->setCurrentBlock("row");
            $class = ($i % 2 == 0) ? "even" : "odd";
            if ($i == 0) {
                $class .= " first";
            }
            if ($i == count($this->values) - 1) {
                $class .= " last";
            }
            $tpl->setVariable("ROW_CLASS", $class);
            $tpl->setVariable("ROW_NUMBER", $i);

            $tpl->setVariable("KEY_SIZE", $this->getKeySize());
            $tpl->setVariable("KEY_ID", $this->getPostVar() . "[key][$i]");
            $tpl->setVariable("KEY_MAXLENGTH", $this->getKeyMaxlength());

            $tpl->setVariable("VALUE_SIZE", $this->getValueSize());
            $tpl->setVariable("VALUE_ID", $this->getPostVar() . "[value][$i]");
            $tpl->setVariable("VALUE_MAXLENGTH", $this->getValueMaxlength());

            $tpl->setVariable("POST_VAR", $this->getPostVar());

            $tpl->parseCurrentBlock();

            $i++;
        }
        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("KEY_TEXT", $this->getKeyName());
        $tpl->setVariable("VALUE_TEXT", $this->getValueName());
        $tpl->setVariable("POINTS_TEXT", $lng->txt('points'));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
