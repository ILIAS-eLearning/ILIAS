<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAppEventListener implements \ilAppEventListener
{
    /** @var \ilTermsOfServiceHelper $helper */
    protected $helper;

    /** @var string */
    protected $component = '';

    /** @var string */
    protected $event = '';

    /** @var array */
    protected $parameters = [];

    /**
     * ilTermsOfServiceAppEventListener constructor.
     * @param \ilTermsOfServiceHelper $helper
     */
    public function __construct(\ilTermsOfServiceHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param string $component
     * @return \ilTermsOfServiceAppEventListener
     */
    public function withComponent(string $component) : self
    {
        $clone = clone $this;

        $clone->component = $component;

        return $clone;
    }

    /**
     * @param string $event
     * @return \ilTermsOfServiceAppEventListener
     */
    public function withEvent(string $event) : self
    {
        $clone = clone $this;

        $clone->event = $event;

        return $clone;
    }

    /**
     * @param array $parameters
     * @return \ilTermsOfServiceAppEventListener
     */
    public function withParameters(array $parameters) : self
    {
        $clone = clone $this;

        $clone->parameters = $parameters;

        return $clone;
    }

    /**
     * @return bool
     */
    protected function isUserDeletionEvent() : bool
    {
        return (
            'Services/User' === $this->component &&
            'deleteUser' === $this->event
        );
    }

    /**
     * @throws \ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function handle()
    {
        if ($this->isUserDeletionEvent()) {
            $this->helper->deleteAcceptanceHistoryByUser($this->parameters['usr_id']);
        }
    }

    /**
     * @inheritdoc
     */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        $listener = new static(new \ilTermsOfServiceHelper());
        $listener
            ->withComponent($a_component)
            ->withEvent($a_event)
            ->withParameters($a_parameter)
            ->handle();
    }
}
