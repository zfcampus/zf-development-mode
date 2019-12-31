# zf-development-mode

> ## Repository abandoned 2019-12-31
>
> This repository has moved to laminas/laminas-development-mode.

> ## Repository abandoned 2019-12-31
>
> This repository has moved to laminas/laminas-development-mode.

[![Build Status](https://secure.travis-ci.org/zfcampus/zf-development-mode.svg?branch=master)](https://secure.travis-ci.org/zfcampus/zf-development-mode)
[![Coverage Status](https://coveralls.io/repos/github/zfcampus/zf-development-mode/badge.svg?branch=master)](https://coveralls.io/github/zfcampus/zf-development-mode?branch=master)
[![Total Downloads](https://poser.pugx.org/zfcampus/zf-development-mode/downloads)](https://packagist.org/packages/zfcampus/zf-development-mode)

This package provides a script to allow you to enable and disable development
mode for [zend-mvc](https://docs.zendframework.com/zend-mvc) (both versions 2
and 3) and [Expressive](https://docs.zendframework.com/zend-expressive)
applications. The script allows you to specify configuration and modules that
should only be enabled when in development, and not when in production.

## Note to v2 users

If you were using a v2 version of this package previously, invocation has
changed. Previously, you would invoke it via the MVC CLI bootstrap:

```bash
$ php public/index.php development enable  # enable development mode
$ php public/index.php development disable # disable development mode
```

v3 releases now install this as a vendor binary, with no dependencies on other
components:

```bash
$ ./vendor/bin/zf-development-mode enable  # enable development mode
$ ./vendor/bin/zf-development-mode disable # disable development mode
```

## Installation

Install this package using Composer:

```bash
$ composer require zfcampus/zf-development-mode
```

Once installed, you will need to copy a base development configuration into your
application; this configuration will allow you to override modules and bootstrap
configuration:

```bash
$ cp vendor/zfcampus/zf-development-mode/development.config.php.dist config/
```

Optionally, if you want to also have development-specific application
configuration, you can copy another base configuration into your configuration
autoload directory:

```bash
$ cp vendor/zfcampus/zf-development-mode/development.local.php.dist config/autoload/
```

In order for the bootstrap development configuration to run, you may need to
update your application bootstrap. Look for the following lines (or similar) in
`public/index.php`:

```php
// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
```

Replace the above with the following:

```php
// Config
$appConfig = include 'config/application.config.php';
if (file_exists('config/development.config.php')) {
    $appConfig = Zend\Stdlib\ArrayUtils::merge($appConfig, include 'config/development.config.php');
}

// Run the application!
Zend\Mvc\Application::init($appConfig)->run();
```

## To enable development mode

```bash
$ cd path/to/project
$ ./vendor/bin/zf-development-mode enable
```

Note: enabling development mode will also clear your module configuation cache,
to allow safely updating dependencies and ensuring any new configuration is
picked up by your application.

# To disable development mode

```bash
$ cd path/to/project
$ ./vendor/bin/zf-development-mode disable
```

**Note:** Don't run development mode on your production server!