<?php

declare(strict_types=1);

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
    public const DEFAULT_AUTH_MODE = 'ldap';

    public const ERROR_EXTRACT_SERIAL = 'ecs_error_extract_serial';
    public const ERROR_REQUIRED = 'fill_out_all_required_fields';
    public const ERROR_INVALID_IMPORT_ID = 'ecs_check_import_id';
    public const ERROR_CERT_EXPIRED = 'ecs_certificate_expired';

    public const AUTH_CERTIFICATE = 1;
    public const AUTH_APACHE = 2;

    public const DEFAULT_DURATION = 6;


    public const PROTOCOL_HTTP = 0;
    public const PROTOCOL_HTTPS = 1;

    protected static ?array $instances = null;

    private int $server_id;
    private bool $active = false;
    private string $title = '';
    private int $auth_type = self::AUTH_CERTIFICATE;
    private string $server = '';
    private int $protocol = self::PROTOCOL_HTTPS;
    private int $port = 0;
    private string $client_cert_path = '';
    private string $ca_cert_path = '';
    private ?string $cert_serial_number = '';
    private string $key_path = '';
    private string $key_password = '';
    private int $polling = 0;
    private int $import_id = 0;
    private int $global_role = 0;
    private int $duration = 0;

    private string $auth_user = '';
    private string $auth_pass = '';

    private array $user_recipients = [];
    private array $econtent_recipients = [];
    private array $approval_recipients = [];

    private ilDBInterface $db;
    private ilLogger $log;
    private ilObjectDataCache $objDataCache;
    private ilTree $tree;

    /**
     * Singleton contructor
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
    public static function getInstanceByServerId(int $a_server_id): ilECSSetting
    {
        return self::$instances[$a_server_id] ?? (self::$instances[$a_server_id] = new ilECSSetting($a_server_id));
    }

    /**
     * Lookup auth mode
     */
    public static function lookupAuthMode(): string
    {
        return self::DEFAULT_AUTH_MODE;
    }

    /**
     * Checks if an ecs server is configured
     * @deprecated use ilECSServerSettings::getInstance()->serverExists()
     */
    public static function ecsConfigured(): bool
    {
        return ilECSServerSettings::getInstance()->serverExists();
    }

    /**
     * Set title
     */
    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    /**
     * Get title
     * @return string title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set auth type
     */
    public function setAuthType($a_auth_type): void
    {
        $this->auth_type = $a_auth_type;
    }

    /**
     * Get auth type
     */
    public function getAuthType(): int
    {
        return $this->auth_type;
    }

    /**
     * Set apache auth user
     */
    public function setAuthUser($a_user): void
    {
        $this->auth_user = $a_user;
    }

    /**
     * Get apache auth user
     */
    public function getAuthUser(): string
    {
        return $this->auth_user;
    }

    /**
     * Set Apache auth password
     */
    public function setAuthPass($a_pass): void
    {
        $this->auth_pass = $a_pass;
    }

    /**
     * Get auth password
     */
    public function getAuthPass(): string
    {
        return $this->auth_pass;
    }

    /**
     * Get current server id
     */
    public function getServerId(): int
    {
        return (int) $this->server_id;
    }

    /**
     * en/disable ecs functionality
     *
     */
    public function setEnabledStatus(bool $status): void
    {
        $this->active = $status;
    }

    /**
     * is enabled
     */
    public function isEnabled(): bool
    {
        return $this->active;
    }

    /**
     * set server
     */
    public function setServer(string $a_server): void
    {
        $this->server = $a_server;
    }

    /**
     * get server
     */
    public function getServer(): string
    {
        return $this->server;
    }

    /**
     * get complete server uri
     */
    public function getServerURI()
    {
        $uri = "";
        switch ($this->getProtocol()) {
            case self::PROTOCOL_HTTP:
                $uri .= 'http://';
                break;

            case self::PROTOCOL_HTTPS:
                $uri .= 'https://';
                break;
        }

        if (strpos($this->getServer(), '/') !== false) {
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
     */
    public function setProtocol(int $a_prot): void
    {
        $this->protocol = $a_prot;
    }

    /**
     * get protocol
     */
    public function getProtocol(): int
    {
        return $this->protocol;
    }

    /**
     * set port
     */
    public function setPort(int $a_port): void
    {
        $this->port = $a_port;
    }

    /**
     * get port
     */
    public function getPort(): int
    {
        return $this->port;
    }

    public function setClientCertPath($a_path): void
    {
        $this->client_cert_path = $a_path;
    }

    /**
     * get certificate path
     */
    public function getClientCertPath(): string
    {
        return $this->client_cert_path;
    }

    /**
     * set ca cert path
     *
     * @param string ca cert path
     */
    public function setCACertPath(string $a_ca): void
    {
        $this->ca_cert_path = $a_ca;
    }

    /**
     * get ca cert path
     */
    public function getCACertPath(): string
    {
        return $this->ca_cert_path;
    }

    /**
     * get key path
     */
    public function getKeyPath(): string
    {
        return $this->key_path;
    }

    /**
     * set key path
     *
     * @param string key path
     */
    public function setKeyPath(string $a_path): void
    {
        $this->key_path = $a_path;
    }

    /**
     * get key password
     */
    public function getKeyPassword(): string
    {
        return $this->key_password;
    }

    /**
     * set key password
     *
     * @param string key password
     */
    public function setKeyPassword(string $a_pass): void
    {
        $this->key_password = $a_pass;
    }

    /**
     * set import id
     * Object of category, that store new remote courses
     */
    public function setImportId(int $a_id): void
    {
        $this->import_id = $a_id;
    }

    /**
     * get import id
     */
    public function getImportId(): int
    {
        return $this->import_id;
    }

    /**
     * set cert serial number
     */
    public function setCertSerialNumber(string $a_cert_serial): void
    {
        $this->cert_serial_number = $a_cert_serial;
    }

    /**
     * get cert serial number
     */
    public function getCertSerialNumber(): ?string
    {
        return $this->cert_serial_number;
    }

    /**
     * get global role
     */
    public function getGlobalRole(): int
    {
        return $this->global_role;
    }

    /**
     * set default global role
     */
    public function setGlobalRole(int $a_role_id): void
    {
        $this->global_role = $a_role_id;
    }

    /**
     * set Duration
     */
    public function setDuration(int $a_duration): void
    {
        $this->duration = $a_duration;
    }

    /**
     * get duration
     */
    public function getDuration(): int
    {
        return $this->duration ?: self::DEFAULT_DURATION;
    }

    /**
     * Get new user recipients
     */
    public function getUserRecipients(): array
    {
        return $this->user_recipients;
    }

    /**
     * Get new user recipients
     */
    public function getUserRecipientsAsString(): string
    {
        return implode(',', $this->user_recipients);
    }

    /**
     * set user recipients
     *
     * @param array of recipients (array of user login names)
     *
     */
    public function setUserRecipients(array $a_logins): void
    {
        $this->user_recipients = $a_logins;
    }

    /**
     * get Econtent recipients
     */
    public function getEContentRecipients(): array
    {
        return $this->econtent_recipients;
    }

    /**
     * get EContent recipients as string
     */
    public function getEContentRecipientsAsString(): string
    {
        return implode(',', $this->econtent_recipients);
    }

    /**
     * set EContent recipients
     *
     * @param array of user obj_ids
     */
    public function setEContentRecipients(array $a_logins): void
    {
        $this->econtent_recipients = $a_logins;
    }

    /**
     * get approval recipients
     */
    public function getApprovalRecipients(): array
    {
        return $this->approval_recipients;
    }

    /**
     * get approval recipients as string
     */
    public function getApprovalRecipientsAsString(): string
    {
        return implode(',', $this->approval_recipients);
    }

    /**
     * set approval recipients
     */
    public function setApprovalRecipients(array $a_rcp): void
    {
        $this->approval_recipients = $a_rcp;
    }

    /**
     * Validate settings
     *
     * @return string an string indicating a error or a empty string if no error occured
     */
    public function validate(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        // Cert based authentication
        if ($this->getAuthType() === self::AUTH_CERTIFICATE) {
            if (!$this->getClientCertPath() || !$this->getCACertPath() || !$this->getKeyPath() || !$this->getKeyPassword()) {
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
        if ($this->getAuthType() === self::AUTH_APACHE) {
            if (!$this->getAuthUser() || !$this->getAuthPass()) {
                return self::ERROR_REQUIRED;
            }
        }

        // required fields
        if (!$this->getServer() || !$this->getPort() || !$this->getImportId()
            || !$this->getGlobalRole() || !$this->getDuration()) {
            return self::ERROR_REQUIRED;
        }

        if (!$this->checkImportId()) {
            return self::ERROR_INVALID_IMPORT_ID;
        }
        return '';
    }

    /**
     * check import id
     */
    public function checkImportId(): bool
    {
        if (!$this->getImportId()) {
            return false;
        }
        if ($this->objDataCache->lookupType($this->objDataCache->lookupObjId($this->getImportId())) !== 'cat') {
            return false;
        }
        if ($this->tree->isDeleted($this->getImportId())) {
            return false;
        }
        return true;
    }

    /**
     * save settings
     */
    public function save(): void
    {
        $this->server_id = $this->db->nextId('ecs_server');
        $this->db->manipulate(
            'INSERT INTO ecs_server (server_id,active,title,protocol,server,port,auth_type,client_cert_path,ca_cert_path,' .
            'key_path,key_password,cert_serial,polling_time,import_id,global_role,econtent_rcp,user_rcp,approval_rcp,duration,auth_user,auth_pass) ' .
            'VALUES (' .
            $this->db->quote($this->getServerId(), 'integer') . ', ' .
            $this->db->quote((int) $this->isEnabled(), 'integer') . ', ' .
            $this->db->quote($this->getTitle(), 'text') . ', ' .
            $this->db->quote($this->getProtocol(), 'integer') . ', ' .
            $this->db->quote($this->getServer(), 'text') . ', ' .
            $this->db->quote($this->getPort(), 'integer') . ', ' .
            $this->db->quote($this->getAuthType(), 'integer') . ', ' .
            $this->db->quote($this->getClientCertPath(), 'text') . ', ' .
            $this->db->quote($this->getCACertPath(), 'text') . ', ' .
            $this->db->quote($this->getKeyPath(), 'text') . ', ' .
            $this->db->quote($this->getKeyPassword(), 'text') . ', ' .
            $this->db->quote($this->getCertSerialNumber(), 'text') . ', ' .
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
    public function update(): void
    {
        $this->db->manipulate(
            'UPDATE ecs_server SET ' .
            'server_id = ' . $this->db->quote($this->getServerId(), 'integer') . ', ' .
            'active = ' . $this->db->quote((int) $this->isEnabled(), 'integer') . ', ' .
            'title = ' . $this->db->quote($this->getTitle(), 'text') . ', ' .
            'protocol = ' . $this->db->quote($this->getProtocol(), 'integer') . ', ' .
            'server = ' . $this->db->quote($this->getServer(), 'text') . ', ' .
            'port = ' . $this->db->quote($this->getPort(), 'integer') . ', ' .
            'auth_type = ' . $this->db->quote($this->getAuthType(), 'integer') . ', ' .
            'client_cert_path = ' . $this->db->quote($this->getClientCertPath(), 'text') . ', ' .
            'ca_cert_path = ' . $this->db->quote($this->getCACertPath(), 'text') . ', ' .
            'key_path = ' . $this->db->quote($this->getKeyPath(), 'text') . ', ' .
            'key_password = ' . $this->db->quote($this->getKeyPassword(), 'text') . ', ' .
            'cert_serial = ' . $this->db->quote($this->getCertSerialNumber(), 'text') . ', ' .
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
    public function delete(): bool
    {
        // --- cascading delete
        ilECSCmsData::deleteByServerId($this->getServerId());

        //TODO fix properly
        ilECSCommunityCache::getInstance($this->getServerId(), -1)->deleteByServerId($this->getServerId());

        ilECSDataMappingSettings::getInstanceByServerId($this->getServerId())->delete();

        (new ilECSEventQueueReader($this))->deleteAll();

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

        $this->server_id = 0;
        return true;
    }


    /**
     * Fetch validity (expired date)
     */
    public function fetchCertificateExpiration(): ?ilDateTime
    {
        if ($this->getAuthType() !== self::AUTH_CERTIFICATE) {
            return null;
        }

        if ((function_exists('openssl_x509_parse') &&
                ($cert = openssl_x509_parse('file://' . $this->getClientCertPath())) &&
                        $cert && isset($cert['validTo_time_t'])) && $cert['validTo_time_t']) {
            $dt = new ilDateTime($cert['validTo_time_t'], IL_CAL_UNIX);

            $this->log->debug('Certificate expires at: ' . ilDatePresentation::formatDate($dt));
            return $dt;
        }
        return null;
    }

    /**
     * Fetch serial ID from cert
     */
    private function fetchSerialID(): bool
    {
        if (function_exists('openssl_x509_parse') && ($cert = openssl_x509_parse('file://' . $this->getClientCertPath())) && $cert && isset($cert['serialNumber']) && $cert['serialNumber']) {
            $this->setCertSerialNumber($cert['serialNumber']);
            $this->log->debug('Searial number is: ' . $cert['serialNumber']);
            return true;
        }

        if (!file_exists($this->getClientCertPath()) || !is_readable($this->getClientCertPath())) {
            return false;
        }
        $lines = file($this->getClientCertPath());
        $found = false;
        foreach ($lines as $line) {
            if (strpos($line, 'Serial Number:') !== false) {
                $found = true;
                $serial_line = explode(':', $line);
                $serial = trim($serial_line[1]);
                break;
            }
        }
        if ($found && isset($serial)) {
            $this->setCertSerialNumber($serial);
            return true;
        }
        return false;
    }

    /**
     * Read settings
     */
    private function read(): void
    {
        if (!$this->getServerId()) {
            return;
        }

        $query = 'SELECT * FROM ecs_server ' .
            'WHERE server_id = ' . $this->db->quote($this->getServerId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $this->setServer($row['server']);
            $this->setTitle($row['title']);
            $this->setProtocol((int) $row['protocol']);
            $this->setPort((int) $row['port']);
            $this->setClientCertPath($row['client_cert_path']);
            $this->setCACertPath($row['ca_cert_path']);
            $this->setKeyPath($row['key_path']);
            $this->setKeyPassword($row['key_password']);
            $this->setImportId((int) $row['import_id']);
            $this->setEnabledStatus((bool) $row['active']);
            if ($row['cert_serial']) {
                $this->setCertSerialNumber($row['cert_serial']);
            }
            $this->setGlobalRole((int) $row['global_role']);
            $this->econtent_recipients = explode(',', $row['econtent_rcp']);
            $this->approval_recipients = explode(',', $row['approval_rcp']);
            $this->user_recipients = explode(',', $row['user_rcp']);
            $this->setDuration((int) $row['duration']);
            $this->setAuthUser($row['auth_user']);
            $this->setAuthPass($row['auth_pass']);
            $this->setAuthType((int) $row['auth_type']);
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
