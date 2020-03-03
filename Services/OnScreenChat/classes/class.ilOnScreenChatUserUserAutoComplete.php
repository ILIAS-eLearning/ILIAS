<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilUserAutoComplete.php';

/**
 * Class ilOnScreenChatUserUserAutoComplete
 * @author  Michael Jansen <mjansen@databay.de>
 */
class ilOnScreenChatUserUserAutoComplete extends ilUserAutoComplete
{
    /**
     * {@inheritdoc}
     */
    protected function getFromPart()
    {
        global $DIC;

        $from_part  = parent::getFromPart();
        $from_part .= '
			INNER JOIN usr_pref chat_osc_am
				ON chat_osc_am.usr_id = ud.usr_id
				AND chat_osc_am.keyword = ' . $DIC->database()->quote('chat_osc_accept_msg', 'text') . '
				AND chat_osc_am.value = ' . $DIC->database()->quote('y', 'text') . ' ';

        return $from_part;
    }

    /**
     * {@inheritdoc}
     */
    protected function getWherePart(array $search_query)
    {
        global $DIC;

        $where  = parent::getWherePart($search_query);
        $where .= ' AND (ud.usr_id != ' . $DIC->database()->quote($this->getUser()->getId(), 'integer') . ') ';

        return $where;
    }
}
