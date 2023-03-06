Rector - Instant Upgrades and Automated Refactoring
===================================================

We can use Rector to do some automated refactoring. This is a good way to upgrade your codebase to a new version of PHP.

## Usage
There is  basic configuration which includes two costim rules, removing all requires/includes and addid the License-header if needed.

```bash
./libs/composer/vendor/bin/rector process --config CI/Rector/basic_rector.php YOUR_DIRECTORY
```

You can try to update your Code to support PHP 8.0 to 8.2 with the follwoing rule-set:

```bash
./libs/composer/vendor/bin/rector process --config CI/Rector/ilias_9.php YOUR_DIRECTORY
```

Please check the changes and revert the ones you don't want to have.

The following rule-set contains some general improvements for your code 8such as early returns, removing unused variables, etc.):

```bash
./libs/composer/vendor/bin/rector process --config CI/Rector/code_quality.php YOUR_DIRECTORY
```
