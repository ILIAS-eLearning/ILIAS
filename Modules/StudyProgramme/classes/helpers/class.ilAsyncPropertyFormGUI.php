<?php

declare(strict_types=1);

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

use ILIAS\HTTP\Wrapper\RequestWrapper;

/**
 * Class ilAsyncPropertyFormGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilAsyncPropertyFormGUI extends ilPropertyFormGUI
{
    /**
     * @var string Path to the async-form js
     */
    protected static string $js_path = "./Modules/StudyProgramme/templates/js/";

    /**
     * @var string Default form name (used for jquery-selection)
     */
    protected static string $default_from_name = "async_form";

    /**
     * @var array Added js-onload codes
     */
    protected static array $js_on_load_added = array();

    /**
     * @var bool Indicates if the form has an error
     */
    protected bool $has_errors = false;

    /**
     * @var bool Indicates if form is async
     */
    protected bool $is_async = true;

    protected RequestWrapper $request_wrapper;

    public function __construct(RequestWrapper $request_wrapper, array $config = array(), bool $is_async = true)
    {
        parent::__construct();

        foreach ($config as $key => $value) {
            $setterMethod = "set" . ucfirst($key);
            if (method_exists($this, $setterMethod)) {
                $setterMethod($value);
            }
        }

        $this->request_wrapper = $request_wrapper;
        $this->setAsync($is_async);
        $this->setName(self::$default_from_name);
    }


    /**
     * Adds all needed js
     * By default is called by ilAsyncPropertyFormGUI::getHTML()
     */
    public static function addJavaScript(bool $add_form_loader = false, string $js_base_path = null): void
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        $js_path = $js_base_path ?? self::$js_path;

        $tpl->addJavaScript($js_path . 'ilAsyncPropertyFormGUI.js');

        $global_config =
            "$.ilAsyncPropertyForm.global_config.error_message_template = '" .
            self::getErrorMessageTemplate() .
            "'; $.ilAsyncPropertyForm.global_config.async_form_name = '" .
            self::$default_from_name . "';"
        ;

        self::addOnLoadCode('global_config', $global_config);

        if ($add_form_loader) {
            self::addOnLoadCode('form_loader', '$("body").ilAsyncPropertyForm();');
        }
    }

    /**
     * Saves the change input result into a property
     */
    public function checkInput(): bool
    {
        $result = parent::checkInput();
        $this->has_errors = $result;

        return $result;
    }

    /**
     * Return errors of the form as array
     *
     * @return array Array with field id and error message: array([]=>array('key'=>fieldId, 'message'=>error-message))
     */
    public function getErrors(): array
    {
        if (!$this->check_input_called) {
            $this->checkInput();
        }

        $errors = array();
        foreach ($this->getItems() as $item) {
            // We call method exists as there are items in the form (ilFormSectionHeaderGUI)
            // that do not have alerts. (#16956)
            if (method_exists($item, "getAlert") && $item->getAlert() !== "") {
                $errors[] = array('key' => $item->getFieldId(), 'message' => $item->getAlert());
            }
        }
        return $errors;
    }

    /**
     * Return if there were errors on the last checkInput call
     */
    public function hasErrors(): bool
    {
        return $this->has_errors;
    }

    /**
     * Returns the error-message template for the client-side validation
     */
    public static function getErrorMessageTemplate(): string
    {
        global $DIC;
        $lng = $DIC['lng'];

        $tpl = new ilTemplate("tpl.property_form.html", true, true, "Services/Form");

        $tpl->setCurrentBlock("alert");
        // TODO: DW -> refactor getImagePath
        $tpl->setVariable("IMG_ALERT", ilUtil::getImagePath("icon_alert.svg"));
        $tpl->setVariable("ALT_ALERT", $lng->txt("alert"));
        $tpl->setVariable("TXT_ALERT", "[TXT_ALERT]");
        $tpl->parseCurrentBlock();
        return trim($tpl->get("alert"));
    }

    /**
     * Copies form items, buttons and properties from another form
     */
    public function cloneForm(ilPropertyFormGUI $form_to_clone): ilAsyncPropertyFormGUI
    {
        if (count($this->getItems()) > 0) {
            throw new ilException("You cannot clone into a already filled form!");
        }

        $reflect = new ReflectionClass($this);
        $properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $this->{$property->getName()} = $property->getValue($form_to_clone);
        }

        foreach ($form_to_clone->getItems() as $item) {
            $this->addItem($item);
        }

        foreach ($form_to_clone->getCommandButtons() as $button) {
            $this->addCommandButton($button['cmd'], $button['text']);
        }

        return $this;
    }

    /**
     * Adds onload code to the template
     */
    protected static function addOnLoadCode(string $id, string $content): void
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        if (!isset(self::$js_on_load_added[$id])) {
            $tpl->addOnLoadCode($content);
            self::$js_on_load_added[$id] = $content;
        }
    }

    /**
     * Returns the rendered form content
     */
    public function getHTML(): string
    {
        self::addJavaScript($this->isAsync());

        return parent::getHTML();
    }

    /**
     * Checks if the form was submitted
     */
    public function isSubmitted(): bool
    {
        if ($this->request_wrapper->has("cmd")) {
            return true;
        }
        return false;
    }

    /**
     * Sets the form action
     * If the form is set to async, the cmdMode=asynch is added to the url
     */
    public function setFormAction(string $a_formaction): void
    {
        if ($this->isAsync()) {
            $a_formaction .= "&cmdMode=asynch";
        }

        $this->formaction = $a_formaction;
    }

    public function getJsPath(): ?string
    {
        return self::$js_path;
    }

    public function setJsPath(string $js_path): void
    {
        self::$js_path = $js_path;
    }

    public function getDefaultFormName(): string
    {
        return self::$default_from_name;
    }

    public function isAsync(): bool
    {
        return $this->is_async;
    }

    public function setAsync(bool $is_async): void
    {
        $this->is_async = $is_async;
    }

    /**
     * @param string $a_name
     */
    public function setName(string $a_name): void
    {
        self::$default_from_name = $a_name;

        parent::setName($a_name);
    }
}
