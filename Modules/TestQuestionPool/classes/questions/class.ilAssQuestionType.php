<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssQuestionType
{
    /**
     * @var ilPluginAdmin
     */
    protected $pluginAdmin;
    
    /**
     * @var integer
     */
    protected $id;
    
    /**
     * @var string
     */
    protected $tag;
    
    /**
     * @var bool
     */
    protected $plugin;
    
    /**
     * @var string
     */
    protected $pluginName;
    
    /**
     * ilAssQuestionType constructor.
     */
    public function __construct()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $this->pluginAdmin = $DIC['ilPluginAdmin'];
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
    public function setId($id) : void
    {
        $this->id = $id;
    }
    
    /**
     * @return string
     */
    public function getTag() : string
    {
        return $this->tag;
    }
    
    /**
     * @param string $tag
     */
    public function setTag($tag) : void
    {
        $this->tag = $tag;
    }
    
    /**
     * @return bool
     */
    public function isPlugin() : bool
    {
        return $this->plugin;
    }
    
    /**
     * @param bool $plugin
     */
    public function setPlugin($plugin) : void
    {
        $this->plugin = $plugin;
    }
    
    /**
     * @return string
     */
    public function getPluginName() : string
    {
        return $this->pluginName;
    }
    
    /**
     * @param string $pluginName
     */
    public function setPluginName($pluginName) : void
    {
        $this->pluginName = $pluginName;
    }
    
    /**
     * @return bool
     */
    public function isImportable() : bool
    {
        if (!$this->isPlugin()) {
            return true;
        }
        return false;

        /* Plugins MUST overwrite this method an report back their activation status
        require_once 'Modules/TestQuestionPool/classes/class.ilQuestionsPlugin.php';
        return $this->pluginAdmin->isActive(
            ilComponentInfo::TYPE_MODULES,
            ilQuestionsPlugin::COMP_NAME,
            ilQuestionsPlugin::SLOT_ID,
            $this->getPluginName()
        );
        */
    }
    
    /**
     * @param array $questionTypeData
     * @return array
     */
    public static function completeMissingPluginName($questionTypeData) : array
    {
        if ($questionTypeData['plugin'] && !strlen($questionTypeData['plugin_name'])) {
            $questionTypeData['plugin_name'] = $questionTypeData['type_tag'];
        }

        return $questionTypeData;
    }
}
