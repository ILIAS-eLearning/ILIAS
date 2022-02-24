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
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*/
class ilECSSetting
{
    const DEFAULT_AUTH_MODE = 'ldap';
    
    const ERROR_EXTRACT_SERIAL = 'ecs_error_extract_serial';
    const ERROR_REQUIRED = 'fill_out_all_required_fields';
    const ERROR_INVALID_IMPORT_ID = 'ecs_check_import_id';
    const ERROR_CERT_EXPIRED = 'ecs_certificate_expired';

    const AUTH_CERTIFICATE = 1;
    const AUTH_APACHE = 2;
    
    const DEFAULT_DURATION = 6;
    
    
    const PROTOCOL_HTTP = 0;
    const PROTOCOL_HTTPS = 1;
    
    protected static ?array $instances = null;
    protected static $configured;

    private $server_id = 0;
    private bool $active = false;
    private string $title = '';
    private int $auth_type = self::AUTH_CERTIFICATE;
    private $server;
    private $protocol;
    private $port;
    private $client_cert_path;
    private $ca_cert_path;
    private $cert_serial_number;
    private $key_path;
    private $key_password;
    private $polling;
    private int $import_id = 0;
    private $cert_serial;
    private $global_role;
    private int $duration;

    private string $auth_user = '';
    private string $auth_pass = '';
    
    private $user_recipients = array();
    private array $econtent_recipients = array();
    private $approval_recipients = array();
    
    private ilDBInterface $db;
    private ilLogger $log;
    private ilObjectDataCache $objDataCache;
    private ilTree $tree;

    /**
     * Singleton contructor
     *
     * @access private
     */
    private function __construct($a_server_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->log = $DIC->logger()->wsrv();
        $this->objDataCache = $DIC['ilObjDataCache'];
        $this->tree = $DIC->repositoryTree();

        $this->server_id = $a_server_id;
        $this->read();
    }

    /**
     * Get singleton instance per server
     * @param int $a_server_id
     * @return ilECSSetting
     */
    public static function getInstanceByServerId(int $a_server_id) : ilECSSetting
    {
        if (isset(self::$instances) && isset(self::$instances[$a_server_id])) {
            return self::$instances[$a_server_id];
        }
        return self::$instances[$a_server_id] = new ilECSSetting($a_server_id);
    }
    
    /**
     * Lookup auth mode
     */
    public static function lookupAuthMode()
    {
        return self::DEFAULT_AUTH_MODE;
    }

    /**
     * Checks if an ecs server is configured
     * @deprecated use ilECSServerSettings::getInstance()->serverExists()
     * @return boolean
     */
    public static function ecsConfigured() : bool
    {
        return ilECSServerSettings::getInstance()->serverExists();
    }

    /**
     * Set title
     * @param string $a_title
     */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
     * Get title
     * @return string title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set auth type
     * @param int $a_auth_type
     */
    public function setAuthType($a_auth_type)
    {
        $this->auth_type = $a_auth_type;
    }

    /**
     * Get auth type
     * @return int
     */
    public function getAuthType()
    {
        return $this->auth_type;
    }

    /**
     * Set apache auth user
     * @param string $a_user
     */
    public function setAuthUser($a_user)
    {
        $this->auth_user = $a_user;
    }

    /**
     * Get apache auth user
     * @return string
     */
    public function getAuthUser()
    {
        return $this->auth_user;
    }

    /**
     * Set Apache auth password
     * @param string $a_pass
     */
    public function setAuthPass($a_pass)
    {
        $this->auth_pass = $a_pass;
    }

    /**
     * Get auth password
     * @return string
     */
    public function getAuthPass()
    {
        return $this->auth_pass;
    }

    /**
     * Get current server id
     * @return int
     */
    public function getServerId()
    {
        return (int) $this->server_id;
    }
    
    /**
     * en/disable ecs functionality
     *
     * @param bool status
     *
     */
    public function setEnabledStatus(bool $a_status) : void
    {
        $this->active = $a_status;
    }
    
    /**
     * is enabled
     *
     */
    public function isEnabled() : bool
    {
        return $this->active;
    }
    
    /**
     * set server
     *
     * @access public
     * @param
     *
     */
    public function setServer($a_server)
    {
        $this->server = $a_server;
    }
    
    /**
     * get server
     *
     * @access public
     * @param
     *
     */
    public function getServer()
    {
        return $this->server;
    }
    
