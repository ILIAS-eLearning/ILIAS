# Dependency Injection for ILIAS

This namespace provides a Dependency Injection Container (DIC) for ILIAS, which is 
a small adjustment of the [Pimple DIC](https://github.com/silexphp/Pimple) to fit ILIAS.

As a first step to the introduction of Dependency Injection the [Jour Fixe
deprecated all globals besides the `$DIC` global on 2015-08-03](http://www.ilias.de/docu/goto.php?target=wiki_1357_JourFixe-2015-08-03)
based on a [proposal of the SIG Refactoring](https://github.com/klees/ILIAS_SIG_Refactoring/blob/4a226b100e3e90db5a71f0e6e32bf9731fd31be0/DependencyInjection/PROPOSAL_1.md)
starting with the development of ILIAS 5.2. The first step to get rid of globals
will be the move to a global registry provided via pimple. A script that helps
you to move your component to the global registry pattern is provided 
[here](https://github.com/ILIAS-eLearning/DeveloperTools/tree/master/global_to_dic).

## Usage

The container can be accessed via the global `$DIC`. It provides an array access
interface, i.e. it can basically be used like the $GLOBALS-array.

The standard pimple interface is enhanced with explicit methods to get the most
common ILIAS services to help IDEs detect the types of services retreived from
the container.

```php

// retrieve the last global we'll ever need
global $DIC;

// get some handy ilias services via the special methods...
$ilDB = $DIC->db();
$ilCtrl = $DIC->ctrl();
$ilUser = $DIC->user();
$tree = $DIC->tree();
$lng = $DIC->language();
$ilAccess = $DIC->access();
$ilTabs = $DIC->tabs();
$ilToolbar = $DIC->toolbar();

// ... which are namespaced for rbac ...
$ilRbacSystem = $DIC->rbac()->system();
$ilRbacAdmin = $DIC->rbac()->admin();
$ilRbacReview = $DIC->rbac()->review();

// ... and loggers.
$root_logger = $DIC->logger()->root();
$grp_logger = $DIC->logger()->grp();
$crs_logger = $DIC->logger()->crs();
$tree = $DIC->logger()->tree();
// ... and similarly for other components that provide logging

// Use the array access for other globals:
$objDefinition = $DIC["objDefinition"];

```

## Local Containers

Components which need a container of their own (a "local DIC") extend or instantiate
`ILIAS\DI\ServiceContainer`, which is also the base of the global `ILIAS\DI\Container`.
They MUST NOT use the classes of pimple directly, so the library stays an
implementation detail of this component.

```php
use ILIAS\DI\ServiceContainer;

class MyComponentDIC extends ServiceContainer
{
    public function __construct()
    {
        parent::__construct();
        $this['repository'] = static fn(ServiceContainer $c): Repository => new DBRepository();
    }
}
```

Code which expects the global `$DIC` types against `ILIAS\DI\Container`.
