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
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesWebServicesECS
*/
class ilECSAuth
{
    protected ilLogger $log;
    protected array $mids = array();

    //public $url;
    public $realm;
    #	public $hash;
    #	public $sov;
    #	public $eov;
    #	public $url;
    #	public $abbr;
    #	public $pid;
    

    /**
     * constuctor
     *
     * @access public
     * @param
     *
     */
    public function __construct()
    {
        global $DIC;
        
        $this->log = $DIC->logger()->wsrv();
    }
    
    public function setPid($a_pid)
    {
        $this->pid = $a_pid;
    }
    
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * URL
     * @param string $a_url
     */
    public function setUrl($a_url)
    {
        $this->url = $a_url;
    }
    
    /**
     * get Url
     * @return <type>
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    public function setRealm($a_realm)
    {
        $this->realm = $a_realm;
    }
    
    public function getRealm()
    {
        return $this->realm;
    }
    
    /**
     * get hash
     *
     * @access public
     *
     */
    public function getHash()
    {
        return $this->hash;
    }
    
    /**
     * set SOV
     *
     * @access public
     * @param int start of verification
     *
     */
    public function setSOV($a_sov)
    {
        $dt = new ilDateTime($a_sov, IL_CAL_UNIX);
        $this->sov = $dt->get(IL_CAL_ISO_8601);
    }

    /**
     * set EOV
     *
     * @access public
     * @param int eov of verification
     *
     */
    public function setEOV($a_eov)
    {
        $dt = new ilDateTime($a_eov, IL_CAL_UNIX);
        $this->sov = $dt->get(IL_CAL_ISO_8601);
    }
}
