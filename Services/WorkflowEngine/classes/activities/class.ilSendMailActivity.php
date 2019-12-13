<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilActivity.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilNode.php';

/**
 * Class ilSendMailActivity
 *
 * This activity setup and send or read an email.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilSendMailActivity implements ilActivity, ilWorkflowEngineElement
{
    /** @var ilWorkflowEngineElement $context Holds a reference to the parent object */
    private $context;

    /** @var string ID of the message to be sent. */
    private $message_name;

    /** @var string $name */
    protected $name;

    /** @var array $parameters Holds an array with params to be passed as second argument to the method. */
    private $parameters;

    /** @var array $outputs Holds a list of valid output element IDs passed as third argument to the method. */
    private $outputs;

    /**
     * Default constructor.
     *
     * @param ilNode $a_context
     */
    public function __construct(ilNode $a_context)
    {
        $this->context = $a_context;
    }

    /**
     * Executes this action according to its settings.
     *
     * @todo Use exceptions / internal logging.
     *
     * @return void
     */
    public function execute()
    {
        /** @var ilBaseWorkflow $workflow */
        $workflow = $this->getContext()->getContext();
        $definitions = $workflow->getInstanceVars();

        $recipient = '';
        $subject = '';
        foreach ($this->parameters as $parameter) {
            foreach ($definitions as $definition) {
                if ($definition['id'] = $parameter) {
                    switch (strtolower($definition['role'])) {
                        case 'emailaddress':
                            $recipient = $definition['value'];
                            break;
                        case 'subject':
                            $subject = $definition['value'];
                            break;
                    }
                }
            }
        }

        $mail_data = $this->context->getContext()->getMessageDefinition($this->message_name);
        $mail_text = $this->decodeMessageText($mail_data['content']);
        $mail_text = $this->processPlaceholders($mail_text);

        require_once './Services/WorkflowEngine/classes/activities/class.ilWorkflowEngineMailNotification.php';
        $mail = new ilWorkflowEngineMailNotification();
        $mail->setSubjectText($subject);
        $mail->setBodyText($mail_text);

        $mail->send($recipient);
    }

    /**
     * Returns a reference to the parent node.
     *
     * @return ilNode Reference to the parent node.
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMessageName()
    {
        return $this->message_name;
    }

    /**
     * @param string $message_name
     */
    public function setMessageName($message_name)
    {
        $this->message_name = $message_name;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getOutputs()
    {
        return $this->outputs;
    }

    /**
     * @param array $outputs
     */
    public function setOutputs($outputs)
    {
        $this->outputs = $outputs;
    }

    public function decodeMessageText($message_text)
    {
        return base64_decode($message_text);
    }

    public function processPlaceholders($message_text)
    {
        $matches = array();
        preg_match_all('/\[(.*?)\]/', $message_text, $matches, PREG_PATTERN_ORDER);

        foreach ($matches[0] as $match) {
            $placeholder = substr($match, 1, strlen($match)-2);

            $handled = false;
            if (strtolower(substr($placeholder, 0, strlen('EVENTLINK'))) == 'eventlink') {
                $handled = true;
                $content = $this->getEventLink($match);
            }

            if (!$handled) {
                $content = $this->context->getContext()->getInstanceVarById($placeholder);
            }

            if (strlen($content)) {
                $message_text = str_replace($match, $content, $message_text);
            }
        }
        return $message_text;
    }

    public function getEventLink($eventlink_string)
    {
        $type = substr($eventlink_string, 1, strpos($eventlink_string, ' ')-1);
        $params = substr($eventlink_string, strpos($eventlink_string, ' ')+1, -1);

        $matches = array();
        preg_match_all('/\{{(.*?)\}}/', $params, $matches, PREG_PATTERN_ORDER);
        foreach ($matches[1] as $match) {
            if ($match == 'THIS:WFID') {
                $params = str_replace('{{' . $match . '}}', $this->getContext()->getContext()->getDbId(), $params);
            }
        }
        $pieces = explode(':', $params);
        /** @var ilias $ilias */
        global $DIC;
        $ilias = $DIC['ilias'];

        $address = ilUtil::_getHttpPath() . '/goto.php?target=wfe_WF'
            . $pieces[0] . 'EVT' . $pieces[1] . '&client_id=' . $ilias->getClientId();

        return $address;
    }
}
