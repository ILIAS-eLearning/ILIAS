# Architectural Rules for PHPStan

Example configuration file to include the custm rules and services:

```yaml
includes:
  - CI/PHPStan/phpstan.neon
  - phpstan-baseline.neon
parameters:
  level: 6
rules:
  - ILIAS\CI\PHPStan\rules\NoTriggerErrorFunctionCallRule
  - ILIAS\CI\PHPStan\rules\NoSilenceOperatorRule
  - ILIAS\CI\PHPStan\rules\NoScriptTerminationRule
  - ILIAS\CI\PHPStan\rules\NoEvalFunctionCallRule
  - ILIAS\CI\PHPStan\rules\ControllersMustNotUseDatabaseRule
services:
  -
    class: ILIAS\CI\PHPStan\services\SuffixBasedControllerDetermination
    arguments:
      suffix: "GUI"
```