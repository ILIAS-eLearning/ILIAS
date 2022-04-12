<?php declare(strict_types=1);

use ILIAS\Filesystem\Exception\IOException;

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
 * Class ilLTIConsumeProvider
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumeProvider
{
    protected int $id = 0;
    
    protected string $title = '';
    
    protected string $description = '';
    
    protected int $availability = self::AVAILABILITY_NONE;
    const AVAILABILITY_NONE = 0;  // Provider is not longer available (error message)
    const AVAILABILITY_EXISTING = 1; // Existing objects can still use the provider, new objects not
    const AVAILABILITY_CREATE = 2;  // New objects can use this provider
    
    protected string $remarks = '';
    
    protected int $time_to_delete = 0;
    
    protected int $log_level = 0;
    
    protected string $provider_url = '';
    
    protected string $provider_key = '';
    
    protected string $provider_secret = '';
    
    protected bool $provider_key_customizable = true;
    
    protected string $provider_icon_filename = '';
    
    /**
     * @var ilLTIConsumeProviderIcon|null
     */
    protected ?ilLTIConsumeProviderIcon $providerIcon = null;

    /**
     * @var ilImageFileInputGUI|null
     */
    protected ?ilImageFileInputGUI $providerIconUploadInput = null;
    
    const CATEGORY_ASSESSMENT = 'assessment';
    const CATEGORY_FEEDBACK = 'feedback';
    const CATEGORY_CONTENT = 'content';
    const CATEGORY_COMMUNICATION = 'communication';
    const CATEGORY_ORGANISATION = 'organisation';
    /**
     * @var string
     */
    protected string $category = self::CATEGORY_CONTENT;
    
    protected string $provider_xml = '';
    
    protected bool $is_external_provider = false;
    
    //ToDo : necessary?
    const LAUNCH_METHOD_OWN = 'ownWin';
    const LAUNCH_METHOD_NEW = 'newWin';
    protected string $launch_method = self::LAUNCH_METHOD_NEW;
    
    protected bool $has_outcome = false;
    
    protected float $mastery_score = 0.8;
    
    protected bool $keep_lp = false;
    
    const PRIVACY_IDENT_IL_UUID_USER_ID = 0;
    const PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT = 1;
    const PRIVACY_IDENT_IL_UUID_LOGIN = 2;
    const PRIVACY_IDENT_REAL_EMAIL = 3;
    protected int $privacy_ident = self::PRIVACY_IDENT_IL_UUID_USER_ID;
    
    const PRIVACY_NAME_NONE = 0;
    const PRIVACY_NAME_FIRSTNAME = 1;
    const PRIVACY_NAME_LASTNAME = 2;
    const PRIVACY_NAME_FULLNAME = 3;
    protected int $privacy_name = self::PRIVACY_NAME_NONE;
    
    protected bool $include_user_picture = false;
    
    protected string $privacy_comment_default = '';
    
    protected bool $always_learner = false;
    
    protected bool $use_provider_id = false;
    
    protected bool $use_xapi = false;
    
    protected string $xapi_launch_url = '';
    
    protected string $xapi_launch_key = '';

    protected string $xapi_launch_secret = '';
    
    protected string $xapi_activity_id = '';
    
    protected string $custom_params = '';
    
    protected string $keywords = '';

    protected int $creator = 0;
    
    protected int $accepted_by = 0;
    
    protected bool $is_global = false;

    /**
     * ilLTIConsumeProvider constructor.
     * @param int|null $providerId
     * @throws IOException
     */
    public function __construct(?int $providerId = null)
    {
        if ($providerId) {
            $this->setId($providerId);
            $this->load();
        }
    }

    /**
     * Inits class static
     * @param int|null $providerId
     * @return ilLTIConsumeProvider
     * @throws IOException
     */
    public static function getInstance(?int $providerId = null) : ilLTIConsumeProvider
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
    public function setId(int $id) : void
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
    public function setTitle(string $title) : void
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
    public function setDescription(string $description) : void
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
    public function setAvailability(int $availability) : void
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
    public function setRemarks(string $remarks) : void
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
    public function setTimeToDelete(int $time_to_delete) : void
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

    //todo
    /**
     * @param int $log_level
     */
    public function setLogLevel(int $log_level) : void
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
    public function setProviderUrl(string $provider_url) : void
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
    public function setProviderKey(string $provider_key) : void
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
    public function setProviderSecret(string $provider_secret) : void
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
    public function setProviderKeyCustomizable(bool $provider_key_customizable) : void
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
    public function setProviderIconFilename(string $provider_icon_filename) : void
    {
        $this->provider_icon_filename = $provider_icon_filename;
    }
    
    public function getProviderIcon() : ?\ilLTIConsumeProviderIcon
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
        
        return (bool) strlen($this->providerIcon->getFilename());
    }
    
    /**
     * @param ilLTIConsumeProviderIcon $providerIcon
     */
    public function setProviderIcon(ilLTIConsumeProviderIcon $providerIcon) : void
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
    
    public function getProviderIconUploadInput() : ?\ilImageFileInputGUI
    {
        return $this->providerIconUploadInput;
    }
    
    /**
     * @param ilImageFileInputGUI|ilFormPropertyGUI $providerIconUploadInput
     */
    public function setProviderIconUploadInput(ilFormPropertyGUI $providerIconUploadInput) : void
    {
        $this->providerIconUploadInput = $providerIconUploadInput;
    }
    
    /**
     * @return array
     */
    public static function getCategoriesSelectOptions() : array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $categories = [];
        $translation = '';
        
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
    public static function getValidCategories() : array
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
    public function isValidCategory(string $category) : bool
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
    public function setCategory(string $category) : void
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
    public function setProviderXml(string $provider_xml) : void
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
    public function setIsExternalProvider(bool $is_external_provider) : void
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
    public function setLaunchMethod(string $launch_method) : void
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
    public function setHasOutcome(bool $has_outcome) : void
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
    public function setMasteryScore(float $mastery_score) : void
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
    public function setMasteryScorePercent(float $mastery_score_percent) : void
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
    public function setKeepLp(bool $keep_lp) : void
    {
        $this->keep_lp = $keep_lp;
    }
    

    /**
     * @return int
     */
    public function getPrivacyIdent() : int
    {
        return $this->privacy_ident;
    }
    
    /**
     * @param int $privacy_ident
     */
    public function setPrivacyIdent(int $privacy_ident) : void
    {
        $this->privacy_ident = $privacy_ident;
    }

    /**
     * @return int
     */
    public function getPrivacyName() : int
    {
        return $this->privacy_name;
    }
    
    /**
     * @param int $privacy_name
     */
    public function setPrivacyName(int $privacy_name) : void
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
    public function setIncludeUserPicture(bool $include_user_picture) : void
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
    public function setPrivacyCommentDefault(string $privacy_comment_default) : void
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
    public function setAlwaysLearner(bool $always_learner) : void
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
    public function setUseProviderId(bool $use_provider_id) : void
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
    public function setUseXapi(bool $use_xapi) : void
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
    public function setXapiLaunchUrl(string $xapi_launch_url) : void
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
    public function setXapiLaunchKey(string $xapi_launch_key) : void
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
    public function setXapiLaunchSecret(string $xapi_launch_secret) : void
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
    public function setXapiActivityId(string $xapi_activity_id) : void
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
    public function setCustomParams(string $custom_params) : void
    {
        $this->custom_params = $custom_params;
    }
    
    /**
     * @return array
     */
    public function getKeywordsArray() : array
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
    public function setKeywords(string $keywords) : void
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
    public function setCreator(int $creator) : void
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
    public function setAcceptedBy(int $accepted_by) : void
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
    public function setIsGlobal(bool $is_global) : void
    {
        $this->is_global = $is_global;
    }

    /**
     * @param array $dbRow
     * @throws IOException
     */
    public function assignFromDbRow(array $dbRow) : void
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
                case 'privacy_ident': $this->setPrivacyIdent((int) $value); break;
                case 'privacy_name': $this->setPrivacyName((int) $value); break;
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
     * @throws IOException
     */
    public function load() : void
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
     * @throws IOException
     */
    public function save() : void
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
    
    public function update() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->update('lti_ext_provider', $this->getInsertUpdateFields(), array(
                'id' => array('integer', $this->getId()),
        ));
    }
    
    public function insert() : void
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
    
    public function delete() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->manipulateF(
            "DELETE FROM lti_ext_provider WHERE id = %s",
            ['integer'],
            [$this->getId()]
        );
    }
    
    public function isAcceptableAsGlobal() : bool
    {
        return !$this->isGlobal() && (bool) $this->getCreator();
    }
    
    public function isResetableToUserDefined() : bool
    {
        return $this->isGlobal() && (bool) $this->getCreator();
    }
}