    /**
     * get complete server uri
     *
     * @access public
     *
     */
    public function getServerURI()
    {
        switch ($this->getProtocol()) {
            case self::PROTOCOL_HTTP:
                $uri = 'http://';
                break;
                
            case self::PROTOCOL_HTTPS:
                $uri = 'https://';
                break;
        }

        if (stristr($this->getServer(), '/')) {
            $counter = 0;
            foreach ((array) explode('/', $this->getServer()) as $key => $part) {
                $uri .= $part;
                if (!$counter) {
                    $uri .= ':' . $this->getPort();
                }
                $uri .= '/';
                ++$counter;
            }
            $uri = substr($uri, 0, -1);
        } else {
            $uri .= $this->getServer();
            $uri .= (':' . $this->getPort());
        }

        return $uri;
    }
    
    /**
     * set protocol
     *
     * @access public
     * @param
     *
     */
    public function setProtocol($a_prot)
    {
        $this->protocol = $a_prot;
    }

    /**
     * get protocol
     *
     * @access public
     *
     */
    public function getProtocol()
    {
        return $this->protocol;
    }
    
    /**
     * set port
     *
     * @access public
     * @param int port
     *
     */
    public function setPort($a_port)
    {
        $this->port = $a_port;
    }
    
    /**
     * get port
     *
     * @access public
     * @param
     *
     */
    public function getPort()
    {
        return $this->port;
    }
    
    /**
     * set polling time
     *
     * @access public
     * @param int polling time
     *
     */
    public function setPollingTime($a_time)
    {
        $this->polling = $a_time;
    }
    
    /**
     * get polling time
     *
     * @access public
     *
     */
    public function getPollingTime()
    {
        return $this->polling;
    }
    
    /**
     * get polling time seconds (<60)
     *
     * @access public
     *
     */
    public function getPollingTimeSeconds()
    {
        return (int) ($this->polling % 60);
    }
    
    /**
     * get polling time minutes
     *
     * @access public
     *
     */
    public function getPollingTimeMinutes()
    {
        return (int) ($this->polling / 60);
    }
    
    /**
     * Set polling time
     *
     * @access public
     *
     * @param int minutes
     * @param int seconds
     */
    public function setPollingTimeMS($a_min, $a_sec)
    {
        $this->setPollingTime(60 * $a_min + $a_sec);
    }
    
    /**
     * set
     *
     * @access public
     * @param
     *
     */
    public function setClientCertPath($a_path)
    {
        $this->client_cert_path = $a_path;
    }

    /**
     * get certificate path
     *
     * @access public
     */
    public function getClientCertPath()
    {
        return $this->client_cert_path;
    }
    
    /**
     * set ca cert path
     *
     * @access public
     * @param string ca cert path
     *
     */
    public function setCACertPath($a_ca)
    {
        $this->ca_cert_path = $a_ca;
    }
    
    /**
     * get ca cert path
     *
     * @access public
     *
     */
    public function getCACertPath()
    {
        return $this->ca_cert_path;
    }
    
    /**
     * get key path
     *
     * @access public
     *
     */
    public function getKeyPath()
    {
        return $this->key_path;
    }
    
    /**
     * set key path
     *
     * @access public
     * @param string key path
     *
     */
    public function setKeyPath($a_path)
    {
        $this->key_path = $a_path;
    }
    
    /**
     * get key password
     *
     * @access public
     *
     */
    public function getKeyPassword()
    {
        return $this->key_password;
    }
    
    /**
     * set key password
     *
     * @access public
     * @param string key password
     *
     */
    public function setKeyPassword($a_pass)
    {
        $this->key_password = $a_pass;
    }
    
    /**
     * set import id
     * Object of category, that store new remote courses
     */
    public function setImportId(int $a_id) : void
    {
        $this->import_id = $a_id;
    }
    
    /**
     * get import id
     */
    public function getImportId() : int
    {
        return $this->import_id;
    }
    
    /**
     * set cert serial number
     *
     * @access public
     * @param
     *
     */
    public function setCertSerialNumber($a_cert_serial)
    {
        $this->cert_serial_number = $a_cert_serial;
    }
    
    /**
     * get cert serial number
     *
     * @access public
     *
     */
    public function getCertSerialNumber()
    {
        return $this->cert_serial_number;
    }
    
    /**
     * get global role
     *
     * @access public
     *
     */
    public function getGlobalRole()
    {
        return $this->global_role;
    }
    
