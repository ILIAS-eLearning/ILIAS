<?php

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

use ILIAS\Administration\HeaderTitleRepo;

/**
 * Class ilObjSystemFolder
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjSystemFolder extends ilObject
{
    protected ilObjUser $user;

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id, $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->type = "adm";
        parent::__construct($a_id, $a_call_by_reference);
    }


    /**
    * delete systemfolder and all related data
    * DISABLED
    */
    public function delete(): bool
    {
        // DISABLED
        return false;
    }

    public static function _getHeaderTitle(): string
    {
        $repo = new HeaderTitleRepo();
        return $repo->getHeaderTitle();
    }
} // END class.ilObjSystemFolder
