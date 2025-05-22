# The ILIAS Setup 

## What is This About?

The ILIAS Setup makes sure that ILIAS can be run on your system. Only downloading
ILIAS' code won't be enough. There are various things that need to be checked and
prepared to make your system actually run ILIAS: the environment is checked for
required dependencies, folders and files need to be created from the raw sources,
the database needs to be installed and filled with the settings for your installation.

This guide briefly explains the necessary steps to install ILIAS and gives pointers
to additional resources for the actual installation and to understand the ILIAS setup
in depth.

## Why do I Need This in ILIAS?

If you want to get involved with ILIAS in general or with coding in ILIAS specifically:
you won't be able to achieve anything without a running ILIAS installation. Setting
up a local installation will help tremendously when working on ILIAS, even if you just
fix a simple bug.

If you want to code elaborate features with ILIAS you will likely want to hook into
the mechanism of the Setup at some point. This is where an more in-depth understanding
of the machinery will come in handy.

## How to Proceed?

### Get ILIAS Source Code 

We assume that you have set up [your environment](./01-environment.md) already. We
further assume that you, as an aspiring developer, will want to use git to get the
source code onto your machine. Hence, run

```sh
git clone git@github.com:ILIAS-eLearning/ILIAS.git
```

somewhere on your machine. `git` will create a subfolder `ILIAS` that contains
the source code of the oldest supported ILIAS version.

### Install Dependencies and Create Artifacts

The raw source code of ILIAS is not enough to actually run the application. You
will need to install dependencies, that is source code from external vendors that
ILIAS requires to function. Move to the ILIAS folder

```sh
cd ILIAS
```

and start by installing the JavaScript dependencies:

```sh
npm clean-install --ignore-scripts
```

`npm`, the Node Package Manager, should show you how it downloads various packages
from the internet and should then inform you that it has finished its job successfully.
Move on by running a similar command to install PHP packages:

```sh
composer install
```

You should see some progress information about downloaded packages. You will also
see that `composer` performs some ILIAS specific tasks. These are included into the
process via the `composer.json` config file. More specifically, ILIAS will fill
the `public` folder and create artifacts. Artifacts are php-files that are derived
from the raw source code and need to be refreshed when source code changes.

### Make `public` Available for your Webserver

The public folder is the folder that needs to be exposed via the webserver.
We assume that you know how to run and publish a PHP application in general.
Make sure that your webserver exposes the `public`-folder of your ILIAS
installation and runs PHP scripts in that folder. If you access your location
via webbrowser and you see some message that you didn't run the setup until now
you have been successfull and can move on to the next step.

### Create a Configuration

To install ILIAS you need a configuration file that contains basic configuration
for your installation. Create a copy of the file `components/ILIAS/setup_/minimal-config.json`.
Open the file in a text editor and adjust it according to your requirements. Have
a look into [the documentation of the setup](../../../../../components/ILIAS/Setup/README.md#about-the-config-file)
for additional configuration variables.

### Run the setup

Almost done: Now you can actually run the ILIAS setup in your ILIAS folder via 

```sh
php cli/setup.php install your-config.json
```

where `your-config.json` is the path to the config file you have created in the previous
step.

The setup process should show some progress information and inform you that the setup
was indeed executed successfully. Afterwards you should be able to open the
installation with a web browser and login with the default user and passwort combination
`user: root` and `password: homer`.

### Further Steps

If you are interested in a more in depth explanation of the ilias installation, have
a look into [the ILIAS installation guide](../../../../configuration/install.de).
If you want to understand how the setup works, have a look into the [README of the setup](../../../../../components/Setup/README.md).

## What do I Need to Watch Out For? (Dos & Dont's)

* Please be aware that exposing a web application to the internet is not something
  to be done lightly. This tutorial covers the basics to create a running installation
  on a local system for development purpose. Make sure to configure your system
  appropriatly to make sure that no one illegitematly uses your local installation.
* Certain changes to the code will require to rebuild artifacts. If you e.g. create
  new GUI classes or install a plugin, parts of the artifacts will need to be
  refreshed. If in doubt, run `composer install`. This will rebuild static artifacts
  and your public folder.
