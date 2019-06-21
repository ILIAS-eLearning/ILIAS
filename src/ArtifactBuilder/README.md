ArtifactBuilder
===============

## What's the general point?
Information that ILIAS needs in order to offer certain services is based on information statically specified by the source code. For example, which GlobalScreen providers are there in the (core) source code? Which WebAccessChecker checking instances are there that can be requested? Or which command and base classes are there? This information is directly linked to the status of the source code and is therefore static rather than dynamic.

Such information has often been collected in a structure reload in the ILIAS setup and stored in the database. This led to the fact that this information had to be read in the database again for practically every request in ILIAS. With the introduction of GlobalCache at least some such queries could be cached. Nevertheless, it is not necessary for many of the information to be cached or even stored in the database, since it only changes due to the further development of ILIAS.

## How this service can help?
The ArtifactBuilder currently provides two thing: A small description, what an artifact is and a (composer-bound) way to generate them.

> This is not a final stage of this service. Have a look at the discussion in https://github.com/ILIAS-eLearning/ILIAS/pull/1862. There will follow more. 

## An example
The GlobalScreen service needs the information which GlobalScreen providers exist for which scope (e.g. MainBar or MetaBar) in the core. Instead of loading this information from the database with each request, an array of class names implementing the respective provider interface is sufficient. Therefore, the GlobalScreen provider now collects all class names in a BootLoader and can request them directly in the source code. The collection of the class names is done automatically during the development, what is stored is a so called artifact:

```php
// libs/ilias/Artifacts/global_screen_providers.php

<?php return array (
  'ILIAS\\GlobalScreen\\Scope\\MainMenu\\Provider\\StaticMainMenuProvider' => 
  array (
    0 => 'ilLearningHistoryGlobalScreenProvider',

	//...

    18 => 'ilPrtfGlobalScreenProvider',
  ),
  'ILIAS\\GlobalScreen\\Scope\\MetaBar\\Provider\\StaticMetaBarProvider' => 
  array (
    0 => 'ilSearchGSMetaBarProvider',
    1 => 'ilMMCustomTopBarProvider',
  ),
  'ILIAS\\GlobalScreen\\Scope\\Tool\\Provider\\DynamicToolProvider' => 
  array (
    0 => 'ilStaffGSToolProvider',
    1 => 'ilMediaPoolGSToolProvider',
  ),
);

```

The GlobalScreen service can now easily load and use this artifact:

```php
	/**
	 * @inheritDoc
	 */
	public function __construct(Container $dic) {
		 ... 
		$this->class_loader = include "libs/ilias/Artifacts/global_screen_providers.php";
	}

```

## Advantages
Performance. Such a BootLoader will cause the information to be practically in-memory, especially in newer PHP versions. In addition, the - unnecessary - queries to the database will be reduced. Every installation benefits from this because no additional (caching) components have to be installed to use this.

## Why just now?
The generation of such artifacts should be placed close to the development process. By eliminating Composer dependencies as part of the repository, development is even more dependent on updating the Composer class map for autoloading, for example. Composer offers the possibility to connect own scripts to certain events. 
And Generators! We still use them very little. Generators are brutally fast, especially to quickly go through and minimize large lists. https://www.php.net/manual/en/language.generators.syntax.php

## Plugins
The above statements are in favor of information provided by the Core. Of course, ILIAS plugins often also contribute information. Due to the current plugin slots these data would have to be added to the information provided by the core (see e.g. `ilGSProviderFactory`). For later adjustments of the PluginsSlots it should be considered that such information can be requested through the slot at the Plugins.

## Outlook
This PR implements the generation of a boot loader for the GlobalScreen service. In another PR, we also provide the readout and integration of the entire core ilCtrl structure. The implementation has already shown that this readout of the ilCtrl structure for the code takes just 1 second.

Further scripts can follow, for my components I would like to use the service e.g. with WebAccessChecker. Further places that could be mapped by artifacts probably result from the analysis of the ilObjDefReader.

# How do I use it?
In composer.json there is a new script registered which is called in two ways:

```
// libs/composer/composer.json
...
	"scripts": {
    		"post-autoload-dump": [
              "ILIAS\\ArtifactBuilder\\Runner\\ComposerArtifactRunner::run"
    		],
    		"artifacts": [
              "ILIAS\\ArtifactBuilder\\Runner\\ComposerArtifactRunner::run"
    		]
    	},
...
```

the above example now means that the building of artifacts is called after a `composer dump-autoload` as well as after a `composer artifacts`.

For your own artifact-builder you can implement the interface `ArtifactBuilder` or extend from `AbstractArtifactBuilder`. In your `ArtifactBuilder` you return a `Artifact` which then will be `save()`ed. There is currently on implemented Artifact called `ArrayToFileArtifact`.

To find your `ArtifactBuilder`, implement an `ArtifactBuilderFactory` and return all your desired `ArtifactBuilder`. These will be collected by the `MainArtifactBuilderFactory`.

Currently there is a Runner `ComposerArtifactRunner` which executes all `ArtifactBuilder` found by the `MainArtifactBuilderFactory`.

