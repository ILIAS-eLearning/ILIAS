<?php

declare(strict_types=1);

class ilADTLocation extends ilADT
{
    protected ?float $longitude;
    protected ?float $latitude;
    protected ?int $zoom;

    public const ADT_VALIDATION_ERROR_LONGITUDE = "loc1";
    public const ADT_VALIDATION_ERROR_LATITUDE = "loc2";

    // definition

    protected function isValidDefinition(ilADTDefinition $a_def): bool
    {
        return $a_def instanceof ilADTLocationDefinition;
    }

    // default

    public function reset(): void
    {
        parent::reset();
        $this->setZoom(9);
        $this->setLatitude();
        $this->setLongitude();
    }

    // properties

    public function setLongitude(float $a_value = null): void
    {
        $this->longitude = $a_value;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLatitude(?float $a_value = null): void
    {
        $this->latitude = $a_value;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getZoom(): ?int
    {
        return $this->zoom;
    }

    public function setZoom($a_value): void
    {
        $this->zoom = max(1, abs((int) $a_value));
    }

    // comparison

    public function equals(ilADT $a_adt): ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return ($this->getLongitude() == $a_adt->getLongitude() &&
                $this->getLatitude() == $a_adt->getLatitude());
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

    // null

    public function isNull(): bool
    {
        return $this->getLongitude() === null && $this->getLatitude() === null;
    }

    // validation

    public function isValid(): bool
    {
        $valid = parent::isValid();
        $long = $this->getLongitude();
        $lat = $this->getLatitude();
        if ($long !== null && $lat !== null) {
            // 0 - (+-)180
            if ($long < -180 || $long > 180) {
                $this->addValidationError(self::ADT_VALIDATION_ERROR_LONGITUDE);
                $valid = false;
            }
            // 0 - (+-)90
            if ($lat < -90 || $lat > 90) {
                $this->addValidationError(self::ADT_VALIDATION_ERROR_LATITUDE);
                $valid = false;
            }
        }
        return $valid;
    }


    // check

    /**
     * @inheritcoc
     */
    public function translateErrorCode(string $a_code): string
    {
        switch ($a_code) {
            case self::ADT_VALIDATION_ERROR_LONGITUDE:
                return $this->lng->txt("adt_error_longitude");

            case self::ADT_VALIDATION_ERROR_LATITUDE:
                return $this->lng->txt("adt_error_latitude");

            default:
                return parent::translateErrorCode($a_code);
        }
    }

    public function getCheckSum(): ?string
    {
        if (!$this->isNull()) {
            return md5($this->getLongitude() .
                "#" . $this->getLatitude() .
                "#" . $this->getZoom());
        }
        return null;
    }

    public function exportStdClass(): ?stdClass
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->lat = $this->getLatitude();
            $obj->long = $this->getLongitude();
            $obj->zoom = $this->getZoom();
            return $obj;
        }
        return null;
    }

    public function importStdClass(?stdClass $a_std): void
    {
        if (is_object($a_std)) {
            $this->setLatitude($a_std->lat);
            $this->setLongitude($a_std->long);
            $this->setZoom($a_std->zoom);
        }
    }
}
