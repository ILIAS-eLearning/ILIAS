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
 * Interface for all command queue handler classes
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
interface ilECSCommandQueueHandler
{
    /**
     * Handle create event
     * @param ilECSSetting $server
     * @param type $a_content_id
     */
    public function handleCreate(ilECSSetting $server, $a_content_id);
    
    /**
     * Handle update
     * @param ilECSSetting $server
     * @param type $a_content_id
     */
    public function handleUpdate(ilECSSetting $server, $a_content_id);
    
    /**
     * Handle delete action
     * @param ilECSSetting $server
     * @param type $a_content_id
     */
    public function handleDelete(ilECSSetting $server, $a_content_id);
}
