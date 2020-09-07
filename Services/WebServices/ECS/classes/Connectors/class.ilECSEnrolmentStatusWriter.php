<?php

/*
 * Writes ECS enrolment status updates
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSEnrolmentStatusWriter
{
    protected $server = null;
    protected $obj_id = 0;
    
    /**
     * Constructor
     */
    public function __construct($a_obj_id)
    {
        $this->obj_id = 0;
    }
    
    /**
     * ECS server settings
     * @return ilECSSetting
     */
    public function getServer()
    {
        return $this->server;
    }
}
