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
    public function setUpDefaultCacheFile()
    {
        $base = vfsStream::url('project');
        $config = <<< EOC
<?php
return [
    'module_listener_options' => [
        'cache_dir' => '{$base}/cache',
    ],
];
EOC;

        file_put_contents(vfsStream::url('project/config/application.config.php'), $config);
        file_put_contents(vfsStream::url('project/cache/module-config-cache.php'), '<' . "?php\nreturn [];");
    }

    public function setUpCustomCacheFile()
    {
        $base = vfsStream::url('project');
        $config = <<< EOC
<?php
return [
    'module_listener_options' => [
        'cache_dir' => '{$base}/cache',
        'config_cache_key' => 'custom',
    ],
];
EOC;

        file_put_contents(vfsStream::url('project/config/application.config.php'), $config);
        file_put_contents(vfsStream::url('project/cache/module-config-cache.custom.php'), '<' . "?php\nreturn [];");
    }
}
