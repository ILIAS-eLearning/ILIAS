<?php

namespace ILIAS\LTI\ToolProvider;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ConsumerNonce
{

    /**
     * Maximum age nonce values will be retained for (in minutes).
     */
    const MAX_NONCE_AGE = 30;  // in minutes

    /**
     * Date/time when the nonce value expires.
     *
     * @var int|null $expires
     */
    public ?int $expires = null;

    /**
         * Tool Consumer to which this nonce applies.
         */
    private ?\ILIAS\LTI\ToolProvider\Platform $consumer = null;
    /**
         * Nonce value.
         */
    private ?string $value = null;

    /**
     * Class constructor.
     * @param Platform    $consumer Consumer object
     * @param string|null $value    Nonce value (optional, default is null)
     */
    public function __construct(Platform $consumer, ?string $value = null)
    {
        $this->consumer = $consumer;
        $this->value = $value;
        $this->expires = time() + (self::MAX_NONCE_AGE * 60);
    }

    /**
     * Load a nonce value from the database.
     *
     * @return boolean True if the nonce value was successfully loaded
     */
    public function load() : bool
    {
        return $this->consumer->getDataConnector()->loadConsumerNonce($this);
    }

    /**
     * Save a nonce value in the database.
     *
     * @return boolean True if the nonce value was successfully saved
     */
    public function save() : bool
    {
        return $this->consumer->getDataConnector()->saveConsumerNonce($this);
    }

    /**
     * Get tool consumer.
     * @return Platform Consumer for this nonce
     */
    public function getConsumer() : ?\ILIAS\LTI\ToolProvider\Platform
    {
        return $this->consumer;
    }

    /**
     * Get outcome value.
     *
     * @return string Outcome value
     */
    public function getValue() : ?string
    {
        return $this->value;
    }
}
