# Static Code Analysis

## PHPStan

ILIAS uses [`PHPStan`](https://phpstan.org) as an optional tool for developers
to find errors in the ILIAS code base without actually running it.

### Configuration

To provide a common configuration a shared config file is located in
[`./CI/PHPStan/phpstan.neon`](../../CI/PHPStan/phpstan.neon).

`PHPStan` provides the possibility to analyse the code based on specfic
[rule levels](https://phpstan.org/user-guide/rule-levels).
The level used in ILIAS is **level 6**, which includes the reporting of missing typehints.
To prevent `False Positives` beingt reported related to global constants, developers can extend the constant collection
defined in the [`constants.php`](../../CI/PHPStan/constants.php)

### Analysis

To run `PHPStan` you just need to execute the bash script:

```bash
./CI/PHPStan/run_check.sh
```

To run `PHPStan` in the context of a specific component, pass the respective folder name as argument:

```bash
./CI/PHPStan/run_check.sh Services/Mail
```

You can overwrite the rule level by passing a `--level` argument:

```bash
./CI/PHPStan/run_check.sh Services/Mail --level 3
```

### IDE Integration

#### PHPStorm

See: https://www.jetbrains.com/help/phpstorm/using-phpstan.html