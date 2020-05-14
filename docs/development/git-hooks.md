##  Git Hooks

This document lists some useful Git hooks to create a better development
experience for a contributor of the ILIAS project.

**Table of Contents**
* [General](#general)
* [Usage](#usage)
  * [Install Development Dependencies](#install-development-dependencies)
  * [Creating Git Hooks](#creating-git-hooks)
    * [Code Style Hooks](#code-style-hooks)
  * [CaptainHook - Git Hook Library](#captainhook---git-hook-library)
    * [Installation](#installation)
    * [Troubleshooting](#troubleshooting)

### General

Git hooks are powerful tools that can be used on dedicated events during the
development workflow.
Check out the [documentation about Git Hooks](https://git-scm.com/docs/githooks)
for more information.

All the following hooks can be found in the
[ILIAS Developer Tools](https://github.com/ILIAS-eLearning/DeveloperTools)
as separated files.

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
[git_hooks code style folder](https://github.com/ILIAS-eLearning/DeveloperTools/tree/master/git_hooks/hooks/code-style)
to create specific git hook adapted to the needs of the development process.

The official [ILIAS pre-commit](https://github.com/ILIAS-eLearning/DeveloperTools/blob/master/git_hooks/hooks/pre-commit)
uses a dry-run to check your code style and returns the line that needs to change according to the defined code style.

#### CaptainHook - Git Hook Library

ILIAS provides a [shared Git hook configuration](../../captainhook.json) for
[CaptainHook](https://github.com/CaptainHookPhp/captainhook), a Git hook
management library written in PHP.

It enables you to define a shared (amongst developers) Git hook configuration
for actions being executed locally on you machine.

Currently the following actions will be executed:

* pre-commit:
  * PHP Linting
  * php-cs-fixer (dryrun only)

If you'd like to make sure all your committed files pass a PHP syntax (lint) check,
and the [ILIAS Coding Style](./coding-style.md) was applied to all committed files,
you are welcome to install *CaptainHook* and our shared actions.

##### Installation

Once you installed the composer development dependencies, move to the ILIAS
main directory and execute:

```bash
libs/composer/vendor/bin/captainhook install
```
Executing this will create the hook script located in your `.git/hooks` directory,
for each hook you choose to install while running the command.

Every time Git triggers a hook, *CaptainHook* will be executed.

##### Troubleshooting

* If you have issues with *CaptainHook* you can remove the created hooks in the
`./git/hooks` directory permanently. If you only want to temporarily deactivate
a certain hook or action, disable the hook section by setting `enabled` to `false` or
remove the action from the respective array in the `captainhook.json` configuration file.
Please make sure you do **not commit** a changed `captainhook.json` file be accident. 