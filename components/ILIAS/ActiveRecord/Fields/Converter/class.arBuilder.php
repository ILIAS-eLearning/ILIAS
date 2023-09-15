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

/**
 * Class arBuilder
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arBuilder
{
    protected ActiveRecord $ar;
    protected ?int $step = null;

    public function __construct(ActiveRecord $activeRecord, int $step = null)
    {
        $this->setAr($activeRecord);
        $this->setStep($step ?? 0);
    }

    public function generateDBUpdateForInstallation(): void
    {
        $ilTemplate = new ilTemplate(__DIR__ . '/templates/dbupdate.txt', true, true);
        $ar = $this->getAr();

        $ilTemplate->setVariable('TABLE_NAME', $ar->getConnectorContainerName());
        $ilTemplate->setVariable('TABLE_NAME2', $ar->getConnectorContainerName());
        $ilTemplate->setVariable('TABLE_NAME3', $ar->getConnectorContainerName());
        $ilTemplate->setVariable('STEP', $this->getStep());
        $ilTemplate->setVariable('PRIMARY', $this->getAr()->getArFieldList()->getPrimaryFieldName());

        foreach ($this->getAr()->getArFieldList()->getFields() as $arField) {
            $ilTemplate->touchBlock('field');
            $ilTemplate->setVariable('FIELD_NAME', $arField->getName());
            foreach ($arField->getAttributesForConnector() as $name => $value) {
                $ilTemplate->setCurrentBlock('attribute');
                $ilTemplate->setVariable('NAME', arFieldList::mapKey($name));
                $ilTemplate->setVariable('VALUE', $value);
                $ilTemplate->parseCurrentBlock();
            }
        }

        if ($this->getAr()->getArFieldList()->getPrimaryField()->getFieldType() === arField::FIELD_TYPE_INTEGER) {
            $ilTemplate->setCurrentBlock('attribute');
            $ilTemplate->setVariable('TABLE_NAME4', $ar->getConnectorContainerName());
            $ilTemplate->parseCurrentBlock();
        }

        header('Content-type: application/x-httpd-php');
        header("Content-Disposition: attachment; filename=\"dbupdate.php\"");
        echo $ilTemplate->get();
        exit;
    }

    public function setAr(\ActiveRecord $activeRecord): void
    {
        $this->ar = $activeRecord;
    }

    public function getAr(): \ActiveRecord
    {
        return $this->ar;
    }

    public function setStep(int $step): void
    {
        $this->step = $step;
    }

    public function getStep(): ?int
    {
        return $this->step;
    }
}
