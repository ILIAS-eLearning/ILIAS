<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilActivity.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilNode.php';

/**
 * Class ilSettingActivity
 *
 * This activity sets a given setting to the $ilSetting object. Design consideration
 * is to configure this object during workflow creation, since this is called
 * only under predictable circumstances.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilSettingActivity implements ilActivity, ilWorkflowEngineElement
{
    /** @var ilWorkflowEngineElement $context Holds a reference to the parent object */
    private $context;

    /**
     * Holds the name of the setting to be used by this activity.
     *
     * @todo Check for constraints imposed by ilSetting.
     *
     * @var string Name of a setting, $ilSetting constraints are in effect.
     */
    private string $setting_name = '';

    /**
     * Holds the value of the setting to be used by this activity.
     *
     * @todo Check for constraints imposed by ilSetting.
     *
     * @var string Value of a setting, $ilSetting constraints are in effect.
     */
    private string $setting_value = '';

    /** @var string $name */
    protected string $name;

    /**
     * Default constructor.
     *
     * @param ilNode $context
     */
    public function __construct(ilNode $context)
    {
        $this->context = $context;
    }

    /**
     * Sets the name of the setting to be written to.
     * @param string $name Name of the setting.
     * @return void
     *@see $setting_name
     */
    public function setSettingName(string $name)
    {
        $this->setting_name = $name;
    }

    /**
     * Returns the name of the setting to be written to.
     *
     * @see $setting_name
     *
     * @return string
     */
    public function getSettingName() : string
    {
        return $this->setting_name;
    }

    /***
     * Sets the value of the setting.
     * @param string $value Value to be set.
     * @return void
     *@see $setting_value
     */
    public function setSettingValue(string $value)
    {
        $this->setting_value = $value;
    }

    /**
     * Returns the value of the setting to be set.
     *
     * @see $setting_value
     *
     * @return string
     */
    public function getSettingValue() : string
    {
        return $this->setting_value;
    }

    /**
     * Sets the setting name and value for this activity.
     * @param string $name  Name of the setting.
     * @param string $value Value to be set.
     * @return void
     */
    public function setSetting(string $name, string $value)
    {
        $this->setSettingName($name);
        $this->setSettingValue($value);
    }

    /**
     * Executes this action according to its settings.
     * @return void
     *@todo Use exceptions / internal logging.
     */
    public function execute() : void
    {
        global $DIC;
        /** @var ilSetting $ilSetting */
        $ilSetting = $DIC['ilSetting'];

        $ilSetting->set($this->setting_name, $this->setting_value);
    }

    /**
     * Returns a reference to the parent node.
     *
     * @return ilNode Reference to the parent node.
     */
    public function getContext()
    {
        return $this->context;
    }

    public function setName($name) : void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
}
