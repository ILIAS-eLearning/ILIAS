<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT base class
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesADT
 */
abstract class ilADT
{
    protected ilADTDefinition $definition;

    protected ilLanguage $lng;

    protected array $validation_errors = []; // [array]

    public const ADT_VALIDATION_ERROR_NULL_NOT_ALLOWED = "adt1";

    // text-based
    public const ADT_VALIDATION_ERROR_MAX_LENGTH = "adt2";

    // multi
    public const ADT_VALIDATION_ERROR_MAX_SIZE = "adt3";

    // number-based
    public const ADT_VALIDATION_ERROR_MIN = "adt4";
    public const ADT_VALIDATION_ERROR_MAX = "adt5";

    // date-based
    public const ADT_VALIDATION_DATE = "adt6";

    // invalid target node for internal link
    public const ADT_VALIDATION_ERROR_INVALID_NODE = 'adt7';

    public function __construct(ilADTDefinition $a_def)
    {
        global $DIC;
        $this->setDefinition($a_def);
        $this->reset();

        $this->lng = $DIC->language();
    }

    /**
     * Get type (from class/instance)
     * @return string
     */
    public function getType(): string
    {
        return $this->getDefinition()->getType();
    }

    /**
     * Init property defaults
     */
    public function reset(): void
    {
    }


    //
    // definition
    //

    /**
     * Check if definition is valid for ADT
     * @param ilADTDefinition $a_def
     * @return bool
     */
    abstract protected function isValidDefinition(ilADTDefinition $a_def): bool;

    /**
     * Set definition
     * @param ilADTDefinition $a_def
     * @throws ilException
     */
    protected function setDefinition(ilADTDefinition $a_def): void
    {
        if ($this->isValidDefinition($a_def)) {
            $this->definition = clone $a_def;
        } else {
            throw new ilException("ilADT invalid definition");
        }
    }

    /**
     * Get definition
     * @return ilADTDefinition $a_def
     */
    protected function getDefinition(): ilADTDefinition
    {
        return $this->definition;
    }

    /**
     * Get copy of definition
     * @return ilADTDefinition $a_def
     */
    public function getCopyOfDefinition(): ilADTDefinition
    {
        return (clone $this->definition);
    }


    //
    // comparison
    //

    /**
     * Check if given ADT equals self
     * @param ilADT $a_adt
     * @return bool|null
     */
    abstract public function equals(ilADT $a_adt): ?bool;

    /**
     * Check if given ADT is larger than self
     * @param ilADT $a_adt
     * @return bool
     */
    abstract public function isLarger(ilADT $a_adt): ?bool;

    public function isLargerOrEqual(ilADT $a_adt): ?bool
    {
        if (!$this->getDefinition()->isComparableTo($a_adt)) {
            return null;
        }
        if ($this->equals($a_adt) === null || $this->isLarger($a_adt) === null) {
            return null;
        }
        return $this->equals($a_adt) || $this->isLarger($a_adt);
    }

    /**
     * Check if given ADT is smaller than self
     * @param ilADT $a_adt
     * @return bool | null
     */
    abstract public function isSmaller(ilADT $a_adt): ?bool;

    /**
     * Check if given ADT is smaller or equal than self
     * @param ilADT $a_adt
     * @return bool | null
     */
    public function isSmallerOrEqual(ilADT $a_adt): ?bool
    {
        if (!$this->getDefinition()->isComparableTo($a_adt)) {
            return null;
        }
        if ($this->equals($a_adt) === null || $this->isSmaller($a_adt) === null) {
            return null;
        }
        return $this->equals($a_adt) || $this->isSmaller($a_adt);
    }

    /**
     * Check if self is inbetween given ADTs (exclusive)
     * @param ilADT $a_adt_from
     * @param ilADT $a_adt_to
     * @return bool | null
     */
    public function isInbetween(ilADT $a_adt_from, ilADT $a_adt_to): ?bool
    {
        if (
            !$this->getDefinition()->isComparableTo($a_adt_from) ||
            !$this->getDefinition()->isComparableTo($a_adt_to)
        ) {
            return null;
        }
        if ($this->isLarger($a_adt_from) === null || $this->isSmaller($a_adt_to) === null) {
            return null;
        }
        return $this->isLarger($a_adt_from) && $this->isSmaller($a_adt_to);
    }

    /**
     * Check if self is inbetween given ADTs (inclusive)
     * @param ilADT $a_adt_from
     * @param ilADT $a_adt_to
     * @return bool
     */
    public function isInbetweenOrEqual(ilADT $a_adt_from, ilADT $a_adt_to): ?bool
    {
        if (
            !$this->getDefinition()->isComparableTo($a_adt_from) ||
            !$this->getDefinition()->isComparableTo($a_adt_to)
        ) {
            return null;
        }
        if (
            $this->equals($a_adt_from) === null ||
            $this->equals($a_adt_to) === null ||
            $this->isInbetween($a_adt_from, $a_adt_to) === null
        ) {
            return null;
        }
        return
            $this->equals($a_adt_from) ||
            $this->equals($a_adt_to) ||
            $this->isInbetween($a_adt_from, $a_adt_to);
    }

    /**
     * Is currently null
     * @return bool | null
     */
    abstract public function isNull(): bool;

    public function isValid(): bool
    {
        $this->validation_errors = [];

        if (!$this->getDefinition()->isNullAllowed() && $this->isNull()) {
            $this->addValidationError(self::ADT_VALIDATION_ERROR_NULL_NOT_ALLOWED);
            return false;
        }
        return true;
    }

    protected function addValidationError(string $a_error_code): void
    {
        $this->validation_errors[] = $a_error_code;
    }

    /**
     * Get all validation error codes
     * @return string[]
     * @see isValid()
     */
    public function getValidationErrors(): array
    {
        if (
            is_array($this->validation_errors) &&
            count($this->validation_errors)) {
            return array_unique($this->validation_errors);
        }
        return [];
    }

    /**
     * Translate error-code to human-readable message
     * @param string $a_code
     * @return string
     * @throws Exception
     */
    public function translateErrorCode(string $a_code): string
    {
        switch ($a_code) {
            case self::ADT_VALIDATION_ERROR_NULL_NOT_ALLOWED:
                return $this->lng->txt("msg_input_is_required");

            case self::ADT_VALIDATION_ERROR_MAX_LENGTH:
                return $this->lng->txt("adt_error_max_length");

            case self::ADT_VALIDATION_ERROR_MAX_SIZE:
                return $this->lng->txt("adt_error_max_size");

            case self::ADT_VALIDATION_ERROR_MIN:
                return $this->lng->txt("form_msg_value_too_low");

            case self::ADT_VALIDATION_ERROR_MAX:
                return $this->lng->txt("form_msg_value_too_high");

                // :TODO: currently not used - see ilDateTimeInputGUI
            case self::ADT_VALIDATION_DATE:
                return $this->lng->txt("exc_date_not_valid");
        }
        throw new Exception("ADT unknown error code");
    }

    /**
     * Get unique checksum
     * @return string | null
     */
    abstract public function getCheckSum(): ?string;

    /**
     * Export value as stdClass
     * @return stdClass | null
     */
    abstract public function exportStdClass(): ?stdClass;

    /**
     * Import value from stdClass
     * @param stdClass | null $a_std
     */
    abstract public function importStdClass(?stdClass $a_std): void;
}
