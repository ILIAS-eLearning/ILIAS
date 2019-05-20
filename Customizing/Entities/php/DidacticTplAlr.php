<?php



/**
 * DidacticTplAlr
 */
class DidacticTplAlr
{
    /**
     * @var int
     */
    private $actionId = '0';

    /**
     * @var int|null
     */
    private $roleTemplateId;


    /**
     * Get actionId.
     *
     * @return int
     */
    public function getActionId()
    {
        return $this->actionId;
    }

    /**
     * Set roleTemplateId.
     *
     * @param int|null $roleTemplateId
     *
     * @return DidacticTplAlr
     */
    public function setRoleTemplateId($roleTemplateId = null)
    {
        $this->roleTemplateId = $roleTemplateId;

        return $this;
    }

    /**
     * Get roleTemplateId.
     *
     * @return int|null
     */
    public function getRoleTemplateId()
    {
        return $this->roleTemplateId;
    }
}
