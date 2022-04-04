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
 *********************************************************************/

require_once 'Services/User/classes/class.ilUserAutoComplete.php';

/**
 * Class ilOnScreenChatUserUserAutoComplete
 * @author  Michael Jansen <mjansen@databay.de>
 */
class ilOnScreenChatUserUserAutoComplete extends ilUserAutoComplete
{
    protected function getFromPart() : string
    {
        global $DIC;

        $from_part = parent::getFromPart();
        $from_part .= '
			INNER JOIN usr_pref chat_osc_am
				ON chat_osc_am.usr_id = ud.usr_id
				AND chat_osc_am.keyword = ' . $DIC->database()->quote('chat_osc_accept_msg', 'text') . '
				AND chat_osc_am.value = ' . $DIC->database()->quote('y', 'text') . ' ';

        return $from_part;
    }

    protected function getWherePart(array $search_query) : string
    {
        global $DIC;

        $where = parent::getWherePart($search_query);
        $where .= ' AND (ud.usr_id != ' . $DIC->database()->quote($this->getUser()->getId(), 'integer') . ') ';

        return $where;
    }
}
