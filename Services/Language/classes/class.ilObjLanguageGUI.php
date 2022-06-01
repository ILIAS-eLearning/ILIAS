<?php declare(strict_types=1);

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
 ********************************************************************
 */

/**
* Class ilObjLanguageGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* $Id$Id: class.ilObjLanguageGUI.php,v 1.3 2003/05/16 13:39:22 smeyer Exp $
*
* @extends ilObjectGUI
*/

require_once "./Services/Object/classes/class.ilObjectGUI.php";

class ilObjLanguageGUI extends ilObjectGUI
{
    /**
    * Constructor
    */
    public function __construct(array $a_data, int $a_id, bool $a_call_by_reference)
    {
        $this->type = "lng";
        parent::__construct($a_data, $a_id, $a_call_by_reference);
    }
} // END class.ilObjLanguageGUI