    /**
     * set default global role
     *
     * @access public
     *
     * @param int role_id
     */
    public function setGlobalRole($a_role_id)
    {
        $this->global_role = $a_role_id;
    }
    
    /**
     * set Duration
     *
     * @param int duration
     *
     */
    public function setDuration(int $a_duration) : void
    {
        $this->duration = $a_duration;
    }
    
    /**
     * get duration
     *
     * @access public
     *
     */
    public function getDuration() : int
    {
        return $this->duration ? $this->duration : self::DEFAULT_DURATION;
    }
    
    /**
     * Get new user recipients
     *
     * @access public
     *
     */
    public function getUserRecipients() : array
    {
        return $this->user_recipients;
    }
    
    /**
     * Get new user recipients
     *
     * @access public
     *
     */
    public function getUserRecipientsAsString()
    {
        return implode(',', $this->user_recipients);
    }
    
    /**
     * set user recipients
     *
     * @access public
     * @param array of recipients (array of user login names)
     *
     */
    public function setUserRecipients(array $a_logins)
    {
        $this->user_recipients = $a_logins;
    }
    
    /**
     * get Econtent recipients
     *
     * @access public
     *
     */
    public function getEContentRecipients()
    {
        return $this->econtent_recipients;
    }
    
    /**
     * get EContent recipients as string
     *
     * @access public
     *
     */
    public function getEContentRecipientsAsString()
    {
        return implode(',', $this->econtent_recipients);
    }
    
    /**
     * set EContent recipients
     *
     * @access public
     * @param array of user obj_ids
     *
     */
    public function setEContentRecipients(array $a_logins)
    {
        $this->econtent_recipients = $a_logins;
    }
    
    /**
     * get approval recipients
     *
     * @access public
     * @return bool
     */
    public function getApprovalRecipients()
    {
        return $this->approval_recipients;
    }
    
    /**
     * get approval recipients as string
     *
     * @access public
     * @param
     * @return
     */
    public function getApprovalRecipientsAsString()
    {
        return implode(',', $this->approval_recipients);
    }
    
    /**
     * set approval recipients
     *
     * @access public
     * @param string recipients
     */
    public function setApprovalRecipients(array $a_rcp)
    {
        $this->approval_recipients = $a_rcp;
    }
    
    /**
     * Validate settings
     *
     * @access public
     * @param void
     * @return bool
     *
     */
    public function validate()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        // Cert based authentication
        if ($this->getAuthType() == self::AUTH_CERTIFICATE) {
            if (!$this->getClientCertPath() or !$this->getCACertPath() or !$this->getKeyPath() or !$this->getKeyPassword()) {
                return self::ERROR_REQUIRED;
            }
            // Check import id
            if (!$this->fetchSerialID()) {
                return self::ERROR_EXTRACT_SERIAL;
            }
            if (!$this->fetchCertificateExpiration()) {
                return self::ERROR_CERT_EXPIRED;
            }
        }
        // Apache auth
        if ($this->getAuthType() == self::AUTH_APACHE) {
            if (!$this->getAuthUser() or !$this->getAuthPass()) {
                return self::ERROR_REQUIRED;
            }
        }

        // required fields
        if (!$this->getServer() or !$this->getPort() or !$this->getImportId()
            or !$this->getGlobalRole() or !$this->getDuration()) {
            return self::ERROR_REQUIRED;
        }
        
