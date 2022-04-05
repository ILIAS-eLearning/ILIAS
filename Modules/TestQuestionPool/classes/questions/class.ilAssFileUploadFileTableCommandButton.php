<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssFileUploadFileTableCommandButton extends ilSubmitButton
{
    /**
     * @var string
     */
    protected $action;
    
    /**
     * ilAssFileUploadFileTableCommandButton constructor.
     */
    public function __construct($buttonType)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        parent::__construct($buttonType);
        $this->lng($DIC['lng']);
    }
    
    /**
     *
     * @return ilLanguage
     */
    public function lng(ilLanguage $lng = null) : ilLanguage
    {
        if ($lng === null) {
            return $this->lng;
        }
        $this->lng = $lng;
        return $lng;
    }
    
    /**
     * @return string
     */
    public function getAction() : string
    {
        return $this->action;
    }
    
    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
    
    public function renderAttributes(array $a_additional_attr = null) : string
    {
        if (is_array($a_additional_attr) && isset($a_additional_attr['name'])) {
            $a_additional_attr['name'] .= "[{$this->getAction()}]";
        }

        return parent::renderAttributes($a_additional_attr); // TODO: Change the autogenerated stub
    }
}
