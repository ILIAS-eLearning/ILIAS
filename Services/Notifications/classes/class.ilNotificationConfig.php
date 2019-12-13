<?php

/**
 * Describes a notification and provides methods for publishing this notification
 */
class ilNotificationConfig
{
    const TTL_LONG  = 1800;
    const TTL_SHORT = 120;

    const DEFAULT_TTS = 5;

    /**
     * the type of the notification
     * @var string
     */
    private $type;

    /**
     * a link to send with the notification
     * the notification channel decides what to do with this information
     * e.g. the osd uses the link for linking the message title
     * @var string
     */
    private $link;
    private $linktarget = '_self';

    private $title;

    /**
     * an icon to send with the notification
     * the notification channel decides what to do with this information
     * @var string
     */
    private $iconPath;

    private $short_description;
    private $long_description;

    /**
     * used only for notifications that are sent to listeners
     * if set to true, the listener is disabled after this notification has
     * been processed. this is useful for e.g. forum notifications (that currently
     * do not use the notification system) to disable the listener after a new post
     * has been submitted. the listener can be reactivated if the user enters the
     * forum. the result is that the user will not be flooded with notification,
     * he will only get one.
     * @var boolean
     */
    private $disableAfterDelivery = false;
    /**
     * validity in seconds after the notification will be dismissed from the
     * database
     * @var integer
     */
    private $validForSeconds = 0;

    /**
     * Value in seconds after user interface notification (e.g. OSD) disappear
     * @var int
     */
    protected $visibleForSeconds = 0;

    /**
     * additional parameters to pass to the handlers
     * @var array
     */
    private $handlerParams = array();

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setAutoDisable($value)
    {
        $this->disableAfterDelivery = $value;
    }

    public function hasDisableAfterDeliverySet()
    {
        return (bool) $this->disableAfterDelivery;
    }

