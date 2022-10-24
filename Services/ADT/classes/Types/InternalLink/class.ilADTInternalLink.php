<?php

declare(strict_types=1);

class ilADTInternalLink extends ilADT
{
    protected ?int $value;

    protected ilTree $tree;

    public function __construct(ilADTDefinition $a_def)
    {
        global $DIC;
        parent::__construct($a_def);

        $this->tree = $DIC->repositoryTree();
    }

    /**
     * @param ilADTDefinition $a_def
     * @return bool
     */
    protected function isValidDefinition(ilADTDefinition $a_def): bool
    {
        return $a_def instanceof ilADTInternalLinkDefinition;
    }

    /**
     * Reset
     */
    public function reset(): void
    {
        parent::reset();
        $this->value = null;
    }

    public function setTargetRefId(?int $a_value): void
    {
        $this->value = $a_value;
    }

    /**
     * @return int|null get target ref_id
     */
    public function getTargetRefId(): ?int
    {
        return $this->value;
    }

    /**
     * @param ilADT $a_adt
     * @return bool
     */
    public function equals(ilADT $a_adt): ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return strcmp($this->getCheckSum(), $a_adt->getCheckSum()) === 0;
        }
        return null;
    }

    public function isLarger(ilADT $a_adt): ?bool
    {
        return null;
    }

    public function isSmaller(ilADT $a_adt): ?bool
    {
        return null;
    }

    /**
     * is null
     * @return bool
     */
    public function isNull(): bool
    {
        return !$this->getTargetRefId();
    }

    public function isValid(): bool
    {
        $valid = parent::isValid();
        if (!$this->isNull()) {
            if (
                !$this->tree->isInTree($this->getTargetRefId()) ||
                $this->tree->isDeleted($this->getTargetRefId())
            ) {
                $valid = false;
                $this->addValidationError(self::ADT_VALIDATION_ERROR_INVALID_NODE);
            }
        }
        return $valid;
    }

    public function getCheckSum(): ?string
    {
        if (!$this->isNull()) {
            return md5((string) $this->getTargetRefId());
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function exportStdClass(): ?stdClass
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->target_ref_id = $this->getTargetRefId();

            return $obj;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function importStdClass(?stdClass $a_std): void
    {
        if (is_object($a_std)) {
            $this->setTargetRefId($a_std->target_ref_id);
        }
    }
}