        if (!$this->checkImportId()) {
            return self::ERROR_INVALID_IMPORT_ID;
        }
        return '';
    }
    
    /**
     * check import id
     *
     * @access public
     *
     */
    public function checkImportId()
    {
        if (!$this->getImportId()) {
            return false;
        }
        if ($this->objDataCache->lookupType($this->objDataCache->lookupObjId($this->getImportId())) != 'cat') {
            return false;
        }
        if ($this->tree->isDeleted($this->getImportId())) {
            return false;
        }
        return true;
    }
    
    /**
     * save settings
     *
     * @access public
     *
     */
    public function save()
    {
        $this->server_id = $this->db->nextId('ecs_server');
        $this->db->manipulate(
            'INSERT INTO ecs_server (server_id,active,title,protocol,server,port,auth_type,client_cert_path,ca_cert_path,' .
            'key_path,key_password,cert_serial,polling_time,import_id,global_role,econtent_rcp,user_rcp,approval_rcp,duration,auth_user,auth_pass) ' .
            'VALUES (' .
            $this->db->quote($this->getServerId(), 'integer') . ', ' .
            $this->db->quote((int) $this->isEnabled(), 'integer') . ', ' .
            $this->db->quote($this->getTitle(), 'text') . ', ' .
            $this->db->quote((int) $this->getProtocol(), 'integer') . ', ' .
            $this->db->quote($this->getServer(), 'text') . ', ' .
            $this->db->quote($this->getPort(), 'integer') . ', ' .
            $this->db->quote($this->getAuthType(), 'integer') . ', ' .
            $this->db->quote($this->getClientCertPath(), 'text') . ', ' .
            $this->db->quote($this->getCACertPath(), 'text') . ', ' .
            $this->db->quote($this->getKeyPath(), 'text') . ', ' .
            $this->db->quote($this->getKeyPassword(), 'text') . ', ' .
            $this->db->quote($this->getCertSerialNumber(), 'text') . ', ' .
            $this->db->quote($this->getPollingTime(), 'integer') . ', ' .
            $this->db->quote($this->getImportId(), 'integer') . ', ' .
            $this->db->quote($this->getGlobalRole(), 'integer') . ', ' .
            $this->db->quote($this->getEContentRecipientsAsString(), 'text') . ', ' .
            $this->db->quote($this->getUserRecipientsAsString(), 'text') . ', ' .
            $this->db->quote($this->getApprovalRecipientsAsString(), 'text') . ', ' .
            $this->db->quote($this->getDuration(), 'integer') . ', ' .
            $this->db->quote($this->getAuthUser(), 'text') . ', ' .
            $this->db->quote($this->getAuthPass(), 'text') . ' ' .
            ')'
        );
    }

    /**
     * Update setting
     */
    public function update()
    {
        $this->db->manipulate(
            'UPDATE ecs_server SET ' .
            'server_id = ' . $this->db->quote($this->getServerId(), 'integer') . ', ' .
            'active = ' . $this->db->quote((int) $this->isEnabled(), 'integer') . ', ' .
            'title = ' . $this->db->quote($this->getTitle(), 'text') . ', ' .
            'protocol = ' . $this->db->quote((int) $this->getProtocol(), 'integer') . ', ' .
            'server = ' . $this->db->quote($this->getServer(), 'text') . ', ' .
            'port = ' . $this->db->quote($this->getPort(), 'integer') . ', ' .
            'auth_type = ' . $this->db->quote($this->getAuthType(), 'integer') . ', ' .
            'client_cert_path = ' . $this->db->quote($this->getClientCertPath(), 'text') . ', ' .
            'ca_cert_path = ' . $this->db->quote($this->getCACertPath(), 'text') . ', ' .
            'key_path = ' . $this->db->quote($this->getKeyPath(), 'text') . ', ' .
            'key_password = ' . $this->db->quote($this->getKeyPassword(), 'text') . ', ' .
            'cert_serial = ' . $this->db->quote($this->getCertSerialNumber(), 'text') . ', ' .
            'polling_time = ' . $this->db->quote($this->getPollingTime(), 'integer') . ', ' .
            'import_id = ' . $this->db->quote($this->getImportId(), 'integer') . ', ' .
            'global_role = ' . $this->db->quote($this->getGlobalRole(), 'integer') . ', ' .
            'econtent_rcp = ' . $this->db->quote($this->getEContentRecipientsAsString(), 'text') . ', ' .
            'user_rcp = ' . $this->db->quote($this->getUserRecipientsAsString(), 'text') . ', ' .
            'approval_rcp = ' . $this->db->quote($this->getApprovalRecipientsAsString(), 'text') . ', ' .
            'duration = ' . $this->db->quote($this->getDuration(), 'integer') . ', ' .
            'auth_user = ' . $this->db->quote($this->getAuthUser(), 'text') . ', ' .
            'auth_pass = ' . $this->db->quote($this->getAuthPass(), 'text') . ', ' .
            'auth_type = ' . $this->db->quote($this->getAuthType(), 'integer') . ' ' .
            'WHERE server_id = ' . $this->db->quote($this->getServerId(), 'integer')
        );
    }

    /**
     * Delete
     */
    public function delete()
    {
        // --- cascading delete
        ilECSCmsData::deleteByServerId($this->getServerId());
        
        //TODO fix properly
        ilECSCommunityCache::getInstance($this->getServerId(), -1)->deleteByServerId($this->getServerId());

        ilECSDataMappingSettings::getInstanceByServerId($this->getServerId())->delete();
        
        (new ilECSEventQueueReader($this))->deleteServer();

        ilECSNodeMappingAssignment::deleteByServerId($this->getServerId());
        
        $query = 'DELETE FROM ecs_events' .
            ' WHERE server_id = ' . $this->db->quote($this->getServerId(), 'integer');
        $this->db->manipulate($query);
        
        ilECSExportManager::getInstance()->deleteByServer($this->getServerId());
                
        //TODO check which one we need
        ilECSImportManager::getInstance()->deleteByServer($this->getServerId());

        // resetting server id to flag items in imported list
        ilECSImportManager::getInstance()->resetServerId($this->getServerId());
                        
        $this->db->manipulate(
            'DELETE FROM ecs_server ' .
            'WHERE server_id = ' . $this->db->quote($this->getServerId(), 'integer')
        );
        
        $this->server_id = null;
        return true;
    }


    /**
     * Fetch validity (expired date)
     * @return bool
     */
    public function fetchCertificateExpiration()
    {
        if ($this->getAuthType() != self::AUTH_CERTIFICATE) {
            return null;
        }

        if (function_exists('openssl_x509_parse') and $cert = openssl_x509_parse('file://' . $this->getClientCertPath())) {
            if (isset($cert['validTo_time_t']) and $cert['validTo_time_t']) {
                $dt = new ilDateTime($cert['validTo_time_t'], IL_CAL_UNIX);
                
                $this->log->debug('Certificate expires at: ' . ilDatePresentation::formatDate($dt));
                return $dt;
            }
        }
        return null;
    }
    
    /**
     * Fetch serial ID from cert
     *
     * @access private
     *
     */
    private function fetchSerialID()
    {
        if (function_exists('openssl_x509_parse') and $cert = openssl_x509_parse('file://' . $this->getClientCertPath())) {
            if (isset($cert['serialNumber']) and $cert['serialNumber']) {
                $this->setCertSerialNumber($cert['serialNumber']);
                $this->log->debug('Searial number is: ' . $cert['serialNumber']);
                return true;
            }
        }
        
        if (!file_exists($this->getClientCertPath()) or !is_readable($this->getClientCertPath())) {
            return false;
        }
        $lines = file($this->getClientCertPath());
        $found = false;
        foreach ($lines as $line) {
            if (strpos($line, 'Serial Number:') !== false) {
                $found = true;
                $serial_line = explode(':', $line);
                $serial = (int) trim($serial_line[1]);
                break;
            }
        }
        if ($found) {
            $this->setCertSerialNumber($serial);
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Read settings
     *
     * @access private
     */
    private function read()
    {
        if (!$this->getServerId()) {
            return false;
        }

        $query = 'SELECT * FROM ecs_server ' .
            'WHERE server_id = ' . $this->db->quote($this->getServerId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $this->setServer($row['server']);
            $this->setTitle($row['title']);
            $this->setProtocol($row['protocol']);
            $this->setPort($row['port']);
            $this->setClientCertPath($row['client_cert_path']);
            $this->setCACertPath($row['ca_cert_path']);
            $this->setKeyPath($row['key_path']);
            $this->setKeyPassword($row['key_password']);
            $this->setPollingTime($row['polling_time']);
            $this->setImportId(intval($row['import_id']));
            $this->setEnabledStatus(boolval($row['active']));
            $this->setCertSerialNumber($row['cert_serial']);
            $this->setGlobalRole($row['global_role']);
            $this->econtent_recipients = explode(',', $row['econtent_rcp']);
            $this->approval_recipients = explode(',', $row['approval_rcp']);
            $this->user_recipients = explode(',', $row['user_rcp']);
            $this->setDuration(intval($row['duration']));
            $this->setAuthUser($row['auth_user']);
            $this->setAuthPass($row['auth_pass']);
            $this->setAuthType(intval($row['auth_type']));
        }
    }

    /**
     * Overwritten clone method
     * Reset all connection settings
     */
    public function __clone()
    {
        $this->server_id = 0;
        $this->setTitle($this->getTitle() . ' (Copy)');
        $this->setEnabledStatus(false);
        $this->setServer('');
        $this->setProtocol(self::PROTOCOL_HTTPS);
        $this->setPort(0);
        $this->setClientCertPath('');
        $this->setKeyPath('');
        $this->setKeyPassword('');
        $this->setCACertPath('');
        $this->setCertSerialNumber('');
        $this->setAuthType(self::AUTH_CERTIFICATE);
        $this->setAuthUser('');
        $this->setAuthPass('');
    }
}
