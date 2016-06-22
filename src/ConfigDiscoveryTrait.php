<?php
/**
 * @link      http://github.com/zfcampus/zf-development-mode for the canonical source repository
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZF\DevelopmentMode;

use RuntimeException;

/**
 * Shared functionality for the Disable/Enable commands.
 */
trait ConfigDiscoveryTrait
{
    /**
     * @var null|array
     */
    private $applicationConfig;

    /**
     * @var string
     */
    private $applicationConfigPath = 'config/application.config.php';

    /**
     * @var string Base name for configuration cache.
     */
    private $configCacheBase = 'module-config-cache';

    /**
     * Removes the application configuration cache file, if present.
     *
     * @param string $configCacheFile
     */
    private function removeConfigCacheFile($configCacheFile)
    {
        if (! $configCacheFile || ! file_exists($configCacheFile)) {
            return;
        }

        unlink($configCacheFile);
    }

    /**
     * Retrieve the config cache file, if any.
     *
     * @return false|string
     */
    private function getConfigCacheFile()
    {
        $configCacheDir = $this->getConfigCacheDir();
        $configCacheKey = $this->getConfigCacheKey();

        if (empty($configCacheDir)) {
            return false;
        }

        $path = sprintf('%s/%s.', $configCacheDir, $this->configCacheBase);

        if (! empty($configCacheKey)) {
            $path .= $configCacheKey . '.';
        }

        return $path . 'php';
    }

    /**
     * Return the configured configuration cache directory, if any.
     *
     * @return null|string
     */
    public function getConfigCacheDir()
    {
        $config = $this->getApplicationConfig();
        if (! isset($config['module_listener_options']['cache_dir'])) {
            return;
        }

        return $config['module_listener_options']['cache_dir'];
    }

    /**
     * Return the configured configuration cache key, if any.
     *
     * @return null|string
     */
    private function getConfigCacheKey()
    {
        $config = $this->getApplicationConfig();
        if (! isset($config['module_listener_options']['config_cache_key'])) {
            return;
        }

        return $config['module_listener_options']['config_cache_key'];
    }

    /**
     * Return the application configuration.
     *
     * Raises an exception if retrieved configuration is not an array.
     *
     * @return array
     * @throws RuntimeException if config/application.config.php does not
     *     return an array
     */
    function getApplicationConfig()
    {
        if (null !== $this->applicationConfig) {
            return $this->applicationConfig;
        }

        $configFile = isset($this->projectDir)
            ? sprintf('%s/%s', $this->projectDir, $this->applicationConfigPath)
            : $this->applicationConfigPath;
        if (! file_exists($configFile)) {
            $this->applicationConfig = [];
            return $this->applicationConfig;
        }

        $this->applicationConfig = include $configFile;

        if (! is_array($this->applicationConfig)) {
            throw new RuntimeException(
                'Invalid configuration returned from config/application.config.php;' . PHP_EOL
                . 'is this a zendframework application?' . PHP_EOL
            );
        }

        return $this->applicationConfig;
    }
}
