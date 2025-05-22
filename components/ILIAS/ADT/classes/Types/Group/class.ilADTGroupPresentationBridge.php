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

class ilADTGroupPresentationBridge extends ilADTPresentationBridge
{
    protected array $elements = [];

    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTGroup);
    }

    protected function prepareElements(): void
    {
        if (count($this->elements)) {
            return;
        }

        $this->elements = array();
        $factory = ilADTFactory::getInstance();

        // convert ADTs to presentation bridges
        foreach ($this->getADT()->getElements() as $name => $element) {
            $this->elements[$name] = $factory->getPresentationBridgeForInstance($element);
        }
    }

    public function getHTML($delimiter = "<br />"): string
    {
        $res = array();

        $this->prepareElements();
        foreach ($this->elements as $element) {
            $res[] = $this->decorate($element->getHTML());
        }

        if (count($res)) {
            return implode($delimiter, $res);
        }
        return '';
    }

    public function getSortable($delimiter = ";")
    {
        $res = array();

        $this->prepareElements();
        foreach ($this->elements as $element) {
            $res[] = $element->getSortable();
        }

        if (count($res)) {
            return implode($delimiter, $res);
        }
        return '';
    }
}
