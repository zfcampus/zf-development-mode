<?php
/**
 * @link      http://github.com/zfcampus/zf-development-mode for the canonical source repository
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZF\DevelopmentMode;

class Help
{
    /**
     * @var string
     */
    private $message = <<< EOH
Enable/Disable development mode.

Usage:

development-mode [-h|--help] disable|enable|status

--help|-h                    Print this usage message.
disable                      Disable development mode.
enable                       Enable development mode
                             (do not use in production).
status                       Determine if development mode is currently
                             enabled.

To enable development mode, the following file MUST exist:

- config/development.config.php.dist; this file will be copied to
  config/development.config.php

Optionally:

- config/autoload/development.local.php.dist; this file will be copied to
  config/autoload/development.local.php

When disabling development mode:

- config/development.config.php will be removed if it exists
- config/autoload/development.local.php will be removed if it exists

Additionally, both when disabling and enabling development mode, the
script will remove the file cache/module-config-cache.php (or the file
specified by the combination of the module_listener_options.cache_dir
and module_listener_options.config_cache_key options).

EOH;

    /**
     * Emit the help message.
     *
     * @param null|resource $stream Defaults to STDOUT
     */
    public function __invoke($stream = null)
    {
        if (! is_resource($stream)) {
            echo $this->message;
            return;
        }

        fwrite($stream, $this->message);
    }
}
