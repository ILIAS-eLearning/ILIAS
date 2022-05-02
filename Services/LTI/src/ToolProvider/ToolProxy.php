<?php

namespace ILIAS\LTI\ToolProvider;

use ILIAS\LTI\ToolProvider\DataConnector\DataConnector;

// use IMSGlobal\LTI\ToolProvider\MediaType;

/**
 * Class to represent an LTI Tool Proxy
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version  3.0.2
 * @license  GNU Lesser General Public License, version 3 (<http://www.gnu.org/licenses/lgpl.html>)
 */
class ToolProxy
{

/**
 * Local id of tool consumer.
 *
 * @var string $id
 */
    public ?string $id = null;

    /**
     * Tool Consumer for this tool proxy.
     *
     * @var ToolConsumer $consumer
     */
    private ?ToolConsumer $consumer = null;

    /**
     * Tool Consumer ID for this tool proxy.
     */
    private ?int $consumerId = null;

    /**
     * Consumer ID value.
     */
    private ?string $recordId = null;

    /**
     * Data connector object.
     *
     * @var DataConnector $dataConnector
     */
    private ?DataConnector $dataConnector = null;
    ///**
    // * Tool Proxy document.
    // *
    // * @var MediaType\ToolProxy $toolProxy
    // */
//    private $toolProxy = null;

    /**
     * Class constructor.
     * @param DataConnector $dataConnector Data connector
     * @param string|null   $id            Tool Proxy ID (optional, default is null)
     */
    public function __construct(DataConnector $dataConnector, ?string $id = null)
    {
        $this->initialize();
        $this->dataConnector = $dataConnector;
        if (!empty($id)) {
            $this->load($id);
        } else {
            $this->recordId = DataConnector::getRandomString(32);
        }
    }

    /**
     * Initialise the tool proxy.
     */
    public function initialize()
    {
        $this->id = null;
        $this->recordId = null;
//        $this->toolProxy = null;
        $this->created = null; // TODO PHP8 Review: Undefined Property
        $this->updated = null; // TODO PHP8 Review: Undefined Property
    }

    /**
     * Initialise the tool proxy.
     *
     * Pseudonym for initialize().
     */
    public function initialise()
    {
        $this->initialize();
    }

    /**
     * Get the tool proxy record ID.
     *
     * @return int Tool Proxy record ID value
     */
    public function getRecordId() : ?int
    {
        return $this->recordId; // TODO PHP8 Review: Check/Resolve Type-Mismatch
    }

    /**
     * Sets the tool proxy record ID.
     * @param int $recordId Tool Proxy record ID value
     */
    public function setRecordId(int $recordId)
    {
        $this->recordId = $recordId; // TODO PHP8 Review: Check/Resolve Type-Mismatch
    }

    /**
     * Get tool consumer.
     *
     * @return ToolConsumer Tool consumer object for this context.
     */
    public function getConsumer() : ToolConsumer
    {
        if (is_null($this->consumer)) {
            $this->consumer = ToolConsumer::fromRecordId($this->consumerId, $this->getDataConnector());
        }

        return $this->consumer;
    }

    /**
     * Set tool consumer ID.
     * @param int $consumerId Tool Consumer ID for this resource link.
     */
    public function setConsumerId(int $consumerId)
    {
        $this->consumer = null;
        $this->consumerId = $consumerId;
    }

    /**
     * Get the data connector.
     *
     * @return DataConnector  Data connector object
     */
    public function getDataConnector() : DataConnector
    {
        return $this->dataConnector;
    }


    ###
    ###  PRIVATE METHOD
    ###

    /**
     * Load the tool proxy from the database.
     * @param string $id The tool proxy id value
     * @return void True if the tool proxy was successfully loaded
     */
    private function load(string $id) : void
    {
        $this->initialize();
        $this->id = $id;
        $ok = $this->dataConnector->loadToolProxy($this);
        if (!$ok) {
            $this->enabled = false;//$autoEnable; // TODO PHP8 Review: Undefined Property
        }
    }
}
