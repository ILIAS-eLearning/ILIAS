<?php

/**
 * Class ilChatroomServerConnectorMock
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomServerConnectorMock extends ilChatroomServerConnector
{
    protected $error;

    public function __construct()
    {
        $settings = new ilChatroomServerSettings();
        parent::__construct($settings);
    }

    /**
     * @param boolean $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }
}
