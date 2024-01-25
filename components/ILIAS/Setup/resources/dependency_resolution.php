<?php

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
        "\\ILIAS\Language\Language" => "ilSetupLanguage"
    ],
    "\\ILIAS\\Refinery" => [
        "\\ILIAS\Language\Language" => "myLanguage"
    ]
];
