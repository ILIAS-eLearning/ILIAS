<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

declare(strict_types=1);

namespace ILIAS\User\Profile\Fields\Standard;

use ILIAS\User\Profile\PublicProfileGUI;

trait BuildAutocompletionUrl
{
    private function getAutocompleteUrl(\ilCtrl $ctrl): string
    {
        return $ctrl->getLinkTargetByClass(
            [
                \ilPublicProfileBaseClassGUI::class,
                PublicProfileGUI::class
            ],
            'doProfileAutoComplete'
        );
    }
}
