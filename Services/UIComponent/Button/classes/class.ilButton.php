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

/**
 * Description: <button> (https://developer.mozilla.org/en-US/docs/Web/HTML/Element/button) by Mozilla Contributors is licensed under CC-BY-SA 2.5.
 * @author  Michael Jansen <mjansen@databay.de>
 * @deprecated use KS Buttons instead
 */
class ilButton extends ilButtonBase
{
    /**
     * The button has no default behavior. It can have client-side scripts associated with the element's events,
     * which are triggered when the events occur.
     */
    public const BUTTON_TYPE_BUTTON = 'button';

    /**
     * The button submits the form data to the server.
     * This is the default if the attribute is not specified, or if the attribute is dynamically changed to an empty or invalid value.
     */
    public const BUTTON_TYPE_SUBMIT = 'submit';

    /**
     * The button resets all the controls to their initial values.
     */
    public const BUTTON_TYPE_RESET = 'reset';

    /**
     * The default value if the attribute is not specified.
     */
    public const FORM_ENC_TYPE_APPLICATION = 'application/x-www-form-urlencoded';

    /**
     * Use this value if you are using an <input> element with the type attribute set to file.
     */
    public const FORM_ENC_TYPE_MULTI_PART = 'multipart/form-data';

    public const FORM_ENC_TYPE_PLAIN = 'text/plain';
    public const FORM_METHOD_POST = 'POST';
    public const FORM_METHOD_GET = 'GET';

    /**
     * Load the response into the same browsing context as the current one.
     * This value is the default if the attribute is not specified.
     */
    public const FORM_TARGET_SELF = '_self';

    /**
     * Load the response into a new unnamed browsing context.
     */
    public const FORM_TARGET_BLANK = '_blank';

    /**
     * Load the response into the parent browsing context of the current one.
     * If there is no parent, this option behaves the same way as self::FORM_TARGET_SELF.
     */
    public const FORM_TARGET_PARENT = '_parent';

    /**
     * Load the response into the top-level browsing context (that is, the browsing context that is
     * an ancestor of the current one, and has no parent).
     * If there is no parent, this option behaves the same way as self::FORM_TARGET_SELF.
     */
    public const FORM_TARGET_TOP = '_top';

    protected string $button_type = self::BUTTON_TYPE_SUBMIT;
    protected ?string $name = null;
    protected ?string $value = null;
    protected ?string $form = null;
    protected ?string $form_action = null;
    protected ?string $form_enc_type = null;
    protected ?string $form_method = null;
    protected ?string $form_target = null;
    protected bool $form_novalidate = false;

    public static function getInstance() : self
    {
        return new self(self::TYPE_BUTTON);
    }

    public static function getValidFormTargets() : array
    {
        return array(
            self::FORM_TARGET_BLANK,
            self::FORM_TARGET_PARENT,
            self::FORM_TARGET_SELF,
            self::FORM_TARGET_TOP
        );
    }

    public static function getValidFormMethods() : array
    {
        return array(
            self::FORM_METHOD_POST,
            self::FORM_METHOD_GET
        );
    }

    public static function getValidFormEncTypes() : array
    {
        return array(
            self::FORM_ENC_TYPE_APPLICATION,
            self::FORM_ENC_TYPE_MULTI_PART,
            self::FORM_ENC_TYPE_PLAIN
        );
    }

    public static function getValidButtonTypes() : array
    {
        return array(
            self::BUTTON_TYPE_SUBMIT,
            self::BUTTON_TYPE_BUTTON,
            self::BUTTON_TYPE_RESET
        );
    }

    public function isFormNovalidate() : ?bool
    {
        return $this->form_novalidate;
    }

    /**
     * If the button is a submit button, this Boolean attribute specifies that the form is not to be validated when it is submitted.
     * If this attribute is specified, it overrides the novalidate attribute of the button's form owner.
     * @throws InvalidArgumentException
     */
    public function setFormNovalidate(bool $form_novalidate) : self
    {
        if (!is_bool($form_novalidate)) {
            throw new InvalidArgumentException(
                "Please pass a value of type 'boolean' to specify whether the form is not to be validated when it is submitted"
            );
        }

        $this->form_novalidate = $form_novalidate;
        return $this;
    }

