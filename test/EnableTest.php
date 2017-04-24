<?php
/**
 * @link      http://github.com/zfcampus/zf-development-mode for the canonical source repository
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZFTest\DevelopmentMode;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContainer;
use PHPUnit\Framework\TestCase;
use ZF\DevelopmentMode\Enable;

class EnableTest extends TestCase
{
    use RemoveCacheFileTrait;

    /** @var string */
    private $projectDirName = 'project';

    /** @var vfsStreamContainer */
    private $projectDir;

    /** @var resource */
    private $errorStream;

    /** @var Enable */
    private $command;

    public function setUp()
    {
        $this->projectDir = vfsStream::setup($this->projectDirName, null, [
            'config' => [
                'autoload' => [],
            ],
            'cache' => [],
            'data' => [],
        ]);
        $this->errorStream = fopen('php://memory', 'w+');
        $this->command = new Enable(vfsStream::url($this->projectDirName), $this->errorStream);
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

    public function testIndicatesEnabledWhenDevelopmentConfigFileFound()
    {
        vfsStream::newFile(Enable::DEVEL_CONFIG)
            ->at($this->projectDir);
        $command = $this->command;
        $this->expectOutputString('Already in development mode!' . PHP_EOL);
        $this->assertSame(0, $command());
    }

    public function testRaisesErrorMessageIfMissingDevelopmentConfigDistFile()
    {
        $command = $this->command;
        $this->assertSame(1, $command());

        fseek($this->errorStream, 0);
        $this->assertContains(
            'MISSING "config/development.config.php.dist"',
            fread($this->errorStream, 4096)
        );
    }

    public function testRaisesErrorMessageIfApplicationConfigDoesNotReturnAnArrayDevelopmentModeIsNotEnabled()
    {
        vfsStream::newFile('config/development.config.php.dist')
            ->at($this->projectDir)
            ->setContent('<' . "?php\nreturn [];");
        vfsStream::newFile('config/application.config.php')
            ->at($this->projectDir)
            ->setContent('');
        $command = $this->command;
        $this->assertSame(1, $command(), 'Did not get expected return value from invoking enable');
        $this->assertFalse(
            file_exists(vfsStream::url($this->projectDirName . '/config/development.config.php')),
            'Distribution development config was copied to new file'
        );

        fseek($this->errorStream, 0);
        $this->assertContains(
            'Invalid configuration returned from config/application.config.php',
            fread($this->errorStream, 4096),
            'Unexpected error message'
        );
    }

    public function testWillCopyLocalAutoloadDistConfigIfPresent()
    {
        vfsStream::newFile('config/development.config.php.dist')
            ->at($this->projectDir)
            ->setContent('<' . "?php\nreturn [];");
        vfsStream::newFile('config/autoload/development.local.php.dist')
            ->at($this->projectDir)
            ->setContent('<' . "?php\nreturn [];");
        vfsStream::newFile('config/application.config.php')
            ->at($this->projectDir)
            ->setContent('<' . "?php\nreturn [];");
        $command = $this->command;

        $this->expectOutputString('You are now in development mode.' . PHP_EOL);
        $result = $command();
        $this->assertSame(
            0,
            $result,
            'Did not get expected return value from invoking enable; errors: ' . $this->readErrorStream()
        );
        $this->assertTrue(
            file_exists(vfsStream::url($this->projectDirName . '/config/development.config.php')),
            'Distribution development config was not copied to new file'
        );
        $this->assertTrue(
            file_exists(vfsStream::url($this->projectDirName . '/config/autoload/development.local.php')),
            'Distribution development local config was not copied to new file'
        );
    }

    public function testRemovesDefaultConfigCacheFileIfPresent()
    {
        vfsStream::newFile('config/development.config.php.dist')
            ->at($this->projectDir)
            ->setContent('<' . "?php\nreturn [];");
        $this->setUpDefaultCacheFile();
        $command = $this->command;

        $this->expectOutputString('You are now in development mode.' . PHP_EOL);
        $this->assertSame(0, $command(), 'Did not get expected return value from invoking enable');
        $this->assertTrue(
            file_exists(vfsStream::url($this->projectDirName . '/config/development.config.php')),
            'Distribution development config was not copied to new file'
        );
        $this->assertFalse(
            file_exists(vfsStream::url($this->projectDirName . '/cache/module-config-cache.php')),
            'Config cache file was not removed'
        );
    }

    public function testRemovesCustomConfigCacheFileIfPresent()
    {
        vfsStream::newFile('config/development.config.php.dist')
            ->at($this->projectDir)
            ->setContent('<' . "?php\nreturn [];");
        $this->setUpCustomCacheFile();
        $command = $this->command;

        $this->expectOutputString('You are now in development mode.' . PHP_EOL);
        $this->assertSame(0, $command(), 'Did not get expected return value from invoking enable');
        $this->assertTrue(
            file_exists(vfsStream::url($this->projectDirName . '/config/development.config.php')),
            'Distribution development config was not copied to new file'
        );
        $this->assertFalse(
            file_exists(vfsStream::url($this->projectDirName . '/cache/module-config-cache.custom.php')),
            'Config cache file was not removed'
        );
    }

    public function testDevelopmentModeEnabledWhenApplicationConfigNotFound()
    {
        vfsStream::newFile('config/development.config.php.dist')
            ->at($this->projectDir)
            ->setContent('<' . "?php\nreturn [];");
        $command = $this->command;

        $this->expectOutputString('You are now in development mode.' . PHP_EOL);
        $this->assertSame(0, $command(), 'Did not get expected return value from invoking enable');
        $this->assertTrue(
            file_exists(vfsStream::url($this->projectDirName . '/config/development.config.php')),
            'Distribution development config was not copied to new file'
        );
    }

    public function testRemovesDefaultExpressiveConfigCacheFileIfPresent()
    {
        vfsStream::newFile('config/development.config.php.dist')
            ->at($this->projectDir)
            ->setContent('<' . "?php\nreturn [];");
        $this->setUpDefaultExpressiveCacheFile();
        $command = $this->command;

        $this->expectOutputString('You are now in development mode.' . PHP_EOL);
        $this->assertSame(0, $command(), 'Did not get expected return value from invoking enable');
        $this->assertTrue(
            file_exists(vfsStream::url($this->projectDirName . '/config/development.config.php')),
            'Distribution development config was not copied to new file'
        );
        $this->assertFalse(
            file_exists(vfsStream::url($this->projectDirName . '/data/config-cache.php')),
            'Config cache file was not removed'
        );
    }
}
