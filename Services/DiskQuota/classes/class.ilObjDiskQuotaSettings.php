<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Class ilObjDiskQuotaSettings
*
* This class encapsulates accesses to settings which are relevant for the
* disk quota functionality of ILIAS.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
*
* @version $Id$
*
* @extends ilObject
* @package WebDAV
*/

include_once "./Services/Object/classes/class.ilObject.php";

class ilObjDiskQuotaSettings extends ilObject
{
    /**
     * Boolean property. Set this to true, to enable Disk Quota support.
     */
    private $diskQuotaEnabled;
    /**
     * Boolean property. Set this to true, to enable Disk Quota reminder mail.
     */
    private $diskQuotaReminderMailEnabled;

    /** DiskQuota Settings variable */
    private static $dqSettings;
    
    /** Boolean property. Set this to true, to enable Disk Quota summary mail */
    private $diskQuotaSummaryMailEnabled = false;
    
    /** Recipients  */
    private $summary_recipients = '';
    
    private $personalWorkspaceDiskQuotaEnabled;

    /**
    * Constructor
    *
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        // NOTE: We share the facs object with ilObjFileAccessSettings!
        $this->type = "facs";
        parent::__construct($a_id, $a_call_by_reference);
    }
    
    /**
     * Get settings instance
     *
     * @return ilObjDiskQuotaSettings
     */
    public static function getInstance()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT object_reference.ref_id FROM object_reference,tree,object_data " .
            "WHERE tree.parent = " . $ilDB->quote(SYSTEM_FOLDER_ID, 'integer') . " " .
            "AND object_data.type = " . $ilDB->quote('facs', 'text') .
            "AND object_reference.ref_id = tree.child " .
            "AND object_reference.obj_id = object_data.obj_id";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        $ref_id = $row["ref_id"];
        if ($ref_id) {
            $obj = new self($ref_id);
            $obj->read();
            return $obj;
        }
    }

    /**
    * Sets the diskQuotaEnabled property.
    *
    * @param	boolean	new value
    * @return	void
    */
    public function setDiskQuotaEnabled($newValue)
    {
        $this->diskQuotaEnabled = $newValue;
    }
    /**
    * Gets the diskQuotaEnabled property.
    *
    * @return	boolean	value
    */
    public function isDiskQuotaEnabled()
    {
        return $this->diskQuotaEnabled;
    }
    /**
    * Sets the diskQuotaReminderMailEnabled property.
    *
    * @param	boolean	new value
    * @return	void
    */
    public function setDiskQuotaReminderMailEnabled($newValue)
    {
        $this->diskQuotaReminderMailEnabled = $newValue;
    }
    /**
    * Gets the diskQuotaReminderMailEnabled property.
    *
    * @return	boolean	value
    */
    public function isDiskQuotaReminderMailEnabled()
    {
        return $this->diskQuotaReminderMailEnabled;
    }
    /**
    * create
    *
    * note: title, description and type should be set when this function is called
    *
    * @return	integer		object id
    */
    public function create()
    {
        parent::create();
        $this->write();
    }
    /**
    * update object in db
    *
    * @return	boolean	true on success
    */
    public function update()
    {
        parent::update();
        $this->write();
    }
    /**
    * write object data into db
    * @param	boolean
    */
    private function write()
    {
        $settings = new ilSetting('disk_quota');
        $settings->set('enabled', $this->diskQuotaEnabled);
        $settings->set('reminder_mail_enabled', $this->diskQuotaReminderMailEnabled);
        $settings->set('summary_mail_enabled', $this->isDiskQuotaSummaryMailEnabled() ? 1 : 0);
        $settings->set('summary_rcpt', $this->getSummaryRecipients());
        $settings->set('wsp_enabled', $this->personalWorkspaceDiskQuotaEnabled);
    }
    /**
     * read object data from db into object
     */
    public function read()
    {
        parent::read();

        $settings = new ilSetting('disk_quota');
        $this->diskQuotaEnabled = $settings->get('enabled') == true;
        $this->diskQuotaReminderMailEnabled = $settings->get('reminder_mail_enabled') == true;
        $this->isDiskQuotaSummaryMailEnabled($settings->get('summary_mail_enabled') == 1 ? true : false);
        $this->setSummaryRecipients($settings->get('summary_rcpt'));
        $this->setPersonalWorkspaceDiskQuotaEnabled($settings->get('wsp_enabled'));
    }


    /**
     * Looks up the mail template for the specified language.
     *
     * This mail template is used for the reminder mails sent to users
     * who have exceeded their disk quota.
     *
     * @param string $a_lang language code
     * @return array{} Associative array with mail templates.
     */
    public static function _lookupReminderMailTemplate($a_lang)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query("SELECT * FROM mail_template " .
            " WHERE type='dqta' AND lang = " . $ilDB->quote($a_lang, 'text'));

        if ($rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            return $rec;
        }
        return array();
    }

    public function _writeReminderMailTemplate($a_lang, $a_subject, $a_sal_g, $a_sal_f, $a_sal_m, $a_body)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (self::_lookupReminderMailTemplate($a_lang)) {
            $values = array(
                'subject'		=> array('text',$a_subject),
                'body'			=> array('clob',$a_body),
                'sal_g'			=> array('text',$a_sal_g),
                'sal_f'			=> array('text',$a_sal_f),
                'sal_m'			=> array('text',$a_sal_m)
                );
            $ilDB->update(
                'mail_template',
                $values,
                array('lang' => array('text',$a_lang), 'type' => array('text','dqta'))
            );
        } else {
            $values = array(
                'subject'		=> array('text',$a_subject),
                'body'			=> array('clob',$a_body),
                'sal_g'			=> array('text',$a_sal_g),
                'sal_f'			=> array('text',$a_sal_f),
                'sal_m'			=> array('text',$a_sal_m),
                'lang'			=> array('text',$a_lang),
                'type'			=> array('text','dqta')
                );
            $ilDB->insert('mail_template', $values);
        }
    }
    
    /**
     *
     * Setter for summary recipients
     *
     * @param	String	Recipients
     * @access	public
     *
     */
    public function setSummaryRecipients($s_recipients)
    {
        if ($s_recipients) {
            $s_recipients = explode(',', $s_recipients);
            $loginnames = array();
            foreach ($s_recipients as $loginname) {
                $loginname = trim($loginname);
                if (ilObjUser::_lookupId($loginname)) {
                    $loginnames[] = $loginname;
                }
            }
            $s_recipients = implode(',', $loginnames);
        }
            
        $this->summary_recipients = $s_recipients;
        
        return $this;
    }
    
    /**
     *
     * Getter fpr summary recipients
     *
     * @return	String	Recipients
     * @access	public
     *
     */
    public function getSummaryRecipients()
    {
        return $this->summary_recipients;
    }
    
    /**
     *
     * Setter/Getter to activate/deactivate the summary mail cron job
     *
     * @param	mixed	Boolean value or null
     * @return	mixed	Boolean value or instance of ilObjDiskQuoataSettings
     * @access	public
     *
     */
    public function isDiskQuotaSummaryMailEnabled($status = null)
    {
        if (null === $status) {
            return $this->diskQuotaSummaryMailEnabled;
        }
        
        $this->diskQuotaSummaryMailEnabled = $status;
        
        return $this;
    }
    
    /**
    * Sets the personalWorkspaceDiskQuotaEnabled property.
    *
    * @param	boolean	new value
    * @return	void
    */
    public function setPersonalWorkspaceDiskQuotaEnabled($newValue)
    {
        $this->personalWorkspaceDiskQuotaEnabled = $newValue;
    }
    /**
    * Gets the personalWorkspaceDiskQuotaEnabled property.
    *
    * @return	boolean	value
    */
    public function isPersonalWorkspaceDiskQuotaEnabled()
    {
        return $this->personalWorkspaceDiskQuotaEnabled;
    }
} // END class.ilObjDiskQuotaSettings
