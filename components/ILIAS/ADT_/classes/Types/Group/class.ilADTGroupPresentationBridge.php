<?php

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
