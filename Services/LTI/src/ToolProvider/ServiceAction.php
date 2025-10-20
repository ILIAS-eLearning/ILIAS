<?php
declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\LTI\ToolProvider;

/**
 * Enumeration to define alternative service actions
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
enum ServiceAction: int
{

    /**
     * Read action.
     */
    case Read = 1;

    /**
     * Write (create/update) action.
     */
    case Write = 2;

    /**
     * Delete action.
     */
    case Delete = 3;

    /**
     * Create action.
     */
    case Create = 4;

    /**
     * Update action.
     */
    case Update = 5;

}
