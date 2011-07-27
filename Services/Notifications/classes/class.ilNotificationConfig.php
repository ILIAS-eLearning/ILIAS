<?php

class ilNotificationConfig {

    private $type;

    private $link;
    private $linktarget = '_self';
    
    private $title;

    private $iconPath;

    private $short_description;
    private $long_description;

    private $disableAfterDelivery = false;
    private $validForSeconds = 0;

    private $handlerParams = array();

    public function __construct($type) {
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }

    public function setAutoDisable($value) {
        $this->disableAfterDelivery = $value;
    }

    public function hasDisableAfterDeliverySet() {
        return (bool) $this->disableAfterDelivery;
    }

    public function setLink($link) {
        $this->link = $link;
    }

    public function getLink() {
        return $this->link;
    }

    public function setIconPath($path) {
        $this->iconPath = $path;
    }

    public function getIconPath() {
        return $this->iconPath;
    }

    public function setTitleVar($name, $parameters = array(), $language_module = 'notification') {
        $this->title = new ilNotificationParameter($name, $parameters, $language_module);
    }

    public function getTitleVar() {
        return $this->title->getName();
    }

    public function setShortDescriptionVar($name, $parameters = array(), $language_module = 'notification') {
        $this->short_description = new ilNotificationParameter($name, $parameters, $language_module);
    }

    public function getShortDescriptionVar() {
        return $this->short_description->getName();
    }

    public function setLongDescriptionVar($name, $parameters = array(), $language_module = 'notification') {
        $this->long_description = new ilNotificationParameter($name, $parameters, $language_module);
    }

    public function getLongDescriptionVar() {
        return $this->long_description->getName();
    }

    public function getLanguageParameters() {
        return array(
            'title' => $this->title,
            'longDescription' => $this->long_description,
            'shortDescription' => $this->short_description,
        );
    }

    public function getLinktarget() {
        return $this->linktarget;
    }

    public function setLinktarget($linktarget) {
        $this->linktarget = $linktarget;
    }

    public function setValidForSeconds($seconds) {
        $this->validForSeconds = $seconds;
    }

    public function getValidForSeconds() {
        return $this->validForSeconds;
    }

    protected function beforeSendToUsers(){

    }

    protected function afterSendToUsers(){

    }

    protected function beforeSendToListeners(){

    }

    protected function afterSendToListeners(){

    }

    final public function notifyByUsers(array $recipients, $processAsync = false) {
        require_once 'Services/Notifications/classes/class.ilNotificationSystem.php';
        $this->beforeSendToUsers();
        ilNotificationSystem::sendNotificationToUsers($this, $recipients, $processAsync);
        $this->afterSendToUsers();
    }

    final public function notifyByListeners($ref_id, $processAsync = false) {
        require_once 'Services/Notifications/classes/class.ilNotificationSystem.php';
        $this->beforeSendToListeners();
        ilNotificationSystem::sendNotificationToListeners($this, $ref_id, $processAsync);
        $this->afterSendToListeners();
    }

    final public function notifyByRoles(array $roles, $processAsync = false) {
        require_once 'Services/Notifications/classes/class.ilNotificationSystem.php';
        //$this->beforeSendToListeners();
        ilNotificationSystem::sendNotificationToRoles($this, $roles, $processAsync);
        //$this->afterSendToListeners();
    }

    public function getUserInstance(ilObjUser $user, $languageVars, $defaultLanguage) {
        $notificationObject = new ilNotificationObject($this, $user);

        $title = '';
        $short = '';
        $long = '';

        if ($languageVars[$this->title->getName()]->lang[$user->getLanguage()]) {
                $title = $languageVars[$this->title->getName()]->lang[$user->getLanguage()];
        }
        else if ($languageVars[$this->title->getName()]->lang[$defaultLanguage]) {
                $title = $languageVars[$this->title->getName()]->lang[$defaultLanguage];
        }
        else {
            $title = $this->title->getName();
        }

        if ($languageVars[$this->short_description->getName()]->lang[$user->getLanguage()]) {
                $short = $languageVars[$this->short_description->getName()]->lang[$user->getLanguage()];
        }
        else if ($languageVars[$this->short_description->getName()]->lang[$defaultLanguage]) {
                $short = $languageVars[$this->short_description->getName()]->lang[$defaultLanguage];
        }
        else {
            $short = $this->short_description->getName();
        }
 
        if ($languageVars[$this->long_description->getName()]->lang[$user->getLanguage()]) {
                $long = $languageVars[$this->long_description->getName()]->lang[$user->getLanguage()];
        }
        else if ($languageVars[$this->long_description->getName()]->lang[$defaultLanguage]) {
                $long = $languageVars[$this->long_description->getName()]->lang[$defaultLanguage];
        }
        else {
            $long = $this->long_description->getName();
        }

        $notificationObject->title = $title;
        $notificationObject->shortDescription = $short;
        $notificationObject->longDescription = $long;

        $notificationObject->iconPath = $this->iconPath;

        return $notificationObject;
    }

    public function setHandlerParam($name, $value) {
	if (strpos($name, '.')) {
	    $nsParts = explode('.', $name, 2);
	    $ns = $nsParts[0];
	    $field = $nsParts[1];
	    $this->handlerParams[$ns][$field] = $value;
	}
	else {
	    $this->handlerParams[''][$name] = $value;
	}
    }

    public function getHandlerParams() {
	return $this->handlerParams;
    }

    public function unsetHandlerParam($name) {
	unset($this->handlerParams[$name]);
    }
}

class ilNotificationObject {

    /**
     *
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

    public function __construct(ilNotificationConfig $baseNotification, ilObjUser $user) {

        $this->baseNotification = $baseNotification;
        $this->user = $user;

        $this->link = $this->baseNotification->getLink();
        $this->linktarget = $this->baseNotification->getLinktarget();
	$this->handlerParams = $this->baseNotification->getHandlerParams();
    }

    public function __sleep() {
        return array('title', 'shortDescription', 'longDescription', 'iconPath', 'link', 'linktarget', 'handlerParams');
    }

}


class ilNotificationParameter {

    private $name;
    private $parameters = array();
    private $language_module = array();

    public function __construct($name, $parameters = array(), $language_module = 'notification') {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->language_module = $language_module;
    }

    public function getName() {
        return $this->name;
    }

    public function getParameters() {
        return $this->parameters;
    }

    public function getLanguageModule() {
        return $this->language_module;
    }
}