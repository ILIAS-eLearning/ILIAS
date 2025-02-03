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

// This is the default resolution for ambiguous dependencies of components in
// Standard ILIAS.
//
// The structure is as such: keys are components that use services ("dependant")
// that need disambiguation, the value for each dependant is an array where the key
// is the definition ("dependency") and the value is the implementation
// ("implementation") to be used.
//
// The entry "*" for the dependant will define fallbacks to be used for all components
// that have no explicit disambiguation.
return [
    "*" => [
        \ILIAS\Language\Language::class => \ilSetupLanguage::class
    ],
];
