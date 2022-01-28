<?php declare(strict_types=1);

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

/**
 * Writes ECS enrolment status updates
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSEnrolmentStatusWriter
{
    protected ?\ilECSSetting $server = null;
    protected int $obj_id = 0;
    
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
