<?php

/**
 * Class ilOrgUnitOperationContext
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitOperationContext extends ActiveRecord
{
    const CONTEXT_OBJECT = "object";
    const CONTEXT_CRS = "crs";
    const CONTEXT_GRP = "grp";
    const CONTEXT_IASS = "iass";
    const CONTEXT_TST = "tst";
    const CONTEXT_EXC = "exc";
    const CONTEXT_SVY = "svy";


    /**
     * @return array if own and
     */
    public function getPopulatedContextNames()
    {
        $contexts = array( $this->getContext() );
        $this->appendParentContextName($contexts);

        return $contexts;
    }


    /**
     * @return array if own and
     */
    public function getPopulatedContextIds()
    {
        $contexts = array( $this->getId() );
        $this->appendParentContextName($contexts);

        return $contexts;
    }


    /**
     * @var int
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_sequence   true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $id = 0;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_is_unique  true
     * @con_fieldtype  text
     * @con_length     16
     * @con_index      true
     */
    protected $context = self::CONTEXT_OBJECT;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $parent_context_id = 0;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }


    /**
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }


    /**
     * @return int
     */
    public function getParentContextId()
    {
        return $this->parent_context_id;
    }


    /**
     * @param int $parent_context_id
     */
    public function setParentContextId($parent_context_id)
    {
        $this->parent_context_id = $parent_context_id;
    }


    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return 'il_orgu_op_contexts';
    }


    public function create()
    {
        if (self::where(array( 'context' => $this->getContext() ))->hasSets()) {
            throw new ilException('Context already registered');
        }
        parent::create();
    }


    /**
     * @param $contexts
     */
    protected function appendParentContextName(&$contexts)
    {
        if ($this->getParentContextId()) {
            /**
             * @var $parent self
             */
            $parent = self::find($this->getParentContextId());
            if ($parent) {
                $contexts[] = $parent->getContext();
                $parent->appendParentContextName($contexts);
            }
        }
    }


    /**
     * @param $contexts
     */
    protected function appendParentContextId(&$contexts)
    {
        if ($this->getParentContextId()) {
            /**
             * @var $parent self
             */
            $parent = self::find($this->getParentContextId());
            if ($parent) {
                $contexts[] = $parent->getId();
                $parent->appendParentContextName($contexts);
            }
        }
    }
}
