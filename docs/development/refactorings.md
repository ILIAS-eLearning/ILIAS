## Introduction
All software requires constant refactoring of code to meet new standards or 
new versions of php. Refactorings happen on a small scale all the time, 
larger refactorings often involve planning and impact on the code base. All 
of us at ILIAS development welcome refactorings and would like to encourage 
all developers and maintainers to tackle refactorings and would like to help 
make refactorings easier with the following tips and rules.

## Guidelines / What to Consider
Smaller refactorings often have no impact on consumers of code, they often 
take place within a closed system. Larger refactorings or adaptations to the 
code that affect a kind of "public interface", however, often have an impact 
on consumers of the code. in many cases, these consumers are also 
unknown.
- Refactorings SHOULD always be tackled in trunk only. If a developer 
  intends to port a refactoring to another branch the refactoring MUST be 
  communicated at the Jour Fixe and all uses of the corresponding code MUST 
  be checked and fixes proposed as PRs to the corresponding maintainers.
- Developers SHOULD in any case announce refactorings - if it is known that 
  they will have a major impact on consumers of code - by sending a short 
  message to the developer list announcing the intention to refactor, 
  preferably together with an estimate of when this will happen.
- Refactorings SHOULD be tackled at the beginning of the development phase 
  of a new release, so that all developers have enough time to make any 
  adjustments to their own code.
- Refactorings can lead to the trunk no longer being executable. Developers 
  SHOULD make sure that at least the following automatic tasks remain 
  operational also in trunk:
  - Install a new Installation
  - Run all Unit Tests without Failure
  - Run composer without error 
- To make the work easier for other developers, it would be nice if the 
  following tasks in ILIAS trunk would also work:
  - Login as root
  - Create a new User
  - Login as new User
  - Access Dashboard along with a working Meta and Mainbar.

The ILIAS-Community acknowledges, that this is not always possible and that 
thus the trunk may be broken temporarily.

- In refactorings, you as a developer are not responsible for ensuring that 
  consumer code continues to work, unless you change things that are 
  explicitly declared as public API. Changes to the public API MUST be 
  announced as a pull request at a Jour Fixe. Otherwise, the changes MAY be 
  made without having to adapt consumer code as well. The developer MAY 
  adapt consumer code directly. If the changes are trivial, they MAY also 
  be committed without consulting the consumers.
- Changing namespaces can also lead to consumer code no longer working. 
  However, since it is very easy to change namespaces for consumer code 
  using an IDE such as PHPStorm, the developer SHOULD adapt the consumer 
  code as this will probably not involve any additional effort.

### Temporary Provisions
- Moving classes can cause legacy require_once or include_once to no longer 
  work. Since the introduction of autoloading, these are no longer needed 
  anyway and should be removed by the respective maintainers.

## Automated Refactorings using PHP Rector
You can update PHP-Code using automated scripts and rules providede by 
the open source project [Rector](https://github.com/rectorphp/rector). 
Rector is defined as dev-dependecy and you can install it with composer:

```bash
composer install
```
Rector provide 450+ rules and sets of rules which can be applied in the 
rector.php definition file located in `./CI/Rector/basic_rector.php`. A full list 
of available rules can be found [here](https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md).

The current configuration applied rules and rulesets will help you to 
maintain your code and update it für PHP 8.0 and PHP 8.1.

The rules will be applied to your code using the following command (the 
example applies the ule so Services/GlobalCache): 

```bash
./libs/composer/vendor/bin/rector process --config ./CI/Rector/basic_rector.php --no-diffs Services/GlobalCache
```
There also is a composer script for the same command:

```bash
composer rector Services/GlobalCache
```
Please review and test your changes after applying the rules. There might be 
some placey whwre you do not want to apply the rules. You can opt-out lines 
with the comment ``@noRector`, e.g.:

```php
/** @noRector  */
$this->class_loader = include 
"Services/GlobalScreen/artifacts/global_screen_providers.php";
```

It's quite easy to write own rules for rector, currently done with the Rule 
`RemoveRequiresAndIncludes` in CI/Rector/RemoveRequiresAndIncludesRector.php. 