    public function getFormTarget() : ?string
    {
        return $this->form_target;
    }

    /**
     * If the button is a submit button, this attribute is a name or keyword indicating where to display the response that
     * is received after submitting the form. This is a name of, or keyword for, a browsing context
     * (for example, tab, window, or inline frame). If this attribute is specified, it overrides the target attribute of
     * the button's form owner.
     * @throws InvalidArgumentException
     */
    public function setFormTarget(string $form_target) : self
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

    public function getFormMethod() : ?string
    {
        return $this->form_method;
    }

    /**
     * If the button is a submit button, this attribute specifies the HTTP method that the browser uses to submit the form.
     * @throws InvalidArgumentException
     */
    public function setFormMethod(string $form_method) : self
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

    public function getFormEncType() : ?string
    {
        return $this->form_enc_type;
    }

    /**
     * If this attribute is specified, it overrides the enctype attribute of the button's form owner.
     * @throws InvalidArgumentException
     */
    public function setFormEncType(string $form_enc_type) : self
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

    public function getFormAction() : ?string
    {
        return $this->form_action;
    }

    /**
     * The URI of a program that processes the information submitted by the button.
     * If specified, it overrides the action attribute of the button's form owner.
     * @throws InvalidArgumentException
     */
    public function setFormAction(string $form_action) : self
    {
        if (!is_string($form_action)) {
            throw new InvalidArgumentException(
                "The form action must be of type 'string'"
            );
        }

        $this->form_action = $form_action;
        return $this;
    }

    public function getForm() : string
    {
        return $this->form;
    }

    /**
     * The form element that the button is associated with (its form owner).
     * The value of the attribute must be the id attribute of a <form> element in the same document.
     * If this attribute is not specified, the <button> element must be a descendant of a form element.
     * This attribute enables you to place <button> elements anywhere within a document,
     * not just as descendants of their <form> elements.
     * @throws InvalidArgumentException
     */
    public function setForm(string $form) : self
    {
        if (!is_string($form)) {
            throw new InvalidArgumentException(
                "The form id must be of type 'string'"
            );
        }

        $this->form = $form;
        return $this;
    }

    public function getValue() : ?string
    {
        return $this->value;
    }

    /**
     * The initial value of the button.
     * @throws InvalidArgumentException
     */
    public function setValue(string $value) : self
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                "The initial value of the button must be of type 'string'"
            );
        }

        $this->value = $value;
        return $this;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * The name of the button, which is submitted with the form data.
     * @param bool   $is_command if true, a cmd[] is wrapped around the passed name
     * @throws InvalidArgumentException
     */
    public function setName(string $name, bool $is_command = true) : self
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                "The name of the button must be of type 'string'"
            );
        }

        $this->name = $is_command ? 'cmd[' . $name . ']' : $name;
        return $this;
    }

    public function getButtonType() : string
    {
        return $this->button_type;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setButtonType(string $button_type) : self
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

    public function render() : string
    {
        $this->prepareRender();

        $attr = [];
        $attr['type'] = $this->getButtonType() ?? '';
        $attr['name'] = $this->getName() ?? '';
        $attr['value'] = $this->getValue() ?? '';
        $attr['form'] = $this->getForm() ?? '';
        $attr['formaction'] = $this->getFormAction() ?? '';
        $attr['formmethod'] = $this->getFormMethod() ?? '';
        $attr['formenctype'] = $this->getFormEncType() ?? '';
        $attr['formtarget'] = $this->getFormTarget() ?? '';
        $attr['formnovalidate'] = $this->isFormNovalidate() ? var_export($this->isFormNovalidate(), 1) : null;

        if (self::FORM_TARGET_BLANK === $this->getFormTarget()) {
            $attr['rel'] = 'noopener';
        }

        return '<button' . $this->renderAttributes(array_filter($attr)) . '>' . $this->getCaption() . '</button>';
    }
}
