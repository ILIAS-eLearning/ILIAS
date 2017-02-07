<?php


namespace ILIAS\UI\Implementation\Component\Modal;
use ILIAS\UI\Implementation\Component\Button\Primary;
use ILIAS\UI\Implementation\Component\Button\Standard;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
trait ModalHelper
{

    /**
     * @param string $label
     * @return \ILIAS\UI\Component\Button\Primary|\ILIAS\UI\Component\Button\Standard
     */
    protected function getCancelButton($label = '')
    {
        $button = new Standard($label, '');
        return $button->withOnLoadCode(function ($id) {
            return "$('#{$id}').click(function() { $(this).closest('.modal').modal('hide'); return false; });";
        });
//        return $button->triggerAction($this->close());
    }

}