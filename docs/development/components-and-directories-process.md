# Implementation of new structure of Components and Directories

This document describes the implementation of the new Components and Directories structure.

1. [Creation of root directories](#1-creation-of-root-directories)
2. [dicto](#2-dicto)
3. [Modules and Services](#3-modules-and-services)
4. [Customizing > global > plugin](#4-customizingglobalplugin)
5. [include](#5-include)
6. [CI](#6-ci)
7. [sso](#7-sso)
8. [cron](#8-cron)
9. [src](#9-src)
10. [xml](#10-xml)
11. [webservice](#11-webservice)
12. [data](#12-data)
13. [tests](#13-tests)
14. [libs](#14-libs)
15. [setup](#15-setup)
16. [wopi](#16-wopi)
16. [Final Notes](#17-final-notes)


## 1. Creation of root directories

Creation of following new directories in ILIAS root:

* `cli`
* `components/ILIAS`
* `scripts`
* `vendor`
* `public`

mkdir -m 777 cli components public scripts vendor
mkdir -m 777 components/ILIAS

-----------

## 2. ./dicto

Delete `dicto` directory incl. files as it's not used anymore.
Remove all references to `dicto` incl. files and sub-directories.

-----------

## 3. ./Modules and ./Services

Moving contents of `Modules` and `Services` to `components/ILIAS` folder.
Every link to `Modules` and `Services` where changed to linking to `components/ILIAS`. This also includes changing the linking
the classmap of `composer.json`.
To avoid duplicated directory-names some old Services/Modules components will be renamed to {COMPONENTNAME_}, e.g.: `BackgroundTasks_`, to differentiate those to the components from the old `src`
directory (see [Adjusting ./src directoy](#9-src-and-tests)). This will only be the case for following components =>

- BackgroundTasks
- Cache
- GlobalCache
- GlobalScreen
- KioskMode
- Language
- ResourceStorage
- UI
- VirusScanner


Adjustments via search and refactoring all results:

* ./Modules/
* "Modules/
* 'Modules/
* "Modules"
* 'Modules'
* .Modules
* @package Modules/
* ./Services/
* "Services/
* 'Services/
* "Services"
* 'Services'
* .Services
* @package Services/

Search and refactor single results:

* Modules
* Services


### Update DB content for "Modules":

#### ilObjectDBUpdateSteps:

```
    public function step_2(): void
    {
        if ($this->db->tableExists("il_object_def")) {
            $query = "UPDATE il_object_def SET " . PHP_EOL
            . " component = REPLACE(component, 'Modules', 'components/ILIAS'), " . PHP_EOL
            . " location = REPLACE(location, 'Modules', 'components/ILIAS')" . PHP_EOL
            . " WHERE component LIKE ('Modules/%')";

            $this->db->manipulate($query);
        }
    }
```

#### ilCOPageDBUpdateSteps:
```
    public function step_13(): void
    {
        if ($this->db->tableExists("copg_pc_def")) {
            $query = "UPDATE copg_pc_def SET " . PHP_EOL
                . " component = REPLACE(component, 'Modules', 'components/ILIAS') " . PHP_EOL
                . " WHERE component LIKE ('Modules/%')";
            
            $this->db->manipulate($query);
        }
    }
```

#### ilStudyProgrammePCStatusInfoUpdateSteps:

```
    public function step_2(): void
    {
        if ($this->db->tableExists("copg_pc_def")) {
            $query = "UPDATE " . self::TABLE_NAME . " SET " . PHP_EOL
                . " component = REPLACE(component, 'Modules', 'components/ILIAS') " . PHP_EOL
                . " WHERE component LIKE ('Modules/%')";

            $this->db->manipulate($query);
        }
    }
```

#### ilMailDatabaseUpdateSteps:

```
    public function step_13(): void
    {
        if ($this->db->tableExists("mail_tpl_ctx")) {
            $query = "UPDATE mail_tpl_ctx SET " . PHP_EOL
                . " component = REPLACE(component, 'Modules', 'components/ILIAS') " . PHP_EOL
                . " WHERE component LIKE ('Modules/%')";

            $this->db->manipulate($query);
        }
    }

    public function step_14(): void
    {
        if ($this->db->tableExists("mail_tpl_ctx")) {
            $query = "UPDATE mail_tpl_ctx SET " . PHP_EOL
                . " path = REPLACE(path, 'Modules', 'components/ILIAS') " . PHP_EOL
                . " WHERE component LIKE ('Modules/%')" . PHP_EOL
                . " AND path IS NOT NULL";

            $this->db->manipulate($query);
        }
    }
```

### Update DB content for "Services":

#### ilObjectDBUpdateSteps:

```
     public function step_3(): void
    {
        $query = "UPDATE il_object_def SET " . PHP_EOL
            . " component = REPLACE(component, 'Services', 'components/ILIAS'), " . PHP_EOL
            . " location = REPLACE(location, 'Services', 'components/ILIAS')" . PHP_EOL
            . " WHERE component LIKE ('Services/%')";

        $this->db->manipulate($query);
    }

    public function step_4(): void
    {
        $query = "UPDATE il_object_def SET " . PHP_EOL
            . " component = REPLACE(component, 'Language', 'Language_'), " . PHP_EOL
            . " location = REPLACE(location, '/classes', '_/classes')" . PHP_EOL
            . " WHERE component LIKE ('%Language')";

        $this->db->manipulate($query);
    }
```

#### ilCOPageDBUpdateSteps:
```
    public function step_13(): void
    {
        if ($this->db->tableExists("copg_pc_def")) {
            $query = "UPDATE copg_pc_def SET " . PHP_EOL
                . " component = REPLACE(component, 'Services', 'components/ILIAS') " . PHP_EOL
                . " WHERE component LIKE ('Services/%')";
            
            $this->db->manipulate($query);
        }
    }
```

#### ilStudyProgrammePCStatusInfoUpdateSteps:

```
    public function step_2(): void
    {
        if ($this->db->tableExists("copg_pc_def")) {
            $query = "UPDATE " . self::TABLE_NAME . " SET " . PHP_EOL
                . " component = REPLACE(component, 'Services', 'components/ILIAS') " . PHP_EOL
                . " WHERE component LIKE ('Services/%')";

            $this->db->manipulate($query);
        }
    }
```


-----------
##### Note:
The following new structure won't implemented in the first PR / process step:
* `/docs` for component specific documentation
* `/resources` for auxiliary files like icons, endpoints
* `/src` for the code to be used for production
* `/tests` for code that performs automated tests
* a `$COMPOMENT.php`
* a `component.json`
* a `README.md`


#### Testing
* Running `composer du` and `php setup/setup.php migrate` via Terminal successful.
* Log out and log in to instance was successful.
* View the instance's root repository was successful.
* View some administration settings was successful.
* Creating a course was successful.
* Installation of a second language was successful.

-----------

## 4. ./Customizing/global/plugin

The plugin directory stays at `Customizing/global/plugin` for now and will later on moved to
`components/{SERVICE_PROVIDER}`.
The name {SERVICE_PROVIDER} will be a placeholder for unique provider, who will create their own plugins.

-----------

## 5. ./include

Move `include` to `cli` directory and adjust references.


#### Testing
* Running `composer du` and `php setup/setup.php migrate` via Terminal successful.
* Log out and log in to instance was successful.
* View the instance's root repository was successful.
* View some administration settings was successful.
* Creating a course was successful.

-----------

## 6. ./CI

Move contents of `CI` to `scripts` directory and adjust references.


#### Testing
* Running `composer du` and `php setup/setup.php migrate` via Terminal successful.
* Log out and log in to instance was successful.
* View the instance's root repository was successful.
* View some administration settings was successful.
* Creating a course was successful.

-----------

## 7. ./sso

Move `sso` directory to `cli` directory and adjust references.

-----------
### Testing
* Running `composer du` and `php setup/setup.php migrate` via Terminal successful.
* Log out and log in to instance was successful.
* View the instance's root repository was successful.
* View some administration settings was successful.
* Creating a course was successful.

-----------

## 8. ./cron

Move `cron` directory content to `cli` directory and adjust references.


#### Testing
* Running `composer du` and `php setup/setup.php migrate` via Terminal successful.
* Log out and log in to instance was successful.
* View the instance's root repository was successful.
* Running Cron-Job "Course/Group: Minimum Members Check" was successful.

-----------

<a name="src-and-tests"></a>
## 9. ./src

Create missing directories/files for components and move files in `src` and from `tests` directory to newly generated directories.

```
#!/bin/bash
for d in $(find -maxdepth 1 -type d);
do
mkdir -m 777 $d/docs
mkdir -m 777 $d/test
mkdir -m 777 $d/resources
mkdir -m 777 $d/src
done
```

#### Note: The structure of the components should look like following:
* `/docs` for component specific documentation
* `test` for code that performs automated tests
* a `$COMPOMENT.php`
* a `component.json`
* a `README.md`

As long as `/resources` and `/src` are not created in the corresponding components in `components/ILIAS` they won't be created here too to avoid psr-4 autoloading standard errors.

-----------

## 10. ./xml

Move `xml` contents to `components/ILIAS/Export/xml` directory and adjust references.
Adjust assemblage of Exporter and ExportConfig files in `class.ilExport.php` as the origin assemblage would result
to wrong file names, e.g.: instead of `class.ilMetaDataExporter.php` it would assembly to `class.ilILIASExporter.php` and
run into errors. Also adjust class.ilImportExportFactory (L. 59) => getExporterClass() => remove "_" from $class variable.

Export successfully tested with a course.


#### Testing
* Running `composer du` and `php setup/setup.php migrate` via Terminal successful.
* Log out and log in to instance was successful.
* View the instance's root repository was successful.
* Exporting course from instance was successful
* Downloading export file was successful
    * Check if export file is filled with data was successful

-----------

## 11. ./webservice

Move soap `webservice` to `components/ILIAS` except for nusoapserver.php and server.php. Those last two mentioned files were
moved to `public` and adjust references.
Delete the `webservice` directory.
Update `settings` DB-Table =>
module => common
keyword => soap_wsdl_path
value => adjust link to http://INSERT_IP/public/server.php?wsdl

```
public function step_1(): void
    {
        if ($this->db->tableExists('settings')) {
            $query = "UPDATE settings SET " . PHP_EOL
                . " value = REPLACE(value, '/webservice/', '/public/') " . PHP_EOL
                . " WHERE keyword = 'soap_wsdl_path' " . PHP_EOL
                . " AND value LIKE ('%webservice%')";

            $this->db->manipulate($query);
        }
    }
```    


#### Testing
* Running `composer du` and `php setup/setup.php migrate` via Terminal successful.
* Log out and log in to instance was successful.
* View the instance's root repository was successful.
* Creating a study programme was successful.

-----------

## 12. ./data

Move `data` contents to `cli` directory and adjust references.

Adjust ilias.ini.php, L. 8 =>
``` 
[clients]
path = "public/data"
```

-----------

<a name="src-and-tests"></a>
## 13. ./tests

Move files from `tests` directory to old src-components in `components/ILIAS`.


<a name="note-2"></a>
#### Note 2:
Rename `tests`'s README.md to `unit-test-usage` and move to `docs > development` directory.

Move `ILIASSuite.php` from `tests` directory to `scripts` directory and adjust references.
Adjust `ILIASSuite.php` and rename Suites in components/ILIAS to `ilComponents{COMPONENTNAME}Suite.php`.

#### Adjust Yaml Parser

L. 284 (getEntryFromData(array ...)) =>
``` 
  $entry_data['path'] = str_replace("/ILIAS/UI", "components/ILIAS/UI/src", str_replace("\\", "/", $entry_data['namespace']));

  if (str_contains($entry_data['path'], 'tests/UI/')) {
     $entry_data['path'] = str_replace("tests/UI/", "components/ILIAS/UI/test/", $entry_data['path']);
  }
```

-----------

## 14. ./libs

Move `ilias` and `composer` directory including content to `vendor` directory and adjust links/references.

#### Notes:

Check `.git` directory for references. It might be that the `.git` directory is not visible in the editor (e.g. PHPStorm). In PHPStorm: delete `.git` from
hidden folders in `File > Settings > Editor > File Types > Ignored Files/Folder`.


#### Testing
* Running `composer du` via Terminal successful.
* Log out and log in to instance was successful.
* View the instance's root repository was successful.
* Creating course was successful

-----------

## 15. ./setup

Rename `cli.php` to `setup.php` and move to `cli`.
Delete old `setup.php`.
Move rest of ./setup content to `components/ILIAS` and rename directory to `setup_`.
Adjust references.


#### Testing
* Running `composer du` and `php setup/setup.php migrate` via Terminal successful.
* Log out and log in to instance was successful.
* View the instance's root repository was successful.
* Creating course was successful

-----------

## 16. ./wopi

Move `wopi` to `cli`.
Adjust references.


#### Testing
* Running `composer du` and `php setup/setup.php migrate` via Terminal successful.
* Log out and log in to instance was successful.
* View the instance's root repository was successful.
* Creating course was successful

-----------

## 17. "Final" notes

* Local Instance is still running
* Creating e.g. a course was successful
* Log in and log out to local instance was successful
* `run_tests.sh` can be executed
* `setup.php update` and `setup.php migrate` can be executed
* `composer du` can be executed
