<?php
/**
 * @link      http://github.com/zfcampus/zf-development-mode for the canonical source repository
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZF\DevelopmentMode;

use RuntimeException;

class Enable
{
    use ConfigDiscoveryTrait;

    const DEVEL_CONFIG      = 'config/development.config.php';
    const DEVEL_CONFIG_DIST = 'config/development.config.php.dist';
    const DEVEL_LOCAL       = 'config/autoload/development.local.php';
    const DEVEL_LOCAL_DIST  = 'config/autoload/development.local.php.dist';

    /**
     * @var resource
     */
    private $errorStream;

    /**
     * @param string Path to project.
     */
    private $projectDir;

    /**
     * @param string $projectDir Location to resolve project from.
     * @param null|resource Stream to which to write errors; defaults to STDERR
     */
    public function __construct($projectDir = '', $errorStream = null)
    {
        $this->projectDir = $projectDir;
        $this->errorStream = is_resource($errorStream) ? $errorStream : STDERR;
    }

    /**
     * Enable development mode.
     *
     * @return int
     */
    public function __invoke()
    {
        $develConfig = $this->projectDir
            ? sprintf('%s/%s', $this->projectDir, self::DEVEL_CONFIG)
            : self::DEVEL_CONFIG;
        if (file_exists($develConfig)) {
            // nothing to do
            echo 'Already in development mode!', PHP_EOL;
            return 0;
        }

        $develConfigDist = $this->projectDir
            ? sprintf('%s/%s', $this->projectDir, self::DEVEL_CONFIG_DIST)
            : self::DEVEL_CONFIG_DIST;
        if (! file_exists($develConfigDist)) {
            fwrite(
                $this->errorStream,
                'MISSING "config/development.config.php.dist". Could not switch to development mode!' . PHP_EOL
            );
            return 1;
        }

        copy($develConfigDist, $develConfig);

        $develLocalDist = $this->projectDir
            ? sprintf('%s/%s', $this->projectDir, self::DEVEL_LOCAL_DIST)
            : self::DEVEL_LOCAL_DIST;
        if (file_exists($develLocalDist)) {
            // optional application config override
            $develLocal = $this->projectDir
                ? sprintf('%s/%s', $this->projectDir, self::DEVEL_LOCAL)
                : self::DEVEL_LOCAL;
            copy($develLocalDist, $develLocal);
        }

        try {
            $this->removeConfigCacheFile($this->getConfigCacheFile());
        } catch (RuntimeException $e) {
            fwrite($this->errorStream, $e->getMessage());
            return 1;
        }

        echo 'You are now in development mode.', PHP_EOL;
        return 0;
    }
}
