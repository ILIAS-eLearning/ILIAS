<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Button/classes/class.ilButtonBase.php';

/**
 * Description: <button> (https://developer.mozilla.org/en-US/docs/Web/HTML/Element/button) by Mozilla Contributors is licensed under CC-BY-SA 2.5.
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesUIComponent
 */
class ilButton extends ilButtonBase
{
    /**
     * The button has no default behavior. It can have client-side scripts associated with the element's events,
     * which are triggered when the events occur.
     * @var string
     */
    const BUTTON_TYPE_BUTTON = 'button';

    /**
     * The button submits the form data to the server.
     * This is the default if the attribute is not specified, or if the attribute is dynamically changed to an empty or invalid value.
     * @var string
     */
    const BUTTON_TYPE_SUBMIT = 'submit';

    /**
     * The button resets all the controls to their initial values.
     * @var string
     */
    const BUTTON_TYPE_RESET = 'reset';

    /**
     * The default value if the attribute is not specified.
     * @var string
     */
    const FORM_ENC_TYPE_APPLICATION = 'application/x-www-form-urlencoded';

    /**
     * Use this value if you are using an <input> element with the type attribute set to file.
     * @var string
     */
    const FORM_ENC_TYPE_MULTI_PART = 'multipart/form-data';

    /**
     * @var string
     */
    const FORM_ENC_TYPE_PLAIN = 'text/plain';

    /**
     * @var string
     */
    const FORM_METHOD_POST = 'POST';

    /**
     * @var string
     */
    const FORM_METHOD_GET = 'GET';

    /**
     * Load the response into the same browsing context as the current one.
     * This value is the default if the attribute is not specified.
     * @var string
     */
    const FORM_TARGET_SELF = '_self';

    /**
     * Load the response into a new unnamed browsing context.
     * @var string
     */
    const FORM_TARGET_BLANK = '_blank';

    /**
     * Load the response into the parent browsing context of the current one.
     * If there is no parent, this option behaves the same way as self::FORM_TARGET_SELF.
     * @var string
     */
    const FORM_TARGET_PARENT = '_parent';

    /**
     * Load the response into the top-level browsing context (that is, the browsing context that is
     * an ancestor of the current one, and has no parent).
     * If there is no parent, this option behaves the same way as self::FORM_TARGET_SELF.
     * @var string
     */
    const FORM_TARGET_TOP = '_top';

    /**
     * The type of the button.
     * @var string
     */
    protected $button_type = self::BUTTON_TYPE_SUBMIT;

    /**
     * @var string|null
     */
    protected $name = null;

    /**
     * @var string|null
     */
    protected $value = null;

    /**
     * @var string|null
     */
    protected $form = null;

    /**
     * @var string|null
     */
    protected $form_action = null;

    /**
     * @var string|null
     */
    protected $form_enc_type = null;

    /**
     * @var string|null
     */
    protected $form_method = null;

    /**
     * @var string|null
     */
    protected $form_target = null;

    /**
     * @var bool|null
     */
    protected $form_novalidate = null;

    /**
     * @return self
     */
    public static function getInstance()
    {
        return new self(self::TYPE_BUTTON);
    }

    /**
     * @return array()
     */
    public static function getValidFormTargets()
    {
        return array(
            self::FORM_TARGET_BLANK,
            self::FORM_TARGET_PARENT,
            self::FORM_TARGET_SELF,
            self::FORM_TARGET_TOP
        );
    }

    /**
     * @return array()
     */
    public static function getValidFormMethods()
    {
        return array(
            self::FORM_METHOD_POST,
            self::FORM_METHOD_GET
        );
    }

    /**
     * @return array()
     */
    public static function getValidFormEncTypes()
    {
        return array(
            self::FORM_ENC_TYPE_APPLICATION,
            self::FORM_ENC_TYPE_MULTI_PART,
            self::FORM_ENC_TYPE_PLAIN
        );
    }

    /**
     * @return array()
     */
    public static function getValidButtonTypes()
    {
        return array(
            self::BUTTON_TYPE_SUBMIT,
            self::BUTTON_TYPE_BUTTON,
            self::BUTTON_TYPE_RESET
        );
    }

    /**
     * @return boolean
     */
    public function isFormNovalidate()
    {
        return $this->form_novalidate;
    }

