##  Git Hooks

This document will list some useful git hooks to create a better development
experience for a contributor of the ILIAS project.

**Table of Contents**
* [General](#general)
* [Usage](#usage)
  * [Install Development Dependencies](#install-development-dependencies)
  * [Creating Git Hooks](#creating-git-hooks)
    * [Code Style Hooks](#code-style-hooks)

### General

Git Hooks are powerful tools that can be used on dedicated events during the
development workflow.
Check out the [documentation about Git Hooks](https://git-scm.com/docs/githooks)
for more information.

### Usage

Preconditions:
* [composer](https://getcomposer.org/) must be installed
* [Git](https://git-scm.com/) must be configured for the local project and
  the development environment

__Please be aware that these recommendations are for a development environment
only, and may not be used in actual environments__

#### Install Development Dependencies

To use the following Git Hooks, the composer development dependencies need to
be installed.

Move to the directory with the `composer.json`

```
$ cd libs/composer
```

Install the development dependencies

```
$ composer install --dev
```

#### Creating Git Hooks

Move to directory where git hooks are stored.

```
$ cd .git/hooks
```

By default every hook has the suffix `.sample`.
Removing this suffix will activate the hook.

```
$ mv pre-commit.sample pre-commit
```

##### Code Style Hooks

The following hooks can be used to check or even fix the code style
before creating a commit.

Adapt your existing `pre-commit` hook.

The following hook won't adapt any code for the committed files,
the hook will just check if the coding style following the standard ILIAS code
style.

```bash
#!/bin/sh

if [ -x libs/composer/vendor/bin/php-cs-fixer ]; then
    echo "PHP CS Fixer is installed begin to check PHP files"
    CONFIGURATION_FILE="./CI/PHP-CS-Fixer/code-format.php_cs"
    if [ ! -f $CONFIGURATION_FILE ]; then
        echo "The configuration file is not found under ${CONFIGURATION_FILE}"
        exit 1
    fi

    CHANGED_FILES=$(git diff --cached --name-only --diff-filter=ACM -- '*.php')

    return_code=0
    result=""
    for FILE in $CHANGED_FILES
    do
        echo "Checking file: ${FILE}"
        partial_result=$(libs/composer/vendor/bin/php-cs-fixer fix --dry-run --stop-on-violation --using-cache=no --config=$CONFIGURATION_FILE --diff $FILE)
        partial_return_code=$?
        result="${result} \n\n ${partial_result}"
        if [ $partial_return_code -ne 0 ]; then
           return_code=$partial_return_code
        fi
    done

    if [ $return_code -ne 0 ]; then
       echo "Error in the Code Style"
       echo "${result}"
       echo "\nPlease fix the marked lines. Before commiting"
       exit 1
    fi

    echo "End of checking PHP files"
else
    echo "Couldn't find 'libs/composer/vendor/bin/php-cs-fixer'. Make sure it is installed, for more information check the local '/docs/coding-style.md'"
    exit 1
fi

echo "Could Style is OK."
```

The hook will execute a `dry-run` on the committed PHP files and will be
displayed the differences if the PHP-CS-FIXER returns with an error code.

Alternatively the code can be fixed immediately before creating the commit:

```bash
#!/bin/sh

if [ -x libs/composer/vendor/bin/php-cs-fixer ]; then
    echo "PHP CS Fixer is installed begin to check PHP files"
    CONFIGURATION_FILE="./CI/PHP-CS-Fixer/code-format.php_cs"
    if [ -f $CONFIGURATION_FILE]; then
        echo "The configuration file is not found under ${CONFIGURATION_FILE}"
    fi

    CHANGED_FILES=$(git diff --cached --name-only --diff-filter=ACM -- '*.php')

    return_code=0
    result=""
    for FILE in $CHANGED_FILES
    do
        echo "Fix file: ${FILE}"
        partial_result=$(libs/composer/vendor/bin/php-cs-fixer fix --stop-on-violation --using-cache=no --diff --config=$CONFIGURATION_FILE $FILE)
        partial_return_code=$?
        if [ $partial_return_code -ne 0 ]; then
           return_code=$partial_return_code
           exit 1
        fi
    done

    echo "End of fixing PHP files"
else
    echo "Couldn't find 'libs/composer/vendor/bin/php-cs-fixer'. Make sure it is installed, for more information check the local '/docs/coding-style.md'"
    exit 1
fi

echo "Could Style is OK."
```
