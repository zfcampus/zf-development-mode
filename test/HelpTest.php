<?php
/**
 * @link      http://github.com/zfcampus/zf-development-mode for the canonical source repository
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZFTest\DevelopmentMode;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\DevelopmentMode\Help;

class HelpTest extends TestCase
{
    public function testWritesToStdoutWhenCalledWithNoArguments()
    {
        $help = new Help();
        ob_start();
        $help();
        $output = ob_get_clean();
        $this->assertContains('Enable/Disable development mode.', $output);
    }

    public function testCanProvideAlternateStream()
    {
        $stream = fopen('php://memory', 'w+');
        $help = new Help();
        $help($stream);
        fseek($stream, 0);
        $this->assertContains('Enable/Disable development mode.', fread($stream, 4096));
    }
}
