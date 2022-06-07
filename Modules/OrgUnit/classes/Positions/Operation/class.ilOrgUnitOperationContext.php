<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

/**
 * Class ilOrgUnitOperationContext
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitOperationContext extends ActiveRecord
{
    public const CONTEXT_OBJECT = "object";
    public const CONTEXT_CRS = "crs";
    public const CONTEXT_GRP = "grp";
    public const CONTEXT_IASS = "iass";
    public const CONTEXT_TST = "tst";
    public const CONTEXT_EXC = "exc";
    public const CONTEXT_SVY = "svy";
    public const CONTEXT_USRF = "usrf";
    public const CONTEXT_PRG = "prg";
    public const CONTEXT_ETAL = "etal";

    /**
     * @var array
     */
    public static array $available_contexts = [
        self::CONTEXT_OBJECT,
        self::CONTEXT_CRS,
        self::CONTEXT_GRP,
        self::CONTEXT_IASS,
        self::CONTEXT_TST,
        self::CONTEXT_EXC,
        self::CONTEXT_SVY,
        self::CONTEXT_USRF,
        self::CONTEXT_PRG,
        self::CONTEXT_ETAL,
    ];

    /**
     * @return string[]
     */
    public function getPopulatedContextNames() : array
    {
        $contexts = array($this->getContext());
        $this->appendParentContextName($contexts);

        return $contexts;
    }

    /**
     * @return string[]
     */
    public function getPopulatedContextIds() : array
    {
        $contexts = array($this->getId());
        $this->appendParentContextName($contexts);

        return $contexts;
    }

    /**
     * @var int
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_sequence   true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?int $id = 0;
    /**
     * @var string
     * @con_has_field  true
     * @con_is_unique  true
     * @con_fieldtype  text
     * @con_length     16
     * @con_index      true
     */
    protected string $context = self::CONTEXT_OBJECT;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $parent_context_id = 0;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function setId(?int $id) : void
    {
        $this->id = $id;
    }

    public function getContext() : string
    {
        return $this->context;
    }

    public function setContext(string $context) : void
    {
        $this->context = $context;
    }

    public function getParentContextId() : int
    {
        return $this->parent_context_id;
    }

    public function setParentContextId(int $parent_context_id)
    {
        $this->parent_context_id = $parent_context_id;
    }

     public static function returnDbTableName() : string
    {
        return 'il_orgu_op_contexts';
    }

    public function create() : void
    {
        if (self::where(array('context' => $this->getContext()))->hasSets()) {
            throw new ilException('Context already registered');
        }
        parent::create();
    }

    /**
     * @param string[] $contexts
     */
    private function appendParentContextName(array $contexts) : void
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
     * @param string [] $contexts
     */
    private function appendParentContextId(array $contexts) : void
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