    public function setLink($link)
    {
        $this->link = $link;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setIconPath($path)
    {
        $this->iconPath = $path;
    }

    public function getIconPath()
    {
        return $this->iconPath;
    }

    /**
     * Sets the name of the language variable to use as title. The translation may
     * include [NAME] parts wich will be replaced by the matching parameter found
     * in $parameters. The language var is loaded from the language module
     * given as third parameter.
     * Placeholders of type ##name## are deprecated
     * @param type $name
     * @param type $parameters
     * @param type $language_module
     */
    public function setTitleVar($name, $parameters = array(), $language_module = 'notification')
    {
        $this->title = new ilNotificationParameter($name, $parameters, $language_module);
    }

    public function getTitleVar()
    {
        return $this->title->getName();
    }

    /**
     * Sets the name of the language variable to use as short description text. The translation may
     * include [NAME] parts wich will be replaced by the matching parameter found
     * in $parameters. The language var is loaded from the language module
     * given as third parameter.
     * Placeholders of type ##name## are deprecated
     * The channel itself decided if the short description or the long description
     * should be used
     * @param string $name
     * @param array  $parameters
     * @param string $language_module
     */
    public function setShortDescriptionVar($name, $parameters = array(), $language_module = 'notification')
    {
        $this->short_description = new ilNotificationParameter($name, $parameters, $language_module);
    }

    public function getShortDescriptionVar()
    {
        return $this->short_description->getName();
    }

    /**
     * Sets the name of the language variable to use as long description text. The translation may
     * include [name] parts wich will be replaced by the matching parameter found
     * in $parameters. The language var is loaded from the language module
     * given as third parameter.
     * The channel itself decided if the short description or the long description
     * should be used
     * Placeholders of type ##name## are deprecated
     * @param string $name
     * @param array  $parameters
     * @param string $language_module
     */
    public function setLongDescriptionVar($name, $parameters = array(), $language_module = 'notification')
    {
        $this->long_description = new ilNotificationParameter($name, $parameters, $language_module);
    }

    public function getLongDescriptionVar()
    {
        return $this->long_description->getName();
    }

    public function getLanguageParameters()
    {
        return array(
            'title'            => $this->title,
            'longDescription'  => $this->long_description,
            'shortDescription' => $this->short_description,
        );
    }

    public function getLinktarget()
    {
        return $this->linktarget;
    }

    public function setLinktarget($linktarget)
    {
        $this->linktarget = $linktarget;
    }

    public function setValidForSeconds($seconds)
    {
        $this->validForSeconds = $seconds;
    }

    public function getValidForSeconds()
    {
        return $this->validForSeconds;
    }

    /**
     * @return int
     */
    public function getVisibleForSeconds()
    {
        return $this->visibleForSeconds;
    }

    /**
     * @param int $visibleForSeconds
     */
    public function setVisibleForSeconds($visibleForSeconds)
    {
        $this->visibleForSeconds = $visibleForSeconds;
    }

    protected function beforeSendToUsers()
    {
    }

    protected function afterSendToUsers()
    {
    }

    protected function beforeSendToListeners()
    {
    }

    protected function afterSendToListeners()
    {
    }

    /**
     * sends this notification to a list of users
     * @param array $recipients
     */
    final public function notifyByUsers(array $recipients, $processAsync = false)
    {
        require_once 'Services/Notifications/classes/class.ilNotificationSystem.php';
        $this->beforeSendToUsers();
        ilNotificationSystem::sendNotificationToUsers($this, $recipients, $processAsync);
        $this->afterSendToUsers();
    }

    final public function notifyByListeners($ref_id, $processAsync = false)
    {
        require_once 'Services/Notifications/classes/class.ilNotificationSystem.php';
        $this->beforeSendToListeners();
        ilNotificationSystem::sendNotificationToListeners($this, $ref_id, $processAsync);
        $this->afterSendToListeners();
    }

    final public function notifyByRoles(array $roles, $processAsync = false)
    {
        require_once 'Services/Notifications/classes/class.ilNotificationSystem.php';
        //$this->beforeSendToListeners();
        ilNotificationSystem::sendNotificationToRoles($this, $roles, $processAsync);
        //$this->afterSendToListeners();
    }

    public function getUserInstance(ilObjUser $user, $languageVars, $defaultLanguage)
    {
        $notificationObject = new ilNotificationObject($this, $user);

        $title = '';
        $short = '';
        $long  = '';

        if ($languageVars[$this->title->getName()]->lang[$user->getLanguage()]) {
            $title = $languageVars[$this->title->getName()]->lang[$user->getLanguage()];
        } elseif ($languageVars[$this->title->getName()]->lang[$defaultLanguage]) {
            $title = $languageVars[$this->title->getName()]->lang[$defaultLanguage];
        } else {
            $title = $this->title->getName();
        }

        if ($languageVars[$this->short_description->getName()]->lang[$user->getLanguage()]) {
            $short = $languageVars[$this->short_description->getName()]->lang[$user->getLanguage()];
        } elseif ($languageVars[$this->short_description->getName()]->lang[$defaultLanguage]) {
            $short = $languageVars[$this->short_description->getName()]->lang[$defaultLanguage];
        } else {
            $short = $this->short_description->getName();
        }

        if ($languageVars[$this->long_description->getName()]->lang[$user->getLanguage()]) {
            $long = $languageVars[$this->long_description->getName()]->lang[$user->getLanguage()];
        } elseif ($languageVars[$this->long_description->getName()]->lang[$defaultLanguage]) {
            $long = $languageVars[$this->long_description->getName()]->lang[$defaultLanguage];
        } else {
            $long = $this->long_description->getName();
        }

        $notificationObject->title            = $title;
        $notificationObject->shortDescription = $short;
        $notificationObject->longDescription  = $long;

        $notificationObject->iconPath = $this->iconPath;

        return $notificationObject;
    }

    public function setHandlerParam($name, $value)
    {
        if (strpos($name, '.')) {
            $nsParts                          = explode('.', $name, 2);
            $ns                               = $nsParts[0];
            $field                            = $nsParts[1];
            $this->handlerParams[$ns][$field] = $value;
        } else {
            $this->handlerParams[''][$name] = $value;
        }
    }

    public function getHandlerParams()
    {
        return $this->handlerParams;
    }

    public function unsetHandlerParam($name)
    {
        unset($this->handlerParams[$name]);
    }
}

/**
 * A concrete notification based on the ilNotificationConfiguration and returned
 * by ilNotificationConfiguration::getUserInstance
 * For attribute details see ilNotificatoinConfiguration
 */
class ilNotificationObject
{

    /**
     * @var ilNotification
     */
    public $baseNotification;

    /**
     * @var ilObjUser
     */
    public $user;

    public $title;
    public $shortDescription;
    public $longDescription;
    public $link;
    public $linktarget;
    public $iconPath;
    public $handlerParams;

    public function __construct(ilNotificationConfig $baseNotification, ilObjUser $user)
    {
        $this->baseNotification = $baseNotification;
        $this->user             = $user;

        $this->link          = $this->baseNotification->getLink();
        $this->linktarget    = $this->baseNotification->getLinktarget();
        $this->handlerParams = $this->baseNotification->getHandlerParams();
    }

    public function __sleep()
    {
        return array('title', 'shortDescription', 'longDescription', 'iconPath', 'link', 'linktarget', 'handlerParams');
    }
}

/**
 * description of a localized parameter
 * this information is used locate translations while processing notifications
 */
class ilNotificationParameter
{
    private $name;
    private $parameters = array();
    private $language_module = array();

    public function __construct($name, $parameters = array(), $language_module = 'notification')
    {
        $this->name            = $name;
        $this->parameters      = $parameters;
        $this->language_module = $language_module;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getLanguageModule()
    {
        return $this->language_module;
    }
}
