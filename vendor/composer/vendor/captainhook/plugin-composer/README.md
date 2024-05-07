# Composer-Plugin for [CaptainHook](https://github.com/captainhookphp/captainhook)

This is a composer-plugin that makes sure your team mates install the git hooks. For more information visit its [Website](https://github.com/captainhookphp/captainhook).

[![Latest Stable Version](https://poser.pugx.org/captainhook/plugin-composer/v/stable.svg?v=1)](https://packagist.org/packages/captainhook/plugin-composer)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.1-8892BF.svg)](https://php.net/)
[![Downloads](https://img.shields.io/packagist/dt/captainhook/plugin-composer.svg?v1)](https://packagist.org/packages/captainhook/plugin-composer)
[![License](https://poser.pugx.org/captainhook/plugin-composer/license.svg?v=1)](https://packagist.org/packages/captainhook/plugin-composer)

## Installation:

As this is a composer-plugin the preferred method is to use composer for installation.
 
```bash
$ composer require --dev captainhook/plugin-composer
```

Everything else will happen automagically.

## Customize

You can set a custom name for your hook configuration.
If you want to use the PHAR release of `CaptainHook` you can configure the path to the PHAR file.
All extra config settings are optional and if you are using the default settings you do not have to 
configure anything to make it work.
 
```json
{
  "extra": {
    "captainhook": {
      "config": "hooks.json",
      "exec": "tools/captainhook.phar",
      "disable-plugin": false
    }    
  }  
}

```

## A word of warning

It is still possible to commit without invoking the hooks. 
So make sure you run appropriate backend-sanity checks on your code!
