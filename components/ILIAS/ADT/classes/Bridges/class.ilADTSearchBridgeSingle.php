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
 *********************************************************************/

declare(strict_types=1);

/**
 * Class ilADTSearchBridgeSingle
 */
abstract class ilADTSearchBridgeSingle extends ilADTSearchBridge
{
    protected ilADT $adt;

    protected function setDefinition(ilADTDefinition $a_adt_def): void
    {
        if ($this->isValidADTDefinition($a_adt_def)) {
            $this->adt = ilADTFactory::getInstance()->getInstanceByDefinition($a_adt_def);
            return;
        }

        throw new InvalidArgumentException('ilADTSearchBridge type mismatch.');
    }

    /**
     * Get ADT
     * @return ilADT|null
     */
    public function getADT(): ?ilADT
    {
        return $this->adt;
    }

    public function isNull(): bool
    {
        return !$this->getADT() instanceof ilADT || $this->getADT()->isNull();
    }

    public function isValid(): bool
    {
        return $this->getADT() instanceof ilADT && $this->getADT()->isValid();
    }

    public function validate(): bool
    {
        if (!$this->getADT() instanceof ilADT) {
            return false;
        }
        if (!$this->isValid()) {
            $tmp = array();
            $mess = $this->getADT()->getValidationErrors();
            foreach ($mess as $error_code) {
                $tmp[] = $this->getADT()->translateErrorCode($error_code);
            }
            $field = $this->getForm()->getItemByPostVar($this->getElementId());
            $field->setAlert(implode("<br />", $tmp));

            return false;
        }
        return true;
    }
}
