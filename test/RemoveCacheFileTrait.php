<?php
/**
 * @link      http://github.com/zfcampus/zf-development-mode for the canonical source repository
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZFTest\DevelopmentMode;

use org\bovigo\vfs\vfsStream;

trait RemoveCacheFileTrait
{
    /** @var string */
    private $projectDirName = 'project';

    public function setUpDefaultCacheFile()
    {
        $base = vfsStream::url($this->projectDirName);
        $config = <<< EOC
<?php
return [
    'module_listener_options' => [
        'cache_dir' => '{$base}/cache',
    ],
];
EOC;

        file_put_contents(vfsStream::url($this->projectDirName) . '/config/application.config.php', $config);
        file_put_contents(
            vfsStream::url($this->projectDirName) . '/cache/module-config-cache.php',
            '<' . "?php\nreturn [];"
        );
    }

    public function setUpCustomCacheFile()
    {
        $base = vfsStream::url($this->projectDirName);
        $config = <<< EOC
<?php
return [
    'module_listener_options' => [
        'cache_dir' => '{$base}/cache',
        'config_cache_key' => 'custom',
    ],
];
EOC;

        file_put_contents(vfsStream::url($this->projectDirName) . '/config/application.config.php', $config);
        file_put_contents(
            vfsStream::url($this->projectDirName) . '/cache/module-config-cache.custom.php',
            '<' . "?php\nreturn [];"
        );
    }

    public function setUpDefaultExpressiveCacheFile()
    {
        $cache = vfsStream::url($this->projectDirName) . '/data/config-cache.php';
        $config = <<< EOC
<?php
return [
    'config_cache_path' => '{$cache}',
];
EOC;

        file_put_contents(vfsStream::url($this->projectDirName) . '/config/application.config.php', $config);
        file_put_contents($cache, '<' . "?php\nreturn [];");
    }
}
