<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * Class for global mail information (e.g. in main menu). This class should only contain methods for fetching data which is necessary in global parts of ILIAS, e.g. the main menu.
 * We should keep this class as small as possible. Maybe we duplicate some code which already exists in class ilMail, but we need an efficient class.
 *
 * @author	Michael Jansen <mjansen@databay.de>
 *
 */
class ilMailGlobalServices
{
    /**
     *
     * Cache array key for mail object reference id
     *
     * @var	integer
     *
     */
    const CACHE_TYPE_REF_ID = 0;
    
    /**
     *
     * Cache array key for number of new mails
     *
     * @var	integer
     *
     */
    const CACHE_TYPE_NEW_MAILS = 1;
    
    /**
     *
     * Cache array
     *
     * @var		array
     * @access	protected
     * @static
     *
     */
    protected static $global_mail_services_cache = array();
    
    /**
     *
     * Determines the reference id of the mail object and stores this information in a local cache variable
     *
     * @access	public
     * @return	integer	The reference id of the mail object
     * @static
     *
     */
    public static function getMailObjectRefId()
    {
        global $DIC;
        
        if (isset(self::$global_mail_services_cache[self::CACHE_TYPE_REF_ID]) &&
           null !== self::$global_mail_services_cache[self::CACHE_TYPE_REF_ID]) {
            return self::$global_mail_services_cache[self::CACHE_TYPE_REF_ID];
        }
        
        // mail settings id is set by a constant in ilias.ini. Keep the select for some time until everyone has updated his ilias.ini
        if (!MAIL_SETTINGS_ID) {
            $res = $DIC->database()->queryF(
                '
				SELECT object_reference.ref_id FROM object_reference, tree, object_data
				WHERE tree.parent = %s
				AND object_data.type = %s
				AND object_reference.ref_id = tree.child
				AND object_reference.obj_id = object_data.obj_id',
                array('integer', 'text'),
                array(SYSTEM_FOLDER_ID, 'mail')
            );

            while ($row = $DIC->database()->fetchAssoc($res)) {
                self::$global_mail_services_cache[self::CACHE_TYPE_REF_ID] = $row['ref_id'];
            }
        } else {
            self::$global_mail_services_cache[self::CACHE_TYPE_REF_ID] = MAIL_SETTINGS_ID;
        }
        
        return self::$global_mail_services_cache[self::CACHE_TYPE_REF_ID];
    }

    /**
     *
     * Determines the number of new mails for the passed user id and stores this information in a local cache variable
     *
     * @access	public
     * @return	integer	The number on unread mails (system messages + inbox mails) for the passed user id
     * @param	integer	A user id of an ILIAS user account
     * @static
     *
     */
    public static function getNumberOfNewMailsByUserId($usr_id)
    {
        global $DIC;

        if (!$usr_id) {
            return 0;
        }

        if (
            isset(self::$global_mail_services_cache[self::CACHE_TYPE_NEW_MAILS][$usr_id]) &&
            null !== self::$global_mail_services_cache[self::CACHE_TYPE_NEW_MAILS][$usr_id]) {
            return self::$global_mail_services_cache[self::CACHE_TYPE_NEW_MAILS][$usr_id];
        }

        $res = $DIC->database()->queryF(
            '
			SELECT COUNT(mail_id) cnt FROM mail 
			WHERE folder_id = %s 
			AND user_id = %s
			AND m_status = %s',
            array('integer', 'integer', 'text'),
            array('0', $usr_id, 'unread')
        );

        $row = $DIC->database()->fetchAssoc($res);

        $res = $DIC->database()->queryF(
            '
			SELECT COUNT(mail_id) cnt FROM mail m,mail_obj_data mo 
		 	WHERE m.user_id = mo.user_id 
		 	AND m.folder_id = mo.obj_id 
		 	AND mo.m_type = %s
			AND m.user_id = %s
	 		AND m.m_status = %s',
            array('text', 'integer', 'text'),
            array('inbox', $usr_id, 'unread')
        );

        $row2 = $DIC->database()->fetchAssoc($res);

        self::$global_mail_services_cache[self::CACHE_TYPE_NEW_MAILS][$usr_id] = $row['cnt'] + $row2['cnt'];
        return self::$global_mail_services_cache[self::CACHE_TYPE_NEW_MAILS][$usr_id];
    }
}
