<?php
namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Interruptive extends Modal implements Component\Modal\Interruptive
{

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $action_button_label = 'delete';

    /**
     * @var string
     */
    protected $cancel_button_label = 'cancel';

    /**
     * @var string
     */
    protected $form_action;

    /**
     * @var Component\Modal\InterruptiveItem[]
     */
    protected $items = array();

    /**
     * @param string $title
     * @param string $message
     * @param string $form_action
     * @param SignalGeneratorInterface $signal_generator
     * @param string $action_button_label
     */
    public function __construct($title, $message, $form_action, SignalGeneratorInterface $signal_generator, $action_button_label = 'delete')
    {
        parent::__construct($signal_generator);
        $this->checkStringArg('title', $title);
        $this->checkStringArg('message', $message);
        $this->checkStringArg('form_action', $form_action);
        $this->checkStringArg('action_button_label', $action_button_label);
        $this->title = $title;
        $this->message = $message;
        $this->form_action = $form_action;
        $this->action_button_label = $action_button_label;
    }


    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @inheritdoc
     */
    public function withFormAction($form_action)
    {
        $this->checkStringArg('form_action', $form_action);
        $clone = clone $this;
        $clone->form_action = $form_action;
        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function withAffectedItems(array $items)
    {
        $types = array(Component\Modal\InterruptiveItem::class);
        $this->checkArgListElements('items', $items, $types);
        $clone = clone $this;
        $clone->items = $items;
        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function getActionButtonLabel()
    {
        return $this->action_button_label;
    }


    /**
     * @inheritdoc
     */
    public function getCancelButtonLabel()
    {
        return $this->cancel_button_label;
    }


    /**
     * @inheritdoc
     */
    public function getAffectedItems()
    {
        return $this->items;
    }


    /**
     * @inheritdoc
     */
    public function getFormAction()
    {
        return $this->form_action;
    }
}
