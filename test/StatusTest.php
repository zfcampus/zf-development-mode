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
use ZF\DevelopmentMode\Status;

class StatusTest extends TestCase
{
    public function setUp()
    {
        $this->projectDir = vfsStream::setup('project');
    }

    public function testIndicatesEnabledWhenDevelopmentConfigFileFound()
    {
        vfsStream::newFile(Status::DEVEL_CONFIG)
            ->at($this->projectDir);
        $status = new Status(vfsStream::url('project'));
        ob_start();
        $status();
        $output = ob_get_clean();
        $this->assertContains('ENABLED', $output);
    }

    public function testIndicatesDisabledWhenDevelopmentConfigFileNotFound()
    {
        $status = new Status();
        ob_start();
        $status();
        $output = ob_get_clean();
        $this->assertContains('DISABLED', $output);
    }
}
