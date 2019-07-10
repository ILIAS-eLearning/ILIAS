##  Git Hooks

This document lists some useful Git hooks to create a better development
experience for a contributor of the ILIAS project.

**Table of Contents**
* [General](#general)
* [Usage](#usage)
  * [Install Development Dependencies](#install-development-dependencies)
  * [Creating Git Hooks](#creating-git-hooks)
    * [Code Style Hooks](#code-style-hooks)

### General

Git hooks are powerful tools that can be used on dedicated events during the
development workflow.
Check out the [documentation about Git Hooks](https://git-scm.com/docs/githooks)
for more information.

All the following hooks can be found in the
[ILIAS Developer Tools](https://github.com/ILIAS-eLearning/DeveloperTools)
as seperated files.

### Usage

Preconditions:
* [composer](https://getcomposer.org/) must be installed
* [Git](https://git-scm.com/) must be configured for the local project and
  the development environment

__Please be aware that these recommendations are for a development environment
only, and may not be used in production environments__

#### Install Development Dependencies

To use the following Git hooks, the composer development dependencies need to
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

Move to directory where Git hooks are stored.

```
$ cd .git/hooks
```

By default every hook has the suffix `.sample`.
Removing this suffix will activate the hook.

```
$ mv pre-commit.sample pre-commit
```

##### Code Style Hooks

The ILIAS project serves several Git Hooks stored in the official
[Developer Tools Repository](https://github.com/ILIAS-eLearning/DeveloperTools)
Check the
[git_hooks code style folder](https://github.com/ILIAS-eLearning/DeveloperTools/tree/master/git_hooks/code-style/)
to create specific git hook adapted to the needs of the development process.

The offical [ILIAS pre-commit](https://github.com/ILIAS-eLearning/DeveloperTools/blob/master/git_hooks/hooks/pre-commit)
uses a dry-run the check you code style and returns
the line that needs to change according to the defined code style.
