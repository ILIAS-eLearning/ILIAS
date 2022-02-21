##  Git Hooks

This document contains some basic information regarding Git hooks in ILIAS.
It describes how Git hooks can be installed/activated [manually](#creating-git-hooks)
and where to find some offial Git hooks provided by the ILIAS community.
Furtheremore it contains installation instructions for [CaptainHook](#the-easy-way-captainhook---git-hook-library),
a Git hook PHP library used by ILIAS to manage and execute a shared Git
hook configuration amongst contributers.

**Table of Contents**
* [General](#general)
* [Usage](#usage)
  * [The Easy Way: CaptainHook - Git Hook Library](#the-easy-way-captainhook---git-hook-library)
    * [Installation](#installation)
    * [Troubleshooting](#troubleshooting)
  * [The Manualy Way: Creating Git Hooks](#the-manual-way-creating-git-hooks)
    * [ILIAS Git Hooks](#ilias-git-hooks)

### General

Git hooks are scripts that run automatically every time a particular
event occurs in a Git repository and thus can be a powerful tool
in the ILIAS development workflow.
Check out the [documentation about Git Hooks](https://git-scm.com/docs/githooks)
for more information.

### Usage

Preconditions:
* [Git](https://git-scm.com/) must be configured for the local project and
  the development environment

#### The Easy Way: CaptainHook - Git Hook Library

ILIAS provides a [shared Git hook configuration](../../captainhook.json) for
[CaptainHook](https://github.com/CaptainHookPhp/captainhook), a Git hook
management library written in PHP.

It enables you to define a shared (amongst developers) Git hook configuration
for actions being executed locally on your machine.

Currently, the following actions will be executed:

* pre-commit:
  * PHP Linting
  * php-cs-fixer (dryrun only)

If you'd like to make sure all your committed files pass a PHP syntax (lint) check,
and the [ILIAS Coding Style](./coding-style.md) was applied to all committed files,
you are welcome to install *CaptainHook* and our shared actions.

##### Installation

Once you installed the composer development dependencies, go to the ILIAS
main directory and execute:

```bash
libs/composer/vendor/bin/captainhook install
```
Executing this will create the hook script located in your `.git/hooks` directory,
for each hook you choose to install while running the command.

Every time Git triggers a hook, *CaptainHook* will be executed.

If one the enabled actions (scripts) returns an exit code unequal `0`
(successful program termination), your files will **not** be committed.

##### Troubleshooting

* If you have issues with *CaptainHook* you can remove the created hooks in the
`./git/hooks` directory permanently. If you only want to temporarily deactivate
a certain hook or action, disable the hook section by setting `enabled` to `false` or
remove the action from the respective array in the `captainhook.json` configuration file.
Please make sure you do **not commit** a changed `captainhook.json` file be accident.
* If the Git hooks have been installed once, changing branches could be an issue **if**
*CaptainHook* is **not** installed or the `captainhook.json` file is missing in the branch
just checked out.

##### Additional Custom Actions
You can merge some additional custom actions with the standard actions of the captainhook.json:
Create your own config-file and add actions or settings you want to merge with the default. Make sure, 
you have the config option `includes` defined to include the standard-configuration `captainhook.json`, 
e.g a file `captainhook.local.json` (which will be ignored in the repo):

````json
{
  "config": {
    "includes": [
      "captainhook.json"
    ]
  },

  "pre-push": {
    "enabled": true,
    "actions": [
      {
        "action": "./CI/PHPUnit/run_tests.sh"
      }
    ]
  }
}

````
You must reinstall captainhook with your local config. Go to the ILIAS
main directory and execute:

```bash
libs/composer/vendor/bin/captainhook install -c captainhook.local.json
```

The installation of the local file might respond with the following error:

```bash
In Builder.php line 55:
                                                                        
  bootstrap file not found: '/var/www/ilias/trunk/vendor/autoload.php'  
                                                                        
```

In this case, you have to include the `"bootstrap": "libs/composer/vendor/autoload.php"` setting,
similar to [captainhook.json](../../captainhook.json).

#### The Manual Way: Creating Git Hooks

Git hooks recide in the `.git/hooks` directory of a Git repository.
To install/activate a Git hook first enter this directory.

```bash
cd .git/hooks
```

By default every hook has the suffix `.sample`. This suffix prevents them
from beeing executed by default when a particular Git event occurs.

Removing this suffix will activate the hook.

```bash
mv pre-commit.sample pre-commit
```

Git hooks can be implemented by using any scripting language as long as they
can be run as an executable. There can only be one script for each particular
Git event.

If a script exits with a status set to `1`, the actual execution of
the Git event will be aborted, otherwise an exit status set to `0`
will continue the Git workflow as expected.

##### ILIAS Git Hooks

The ILIAS community provides a bunch of Git hook scripts which can be used to
optimize the development workflow and to enforce certain policies regarding
the ILIAS code base.

__Please be aware that these recommendations are for a development environment
only, and may not be used in production environments__

The Git hook scripts can be found in the
[git_hooks](https://github.com/ILIAS-eLearning/DeveloperTools/tree/master/git_hooks)
folder of the official [ILIAS Developer Tools](https://github.com/ILIAS-eLearning/DeveloperTools)
GitHub repository.