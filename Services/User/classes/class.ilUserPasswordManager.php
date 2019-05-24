<?php declare(strict_types=1);
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserPasswordManager
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesUser
 */
class ilUserPasswordManager
{
    /** @var int */
    const MIN_SALT_SIZE = 16;

    /** @var self */
    private static $instance;

    /** @var ilUserPasswordEncoderFactory */
    protected $encoderFactory;

    /** @var string */
    protected $encoderName;

    /** @var array */
    protected $config = [];

    /** @var \ilSetting */
    protected $settings;

    /** @var \ilDBInterface */
    protected $db;

    /**
     * Please use the singleton method for instance creation
     * The constructor is still public because of the unit tests
     * @param array $config
     * @throws ilUserException
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                switch (strtolower($key)) {
                    case 'settings':
                        $this->setSettings($value);
                        break;
                    case 'db':
                        $this->setDb($value);
                        break;
                    case 'password_encoder':
                        $this->setEncoderName($value);
                        break;
                    case 'encoder_factory':
                        $this->setEncoderFactory($value);
                        break;
                }
            }
        }

        if (!$this->getEncoderName()) {
            throw new ilUserException(sprintf('"password_encoder" must be set in %s.', json_encode($config)));
        }

        if (!($this->getEncoderFactory() instanceof ilUserPasswordEncoderFactory)) {
            throw new ilUserException(sprintf(
                '"encoder_factory" must be instance of ilUserPasswordEncoderFactory and set in %s.',
                json_encode($config)
            ));
        }
    }

    /**
     * Single method to reduce footprint (included files, created instances)
     * @return self
     * @throws ilUserException
     */
    public static function getInstance() : self
    {
        global $DIC;

        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $password_manager = new ilUserPasswordManager(
            [
                'encoder_factory'  => new ilUserPasswordEncoderFactory(
                    [
                        'default_password_encoder' => 'bcryptphp',
                        'ignore_security_flaw'     => true,
                        'data_directory'           => ilUtil::getDataDir()
                    ]
                ),
                'password_encoder' => 'bcryptphp',
                'settings'         => $DIC->settings(),
                'db'               => $DIC->database(),
            ]
        );

        self::$instance = $password_manager;
        return self::$instance;
    }

    /**
     * @param ilSetting $settings
     */
    public function setSettings(ilSetting $settings) : void
    {
        $this->settings = $settings;
    }

    /**
     * @param ilDBInterface $db
     */
    public function setDb(ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    /**
     * @return string
     */
    public function getEncoderName() :? string
    {
        return $this->encoderName;
    }

    /**
     * @param string $encoderName
     */
    public function setEncoderName(string $encoderName) : void
    {
        $this->encoderName = $encoderName;
    }

    /**
     * @return ilUserPasswordEncoderFactory
     */
    public function getEncoderFactory() :? ilUserPasswordEncoderFactory
    {
        return $this->encoderFactory;
    }

    /**
     * @param ilUserPasswordEncoderFactory $encoderFactory
     */
    public function setEncoderFactory(ilUserPasswordEncoderFactory $encoderFactory) : void
    {
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @param ilObjUser $user
     * @param string    $raw The raw password
     * @throws ilUserException
     */
    public function encodePassword(ilObjUser $user, string $raw) : void
    {
        $encoder = $this->getEncoderFactory()->getEncoderByName($this->getEncoderName());
        $user->setPasswordEncodingType($encoder->getName());
        if ($encoder->requiresSalt()) {
            $user->setPasswordSalt(
                substr(str_replace('+', '.', base64_encode(ilPasswordUtils::getBytes(self::MIN_SALT_SIZE))), 0, 22)
            );
        } else {
            $user->setPasswordSalt(null);
        }
        $user->setPasswd($encoder->encodePassword($raw, (string) $user->getPasswordSalt()), IL_PASSWD_CRYPTED);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isEncodingTypeSupported(string $name) : bool
    {
        return in_array($name, $this->getEncoderFactory()->getSupportedEncoderNames());
    }

    /**
     * @param ilObjUser $user
     * @param string    $raw
     * @return bool
     * @throws ilUserException
     */
    public function verifyPassword(ilObjUser $user, string $raw) : bool
    {
        $encoder = $this->getEncoderFactory()->getEncoderByName($user->getPasswordEncodingType(), true);
        if ($this->getEncoderName() != $encoder->getName()) {
            if ($encoder->isPasswordValid((string) $user->getPasswd(), $raw, $user->getPasswordSalt())) {
                $user->resetPassword($raw, $raw);
                return true;
            }
        } elseif ($encoder->isPasswordValid((string) $user->getPasswd(), $raw, $user->getPasswordSalt())) {
            if ($encoder->requiresReencoding((string) $user->getPasswd())) {
                $user->resetPassword($raw, $raw);
            }

            return true;
        }

        return false;
    }

    /**
     *
     */
    public function resetLastPasswordChangeForLocalUsers() : void
    {
        $defaultAuthMode          = $this->settings->get('auth_mode');
        $defaultAuthModeCondition = '';
        if ((int) $defaultAuthMode === (int) AUTH_LOCAL) {
            $defaultAuthModeCondition = ' OR auth_mode = ' . $this->db->quote('default', 'text');
        }

        $this->db->manipulateF("
			UPDATE usr_data
			SET passwd_policy_reset = %s
			WHERE (auth_mode = %s $defaultAuthModeCondition)",
            ['integer', 'text'],
            [1, 'local']
        );
    }
} 