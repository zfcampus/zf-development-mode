<?php
/**
 * @link      http://github.com/zfcampus/zf-development-mode for the canonical source repository
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZFTest\DevelopmentMode;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit_Framework_TestCase as TestCase;
use ZF\DevelopmentMode\Disable;

class DisableTest extends TestCase
{
    use RemoveCacheFileTrait;

    public function setUp()
    {
        $this->projectDir = vfsStream::setup('project', null, [
            'config' => [
                'autoload' => [],
            ],
            'cache' => [],
        ]);
        $this->errorStream = fopen('php://memory', 'w+');
        $this->configStub = '<' . "?php\nreturn [];";
        $this->command = new Disable(vfsStream::url('project'), $this->errorStream);
    }

    public function tearDown()
    {
        if (is_resource($this->errorStream)) {
            fclose($this->errorStream);
        }
    }

    public function readErrorStream()
    {
        fseek($this->errorStream, 0);
        return fread($this->errorStream, 4096);
    }

    public function testIndicatesDisabledWhenDevelopmentConfigFileNotFound()
    {
        $command = $this->command;
        $this->expectOutputString('Development mode was already disabled.' . PHP_EOL);
        $this->assertSame(0, $command());
    }

    public function testRaisesErrorMessageIfApplicationConfigDoesNotReturnAnArray()
    {
        file_put_contents(vfsStream::url('project/config/development.config.php'), $this->configStub);
        vfsStream::newFile('config/application.config.php')
            ->at($this->projectDir)
            ->setContent('');
        $command = $this->command;
        $this->assertSame(1, $command(), 'Did not get expected return value from invoking enable');
        $this->assertFalse(
            file_exists(vfsStream::url('project') . '/config/development.config.php'),
            'Distribution development config was not removed'
        );

        fseek($this->errorStream, 0);
        $this->assertContains(
            'Invalid configuration returned from config/application.config.php',
            fread($this->errorStream, 4096),
            'Unexpected error message'
        );
    }

    public function testWillRemoveLocalAutoloadDistConfigIfPresent()
    {
        file_put_contents(vfsStream::url('project/config/development.config.php'), $this->configStub);
        file_put_contents(vfsStream::url('project/config/autoload/development.local.php'), $this->configStub);
        file_put_contents(vfsStream::url('project/config/application.config.php'), $this->configStub);
        $command = $this->command;

        $this->expectOutputString('Development mode is now disabled.' . PHP_EOL);
        $result = $command();
        $this->assertSame(
            0,
            $result,
            'Did not get expected return value from invoking enable; errors: ' . $this->readErrorStream()
        );
        $this->assertFalse(
            file_exists(vfsStream::url('project/config/development.config.php')),
            'Distribution development config was not removed'
        );
        $this->assertFalse(
            file_exists(vfsStream::url('project/config/autoload/development.local.php')),
            'Distribution development local config was not removed'
        );
    }

    public function testRemovesDefaultConfigCacheFileIfPresent()
    {
        file_put_contents(vfsStream::url('project/config/development.config.php'), $this->configStub);
        $this->setUpDefaultCacheFile();
        $command = $this->command;

        $this->expectOutputString('Development mode is now disabled.' . PHP_EOL);
        $this->assertSame(0, $command(), 'Did not get expected return value from invoking enable');
        $this->assertFalse(
            file_exists(vfsStream::url('project/config/development.config.php')),
            'Distribution development config was not removed'
        );
        $this->assertFalse(
            file_exists(vfsStream::url('project') . '/cache/module-config-cache.php'),
            'Config cache file was not removed'
        );
    }

    public function testRemovesCustomConfigCacheFileIfPresent()
    {
        file_put_contents(vfsStream::url('project/config/development.config.php'), $this->configStub);
        $this->setUpCustomCacheFile();
        $command = $this->command;

        $this->expectOutputString('Development mode is now disabled.' . PHP_EOL);
        $this->assertSame(0, $command(), 'Did not get expected return value from invoking enable');
        $this->assertFalse(
            file_exists(vfsStream::url('project/config/development.config.php')),
            'Distribution development config was not removed'
        );
        $this->assertFalse(
            file_exists(vfsStream::url('project') . '/cache/module-config-cache.custom.php'),
            'Config cache file was not removed'
        );
    }
}
