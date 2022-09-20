<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class arBuilder
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arBuilder
{
    protected ActiveRecord $ar;
    protected ?int $step = null;

    public function __construct(ActiveRecord $ar, int $step = null)
    {
        $this->setAr($ar);
        $this->setStep($step ?? 0);
    }

    public function generateDBUpdateForInstallation(): void
    {
        $tpl = new ilTemplate(__DIR__ . '/templates/dbupdate.txt', true, true);
        $ar = $this->getAr();

        $tpl->setVariable('TABLE_NAME', $ar->getConnectorContainerName());
        $tpl->setVariable('TABLE_NAME2', $ar->getConnectorContainerName());
        $tpl->setVariable('TABLE_NAME3', $ar->getConnectorContainerName());
        $tpl->setVariable('STEP', $this->getStep());
        $tpl->setVariable('PRIMARY', $this->getAr()->getArFieldList()->getPrimaryFieldName());

        foreach ($this->getAr()->getArFieldList()->getFields() as $field) {
            $tpl->touchBlock('field');
            $tpl->setVariable('FIELD_NAME', $field->getName());
            foreach ($field->getAttributesForConnector() as $name => $value) {
                $tpl->setCurrentBlock('attribute');
                $tpl->setVariable('NAME', arFieldList::mapKey($name));
                $tpl->setVariable('VALUE', $value);
                $tpl->parseCurrentBlock();
            }
        }

        if ($this->getAr()->getArFieldList()->getPrimaryField()->getFieldType() === arField::FIELD_TYPE_INTEGER) {
            $tpl->setCurrentBlock('attribute');
            $tpl->setVariable('TABLE_NAME4', $ar->getConnectorContainerName());
            $tpl->parseCurrentBlock();
        }

        header('Content-type: application/x-httpd-php');
        header("Content-Disposition: attachment; filename=\"dbupdate.php\"");
        echo $tpl->get();
        exit;
    }

    public function setAr(\ActiveRecord $ar): void
    {
        $this->ar = $ar;
    }

    public function getAr(): \ActiveRecord
    {
        return $this->ar;
    }

    public function setStep(int $step): void
    {
        $this->step = $step;
    }

    public function getStep(): int
    {
        return $this->step;
    }
}
