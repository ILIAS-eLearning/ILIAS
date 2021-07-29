<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLTIConsumeProvider
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumeProvider
{
    protected $id = 0;
    
    protected $title = '';
    
    protected $description = '';
    
    protected $availability = self::AVAILABILITY_NONE;
    const AVAILABILITY_NONE = 0;  // Provider is not longer available (error message)
    const AVAILABILITY_EXISTING = 1; // Existing objects can still use the provider, new objects not
    const AVAILABILITY_CREATE = 2;  // New objects can use this provider
    
    protected $remarks = '';
    
    protected $time_to_delete = 0;
    
    protected $log_level = 0;
    
    protected $provider_url = '';
    
    protected $provider_key = '';
    
    protected $provider_secret = '';
    
    protected $provider_key_customizable = true;
    
    protected $provider_icon_filename = '';
    
    /**
     * @var ilLTIConsumeProviderIcon
     */
    protected $providerIcon = null;

    /**
     * @var ilImageFileInputGUI
     */
    protected $providerIconUploadInput = null;
    
    const CATEGORY_ASSESSMENT = 'assessment';
    const CATEGORY_FEEDBACK = 'feedback';
    const CATEGORY_CONTENT = 'content';
    const CATEGORY_COMMUNICATION = 'communication';
    const CATEGORY_ORGANISATION = 'organisation';
    /**
     * @var string
     */
    protected $category = self::CATEGORY_CONTENT;
    
    protected $provider_xml = '';
    
    protected $is_external_provider = false;
    
    //ToDo : necessary?
    const LAUNCH_METHOD_OWN = 'ownWin';
    const LAUNCH_METHOD_NEW = 'newWin';
    protected $launch_method = self::LAUNCH_METHOD_NEW;
    
    protected $has_outcome = false;
    
    protected $mastery_score = 0.8;
    
    protected $keep_lp = false;
    
    const PRIVACY_IDENT_IL_UUID_USER_ID = 0;
    const PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT = 1;
    const PRIVACY_IDENT_IL_UUID_LOGIN = 2;
    const PRIVACY_IDENT_REAL_EMAIL = 3;
    protected $privacy_ident = self::PRIVACY_IDENT_IL_UUID_USER_ID;
    
    const PRIVACY_NAME_NONE = 0;
    const PRIVACY_NAME_FIRSTNAME = 1;
    const PRIVACY_NAME_LASTNAME = 2;
    const PRIVACY_NAME_FULLNAME = 3;
    protected $privacy_name = self::PRIVACY_NAME_NONE;
    
    /**
     * @var bool
     */
    protected $include_user_picture = false;
    
    protected $privacy_comment_default = '';
    
    protected $always_learner = false;
    
    protected $use_provider_id = false;
    
    protected $use_xapi = false;
    
    protected $xapi_launch_url = '';
    
    protected $xapi_launch_key = '';

    protected $xapi_launch_secret = '';
    
    protected $xapi_activity_id = '';
    
    protected $custom_params = '';
    
    protected $keywords = '';

    protected $creator = 0;
    
    protected $accepted_by = 0;
    
    protected $is_global = false;
    
    /**
     * ilLTIConsumeProvider constructor.
     * @param null $providerId
     */
    public function __construct($providerId = null)
    {
        if ($providerId) {
            $this->setId($providerId);
            $this->load();
        }
    }

    /**
     * Inits class static
     * @param null $providerId
     * @return ilLTIConsumeProvider
     */
    public static function getInstance($providerId = null)
    {
        return new self($providerId);
    }
    
    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }
    
    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
    
    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }
    
    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
    
    /**
     * @return int
     */
    public function getAvailability() : int
    {
        return $this->availability;
    }
    
    /**
     * @param int $availability
     */
    public function setAvailability(int $availability)
    {
        $this->availability = $availability;
    }
    
    /**
     * @return string
     */
    public function getRemarks() : string
    {
        return $this->remarks;
    }
    
    /**
     * @param string $remarks
     */
    public function setRemarks(string $remarks)
    {
        $this->remarks = $remarks;
    }
    
    /**
     * @return int
     */
    public function getTimeToDelete() : int
    {
        return $this->time_to_delete;
    }
    
    /**
     * @param int $time_to_delete
     */
    public function setTimeToDelete(int $time_to_delete)
    {
        $this->time_to_delete = $time_to_delete;
    }
    
    /**
     * @return int
     */
    public function getLogLevel() : int
    {
        return $this->log_level;
    }
    
    /**
     * @param int $log_level
     */
    public function setLogLevel(int $log_level)
    {
        $this->log_level = $log_level;
    }
    
    /**
     * @return string
     */
    public function getProviderUrl() : string
    {
        return $this->provider_url;
    }
    
    /**
     * @param string $provider_url
     */
    public function setProviderUrl(string $provider_url)
    {
        $this->provider_url = $provider_url;
    }
    
    /**
     * @return string
     */
    public function getProviderKey() : string
    {
        return $this->provider_key;
    }
    
    /**
     * @param string $provider_key
     */
    public function setProviderKey(string $provider_key)
    {
        $this->provider_key = $provider_key;
    }
    
    /**
     * @return string
     */
    public function getProviderSecret() : string
    {
        return $this->provider_secret;
    }
    
    /**
     * @param string $provider_secret
     */
    public function setProviderSecret(string $provider_secret)
    {
        $this->provider_secret = $provider_secret;
    }
    
    /**
     * @return bool
     */
    public function isProviderKeyCustomizable() : bool
    {
        return $this->provider_key_customizable;
    }
    
    /**
     * @param bool $provider_key_customizable
     */
    public function setProviderKeyCustomizable(bool $provider_key_customizable)
    {
        $this->provider_key_customizable = $provider_key_customizable;
    }
    
    /**
     * @return string
     */
    public function getProviderIconFilename() : string
    {
        return $this->provider_icon_filename;
    }
    
    /**
     * @param string $provider_icon_filename
     */
    public function setProviderIconFilename(string $provider_icon_filename)
    {
        $this->provider_icon_filename = $provider_icon_filename;
    }
    
    /**
     * @return ilLTIConsumeProviderIcon
     */
    public function getProviderIcon() : ilLTIConsumeProviderIcon
    {
        return $this->providerIcon;
    }
    
    /**
     * @return bool
     */
    public function hasProviderIcon() : bool
    {
        if (!($this->providerIcon instanceof ilLTIConsumeProviderIcon)) {
            return false;
        }
        
        return strlen($this->providerIcon->getFilename());
    }
    
    /**
     * @param ilLTIConsumeProviderIcon $providerIcon
     */
    public function setProviderIcon(ilLTIConsumeProviderIcon $providerIcon)
    {
        $this->providerIcon = $providerIcon;
    }
    
    /**
     * @return bool
     */
    public function hasProviderIconUploadInput() : bool
    {
        return $this->providerIconUploadInput instanceof ilImageFileInputGUI;
    }
    
    /**
     * @return ilImageFileInputGUI
     */
    public function getProviderIconUploadInput() : ilImageFileInputGUI
    {
        return $this->providerIconUploadInput;
    }
    
    /**
     * @param ilImageFileInputGUI $providerIconUploadInput
     */
    public function setProviderIconUploadInput(ilImageFileInputGUI $providerIconUploadInput)
    {
        $this->providerIconUploadInput = $providerIconUploadInput;
    }
    
    /**
     * @return array
     */
    public static function getCategoriesSelectOptions()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $categories = [];
        
        foreach (self::getValidCategories() as $category) {
            switch ($category) {
                case self::CATEGORY_ORGANISATION:
                    
                    $translation = $DIC->language()->txt('rep_add_new_def_grp_organisation');
                    break;
                case self::CATEGORY_COMMUNICATION:
                    
                    $translation = $DIC->language()->txt('rep_add_new_def_grp_communication');
                    break;
                case self::CATEGORY_CONTENT:
                    
                    $translation = $DIC->language()->txt('rep_add_new_def_grp_content');
                    break;
                case self::CATEGORY_ASSESSMENT:
                    
                    $translation = $DIC->language()->txt('rep_add_new_def_grp_assessment');
                    break;
                case self::CATEGORY_FEEDBACK:
                    
                    $translation = $DIC->language()->txt('rep_add_new_def_grp_feedback');
                    break;
            }
            
            $categories[$category] = $translation;
        }
        
        return $categories;
    }
    
    /**
     * @return array
     */
    public static function getValidCategories()
    {
        return [
            self::CATEGORY_ORGANISATION,
            self::CATEGORY_COMMUNICATION,
            self::CATEGORY_CONTENT,
            self::CATEGORY_ASSESSMENT,
            self::CATEGORY_FEEDBACK
        ];
    }
    
    /**
     * @param string $category
     * @return bool
     */
    public function isValidCategory(string $category)
    {
        return in_array($category, self::getValidCategories());
    }
    
    /**
     * @return string
     */
    public function getCategory() : string
    {
        return $this->category;
    }
    
    /**
     * @param string $category
     */
    public function setCategory(string $category)
    {
        $this->category = $category;
    }
    
    /**
     * @return string
     */
    public function getProviderXml() : string
    {
        return $this->provider_xml;
    }
    
    /**
     * @param string $provider_xml
     */
    public function setProviderXml(string $provider_xml)
    {
        $this->provider_xml = $provider_xml;
    }
    
    /**
     * @return bool
     */
    public function isExternalProvider() : bool
    {
        return $this->is_external_provider;
    }
    
    /**
     * @param bool $is_external_provider
     */
    public function setIsExternalProvider(bool $is_external_provider)
    {
        $this->is_external_provider = $is_external_provider;
    }
    
    /**
     * @return string
     */
    public function getLaunchMethod() : string
    {
        return $this->launch_method;
    }
    
    /**
     * @param string $launch_method
     */
    public function setLaunchMethod(string $launch_method)
    {
        $this->launch_method = $launch_method;
    }
    
    /**
     * @return bool
     */
    public function getHasOutcome() : bool
    {
        return $this->has_outcome;
    }
    
    /**
     * @param bool $has_outcome
     */
    public function setHasOutcome(bool $has_outcome)
    {
        $this->has_outcome = $has_outcome;
    }
    
    /**
     * @return float
     */
    public function getMasteryScore() : float
    {
        return $this->mastery_score;
    }
    
    /**
     * @param float $mastery_score
     */
    public function setMasteryScore(float $mastery_score)
    {
        $this->mastery_score = $mastery_score;
    }
    
    /**
     * @return float
     */
    public function getMasteryScorePercent() : float
    {
        return $this->mastery_score * 100;
    }
    
    /**
     * @param float $mastery_score_percent
     */
    public function setMasteryScorePercent(float $mastery_score_percent)
    {
        $this->mastery_score = $mastery_score_percent / 100;
    }

    /**
     * @return bool
     */
    public function isKeepLp() : bool
    {
        return $this->keep_lp;
    }
    
    /**
     * @param bool $keep_lp
     */
    public function setKeepLp(bool $keep_lp)
    {
        $this->keep_lp = $keep_lp;
    }
    

    /**
     * @return mixed
     */
    public function getPrivacyIdent()
    {
        return $this->privacy_ident;
    }
    
    /**
     * @param mixed $privacy_ident
     */
    public function setPrivacyIdent($privacy_ident)
    {
        $this->privacy_ident = $privacy_ident;
    }

    /**
     * @return string
     */
    public function getPrivacyName() : string
    {
        return $this->privacy_name;
    }
    
    /**
     * @param string $privacy_name
     */
    public function setPrivacyName(string $privacy_name)
    {
        $this->privacy_name = $privacy_name;
    }
    
    /**
     * @return bool
     */
    public function getIncludeUserPicture() : bool
    {
        return $this->include_user_picture;
    }
    
    /**
     * @param bool $include_user_picture
     */
    public function setIncludeUserPicture(bool $include_user_picture)
    {
        $this->include_user_picture = $include_user_picture;
    }

    /**
     * @return string
     */
    public function getPrivacyCommentDefault() : string
    {
        return $this->privacy_comment_default;
    }
    
    /**
     * @param string $privacy_comment_default
     */
    public function setPrivacyCommentDefault(string $privacy_comment_default)
    {
        $this->privacy_comment_default = $privacy_comment_default;
    }
    
    /**
     * @return bool
     */
    public function getAlwaysLearner() : bool
    {
        return $this->always_learner;
    }
    
    /**
     * @param bool $always_learner
     */
    public function setAlwaysLearner(bool $always_learner)
    {
        $this->always_learner = $always_learner;
    }
    
    /**
     * @return bool
     */
    public function getUseProviderId() : bool
    {
        return $this->use_provider_id;
    }
    
    /**
     * @param bool $use_provider_id
     */
    public function setUseProviderId(bool $use_provider_id)
    {
        $this->use_provider_id = $use_provider_id;
    }
    
    /**
     * @return bool
     */
    public function getUseXapi() : bool
    {
        return $this->use_xapi;
    }
    
    /**
     * @param bool $use_xapi
     */
    public function setUseXapi(bool $use_xapi)
    {
        $this->use_xapi = $use_xapi;
    }
    
    /**
     * @return string
     */
    public function getXapiLaunchUrl() : string
    {
        return $this->xapi_launch_url;
    }
    
    /**
     * @param string $xapi_launch_url
     */
    public function setXapiLaunchUrl(string $xapi_launch_url)
    {
        $this->xapi_launch_url = $xapi_launch_url;
    }
    
    /**
     * @return string
     */
    public function getXapiLaunchKey() : string
    {
        return $this->xapi_launch_key;
    }
    
    /**
     * @param string $xapi_launch_key
     */
    public function setXapiLaunchKey(string $xapi_launch_key)
    {
        $this->xapi_launch_key = $xapi_launch_key;
    }
    
    /**
     * @return string
     */
    public function getXapiLaunchSecret() : string
    {
        return $this->xapi_launch_secret;
    }
    
    /**
     * @param string $xapi_launch_secret
     */
    public function setXapiLaunchSecret(string $xapi_launch_secret)
    {
        $this->xapi_launch_secret = $xapi_launch_secret;
    }
    
    /**
     * @return string
     */
    public function getXapiActivityId() : string
    {
        return $this->xapi_activity_id;
    }
    
    /**
     * @param string $xapi_activity_id
     */
    public function setXapiActivityId(string $xapi_activity_id)
    {
        $this->xapi_activity_id = $xapi_activity_id;
    }
    
    /**
     * @return string
     */
    public function getCustomParams() : string
    {
        return $this->custom_params;
    }
    
    /**
     * @param string $custom_params
     */
    public function setCustomParams(string $custom_params)
    {
        $this->custom_params = $custom_params;
    }
    
    /**
     * @return array
     */
    public function getKeywordsArray()
    {
        $keywords = [];
        
        foreach (explode(';', $this->getKeywords()) as $keyword) {
            $keywords[] = trim($keyword);
        }
        
        return $keywords;
    }
    
    /**
     * @return string
     */
    public function getKeywords() : string
    {
        return $this->keywords;
    }
    
    /**
     * @param string $keywords
     */
    public function setKeywords(string $keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @return int
     */
    public function getCreator() : int
    {
        return $this->creator;
    }
    
    /**
     * @param int $creator
     */
    public function setCreator(int $creator)
    {
        $this->creator = $creator;
    }
    
    /**
     * @return int
     */
    public function getAcceptedBy() : int
    {
        return $this->accepted_by;
    }
    
    /**
     * @param int $accepted_by
     */
    public function setAcceptedBy(int $accepted_by)
    {
        $this->accepted_by = $accepted_by;
    }
    
    /**
     * @return bool
     */
    public function isGlobal() : bool
    {
        return $this->is_global;
    }
    
    /**
     * @param bool $is_global
     */
    public function setIsGlobal(bool $is_global)
    {
        $this->is_global = $is_global;
    }
    
    /**
     * @param $dbRow
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function assignFromDbRow($dbRow)
    {
        foreach ($dbRow as $field => $value) {
            switch ($field) {
                case 'id': $this->setId((int) $value); break;
                case 'title': $this->setTitle($value); break;
                case 'description': $this->setDescription($value); break;
                case 'availability': $this->setAvailability((int) $value); break;
                case 'remarks': $this->setRemarks($value); break;
                case 'time_to_delete': $this->setTimeToDelete((int) $value); break;
                case 'log_level': $this->setLogLevel((int) $value); break;
                case 'provider_url': $this->setProviderUrl($value); break;
                case 'provider_key': $this->setProviderKey($value); break;
                case 'provider_secret': $this->setProviderSecret($value); break;
                case 'provider_key_customizable': $this->setProviderKeyCustomizable((bool) $value); break;
                case 'provider_icon': $this->setProviderIconFilename($value); break;
                case 'category': $this->setCategory($value); break;
                case 'provider_xml': $this->setProviderXml($value); break;
                case 'external_provider': $this->setIsExternalProvider((bool) $value); break;
                case 'launch_method': $this->setLaunchMethod($value); break;
                case 'has_outcome': $this->setHasOutcome((bool) $value); break;
                case 'mastery_score': $this->setMasteryScore((float) $value); break;
                case 'keep_lp': $this->setKeepLp((bool) $value); break;
                case 'privacy_ident': $this->setPrivacyIdent($value); break;
                case 'privacy_name': $this->setPrivacyName($value); break;
                case 'inc_usr_pic': $this->setIncludeUserPicture((bool) $value); break;
                case 'privacy_comment_default': $this->setPrivacyCommentDefault($value); break;
                case 'always_learner': $this->setAlwaysLearner((bool) $value); break;
                case 'use_provider_id': $this->setUseProviderId((bool) $value); break;
                case 'use_xapi': $this->setUseXapi((bool) $value); break;
                case 'xapi_launch_url': $this->setXapiLaunchUrl((string) $value); break;
                case 'xapi_launch_key': $this->setXapiLaunchKey((string) $value); break;
                case 'xapi_launch_secret': $this->setXapiLaunchSecret((string) $value); break;
                case 'xapi_activity_id': $this->setXapiActivityId((string) $value); break;
                case 'custom_params': $this->setCustomParams((string) $value); break;
                case 'keywords': $this->setKeywords((string) $value); break;
                case 'creator': $this->setCreator((int) $value); break;
                case 'accepted_by': $this->setAcceptedBy((int) $value); break;
                case 'global': $this->setIsGlobal((bool) $value); break;
            }
        }
        
        $this->setProviderIcon(new ilLTIConsumeProviderIcon($this->getId()));
        $this->getProviderIcon()->setFilename($this->getProviderIconFilename());
    }
    
    /**
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function load()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $query = "SELECT * FROM lti_ext_provider WHERE id = %s";
        $res = $DIC->database()->queryF($query, array('integer'), array($this->getId()));
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $this->assignFromDbRow($row);
        }
    }
    
    /**
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function save()
    {
        if ($this->getId()) {
            if ($this->hasProviderIconUploadInput()) {
                $this->getProviderIcon()->handleUploadInputSubission($this->getProviderIconUploadInput());
                $this->setProviderIconFilename($this->getProviderIcon()->getFilename());
            }
            
            $this->update();
        } else {
            $this->insert();
            
            if ($this->hasProviderIconUploadInput()) {
                $this->setProviderIcon(new ilLTIConsumeProviderIcon($this->getId()));
                
                $this->getProviderIcon()->handleUploadInputSubission($this->getProviderIconUploadInput());
                $this->setProviderIconFilename($this->getProviderIcon()->getFilename());
                
                $this->update();
            }
        }
    }
    
    public function update()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->update('lti_ext_provider', $this->getInsertUpdateFields(), array(
                'id' => array('integer', $this->getId()),
        ));
    }
    
    public function insert()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $this->setId($DIC->database()->nextId('lti_ext_provider'));
        
        $DIC->database()->insert('lti_ext_provider', $this->getInsertUpdateFields());
    }
    
    /**
     * @return array
     */
    protected function getInsertUpdateFields() : array
    {
        return array(
            'id' => array('integer', $this->getId()),
            'title' => array('text', $this->getTitle()),
            'description' => array('text', $this->getDescription()),
            'availability' => array('integer', $this->getAvailability()),
            'remarks' => array('text', $this->getRemarks()),
            'time_to_delete' => array('integer', $this->getTimeToDelete()),
            'provider_url' => array('text', $this->getProviderUrl()),
            'provider_key' => array('text', $this->getProviderKey()),
            'provider_secret' => array('text', $this->getProviderSecret()),
            'provider_key_customizable' => array('integer', $this->isProviderKeyCustomizable()),
            'provider_icon' => array('text', $this->getProviderIconFilename()),
            'category' => array('text', $this->getCategory()),
            'provider_xml' => array('text', $this->getProviderXml()),
            'external_provider' => array('integer', $this->isExternalProvider()),
            'launch_method' => array('text', $this->getLaunchMethod()),
            'has_outcome' => array('integer', $this->getHasOutcome()),
            'mastery_score' => array('float', $this->getMasteryScore()),
            'keep_lp' => array('integer', $this->isKeepLp()),
            'privacy_ident' => array('integer', $this->getPrivacyIdent()),
            'privacy_name' => array('integer', $this->getPrivacyName()),
            'inc_usr_pic' => array('integer', $this->getIncludeUserPicture()),
            'privacy_comment_default' => array('text', $this->getPrivacyCommentDefault()),
            'always_learner' => array('integer', $this->getAlwaysLearner()),
            'use_provider_id' => array('integer', $this->getUseProviderId()),
            'use_xapi' => array('integer', $this->getUseXapi()),
            'xapi_launch_url' => array('text', $this->getXapiLaunchUrl()),
            'xapi_launch_key' => array('text', $this->getXapiLaunchKey()),
            'xapi_launch_secret' => array('text', $this->getXapiLaunchSecret()),
            'xapi_activity_id' => array('text', $this->getXapiActivityId()),
            'custom_params' => array('text', $this->getCustomParams()),
            'keywords' => array('text', $this->getKeywords()),
            'creator' => array('integer', $this->getCreator()),
            'accepted_by' => array('integer', $this->getAcceptedBy()),
            'global' => array('integer', (int) $this->isGlobal())
        );
    }
    
    public function delete()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->manipulateF(
            "DELETE FROM lti_ext_provider WHERE id = %s",
            ['integer'],
            [$this->getId()]
        );
    }
    
    public function isAcceptableAsGlobal()
    {
        return !$this->isGlobal() && (bool) $this->getCreator();
    }
    
    public function isResetableToUserDefined()
    {
        return $this->isGlobal() && (bool) $this->getCreator();
    }
}
