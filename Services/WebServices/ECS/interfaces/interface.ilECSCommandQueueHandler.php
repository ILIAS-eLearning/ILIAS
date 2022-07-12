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
     */
    public function handleCreate(ilECSSetting $server, int $a_content_id) : bool;
    
    /**
     * Handle update
     */
    public function handleUpdate(ilECSSetting $server, int $a_content_id) : bool;
    
    /**
     * Handle delete action
     */
    public function handleDelete(ilECSSetting $server, int $a_content_id) : bool;
}