    /**
     * If the button is a submit button, this Boolean attribute specifies that the form is not to be validated when it is submitted.
     * If this attribute is specified, it overrides the novalidate attribute of the button's form owner.
     * @param boolean $form_novalidate
     * @throws InvalidArgumentException
     * @return self
     */
    public function setFormNovalidate($form_novalidate)
    {
        if (!is_bool($form_novalidate)) {
            throw new InvalidArgumentException(
                sprintf("Please pass a value of type 'boolean' to specify whether the form is not to be validated when it is submitted")
            );
        }

        $this->form_novalidate = $form_novalidate;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormTarget()
    {
        return $this->form_target;
    }

    /**
     * If the button is a submit button, this attribute is a name or keyword indicating where to display the response that
     * is received after submitting the form. This is a name of, or keyword for, a browsing context
     * (for example, tab, window, or inline frame). If this attribute is specified, it overrides the target attribute of
     * the button's form owner.
     * @param string $form_target
     * @throws InvalidArgumentException
     * @return self
     */
    public function setFormTarget($form_target)
    {
        if (!in_array($form_target, self::getValidFormTargets())) {
            throw new InvalidArgumentException(
                sprintf(
                    "Invalid form target passed, must be one of these: %s",
                    implode(', ', self::getValidFormTargets())
                )
            );
        }

        $this->form_target = $form_target;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormMethod()
    {
        return $this->form_method;
    }

    /**
     * If the button is a submit button, this attribute specifies the HTTP method that the browser uses to submit the form.
     * @param string $form_method
     * @throws InvalidArgumentException
     * @return self
     */
    public function setFormMethod($form_method)
    {
        if (!in_array($form_method, self::getValidFormMethods())) {
            throw new InvalidArgumentException(
                sprintf(
                    "Invalid form method passed, must be one of these: %s",
                    implode(', ', self::getValidFormMethods())
                )
            );
        }

        $this->form_method = $form_method;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormEncType()
    {
        return $this->form_enc_type;
    }

    /**
     * If this attribute is specified, it overrides the enctype attribute of the button's form owner.
     * @param string $form_enc_type
     * @throws InvalidArgumentException
     * @return self
     */
    public function setFormEncType($form_enc_type)
    {
        if (!in_array($form_enc_type, self::getValidFormEncTypes())) {
            throw new InvalidArgumentException(
                sprintf(
                    "Invalid form enc type passed, must be one of these: %s",
                    implode(', ', self::getValidFormEncTypes())
                )
            );
        }

        $this->form_enc_type = $form_enc_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->form_action;
    }

    /**
     * The URI of a program that processes the information submitted by the button.
     * If specified, it overrides the action attribute of the button's form owner.
     * @param string $form_action
     * @throws InvalidArgumentException
     * @return self
     */
    public function setFormAction($form_action)
    {
        if (!is_string($form_action)) {
            throw new InvalidArgumentException(
                sprintf("The form action must be of type 'string'")
            );
        }

        $this->form_action = $form_action;
        return $this;
    }

    /**
     * @return string
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * The form element that the button is associated with (its form owner).
     * The value of the attribute must be the id attribute of a <form> element in the same document.
     * If this attribute is not specified, the <button> element must be a descendant of a form element.
     * This attribute enables you to place <button> elements anywhere within a document,
     * not just as descendants of their <form> elements.
     * @param string
     * @throws InvalidArgumentException
     * @return self
     */
    public function setForm($form)
    {
        if (!is_string($form)) {
            throw new InvalidArgumentException(
                sprintf("The form id must be of type 'string'")
            );
        }

        $this->form = $form;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The initial value of the button.
     * @param string $value
     * @throws InvalidArgumentException
     * @return self
     */
    public function setValue($value)
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                sprintf("The initial value of the button must be of type 'string'")
            );
        }

        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The name of the button, which is submitted with the form data.
     * @param string $name
     * @param bool   $is_command if true, a cmd[] is wrapped around the passed name
     * @throws InvalidArgumentException
     * @return self
     */
    public function setName($name, $is_command = true)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                sprintf("The name of the button must be of type 'string'")
            );
        }

        $this->name = $is_command ? 'cmd[' . $name . ']' : $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getButtonType()
    {
        return $this->button_type;
    }

    /**
     * @param string $button_type
     * @throws InvalidArgumentException
     * @return self
     */
    public function setButtonType($button_type)
    {
        if (!in_array($button_type, self::getValidButtonTypes())) {
            throw new InvalidArgumentException(
                sprintf(
                    "Invalid button type passed, must be one of these: %s",
                    implode(', ', self::getValidButtonTypes())
                )
            );
        }

        $this->button_type = $button_type;
        return $this;
    }

    /**
     * Render HTML
     * @return string
     */
    public function render()
    {
        $this->prepareRender();

        $attr                   = array();
        $attr['type']           = $this->getButtonType();
        $attr['name']           = $this->getName();
        $attr['value']          = $this->getValue();
        $attr['form']           = $this->getForm();
        $attr['formaction']     = $this->getFormAction();
        $attr['formmethod']     = $this->getFormMethod();
        $attr['formenctype']    = $this->getFormEncType();
        $attr['formtarget']     = $this->getFormTarget();
        $attr['formnovalidate'] = $this->isFormNovalidate() ? var_export($this->isFormNovalidate(), 1) : null;

        if (self::FORM_TARGET_BLANK === $this->getFormTarget()) {
            $attr['rel'] = 'noopener';
        }

        return '<button' . $this->renderAttributes(array_filter($attr)) . '>' . $this->getCaption() . '</button>';
    }
}
