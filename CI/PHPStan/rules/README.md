# Architectural Rules for PHPStan

Example configuration file to include the custm rules and services:

```yaml
includes:
    - CI/PHPStan/phpstan.neon
    - phpstan-baseline.neon
parameters:
    level: 6
rules:
    - ILIAS\CI\PHPStan\rules\NoTriggerErrorFunctionCall
    - ILIAS\CI\PHPStan\rules\NoSilenceOperatorRule
    - ILIAS\CI\PHPStan\rules\NoScriptTermination
    - ILIAS\CI\PHPStan\rules\NoEvalFunctionCall
    - ILIAS\CI\PHPStan\rules\ControllersMustNotUseDatabase
services:
    -
        class: ILIAS\CI\PHPStan\rules\SuffixBasedControllerDetermination
        arguments:
          suffix: "GUI"
```