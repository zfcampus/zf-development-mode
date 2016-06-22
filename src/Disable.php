<?php
/**
 * @link      http://github.com/zfcampus/zf-development-mode for the canonical source repository
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZF\DevelopmentMode;

use RuntimeException;

class Disable
{
    use ConfigDiscoveryTrait;

    const DEVEL_CONFIG = 'config/development.config.php';
    const DEVEL_LOCAL  = 'config/autoload/development.local.php';

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
     * Disable development mode.
     *
     * @return int
     */
    public function __invoke()
    {
        $develConfig = $this->projectDir
            ? sprintf('%s/%s', $this->projectDir, self::DEVEL_CONFIG)
            : self::DEVEL_CONFIG;
        if (! file_exists($develConfig)) {
            // nothing to do
            echo 'Development mode was already disabled.', PHP_EOL;
            return 0;
        }

        $develLocalConfig = $this->projectDir
            ? sprintf('%s/%s', $this->projectDir, self::DEVEL_LOCAL)
            : self::DEVEL_LOCAL;
        if (file_exists($develLocalConfig)) {
            // optional application config override
            unlink($develLocalConfig);
        }

        unlink($develConfig);

        try {
            $this->removeConfigCacheFile($this->getConfigCacheFile());
        } catch (RuntimeException $e) {
            fwrite($this->errorStream, $e->getMessage());
            return 1;
        }

        echo 'Development mode is now disabled.', PHP_EOL;
        return 0;
    }
}
