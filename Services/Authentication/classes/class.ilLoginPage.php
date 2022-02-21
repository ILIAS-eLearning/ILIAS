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
 * Login page object
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilLoginPage extends ilPageObject
{
    /**
     * Get parent type
     * @return string parent type
     */
    public function getParentType() : string
    {
        return "auth";
    }
}
