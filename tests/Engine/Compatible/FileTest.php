<?php
/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 12-12-17
 * Time: 23:59
 */

namespace Srcoder\TemplateBridge\Engine\Compatible;

use PHPUnit\Framework\TestCase;
use Srcoder\TemplateBridge\Content;
use Srcoder\TemplateBridge\Data;

class FileTest extends TestCase
{

    public function testMethodNonExistent()
    {
        $file = new File(['test' => function() {
            return 'test';
        }]);

        $this->assertNull($file->__call('test2', []));
        $this->assertNull($file->test2());
    }

    public function testMethodEmpty()
    {
        $file = new File(['test' => function() {
            return 'test';
        }]);

        $this->assertSame('test', $file->__call('test', []));
        $this->assertSame('test', $file->test());
    }

    public function testMethod()
    {
        $file = new File(['test' => function($param) {
            return 'test' . $param;
        }]);

        $this->assertSame('testparam', $file->__call('test', ['param']));
        $this->assertSame('testparam', $file->test('param'));
    }

    public function testParent()
    {
        $parent = $this->getMockBuilder(\stdClass::class)
                ->setMethods(['test'])
                ->getMock();

        $file = $this->getMockBuilder(File::class)
                ->setConstructorArgs([], $parent)
                ->getMock();

        $file = new File([], $parent);

        $parent->expects($this->once())
                ->method('test')
                ->willReturn('pass');

        $this->assertSame('pass', $file->test());
    }

    public function testMethodOverrideParentMethod()
    {
        $parent = $this->getMockBuilder(\stdClass::class)
                ->setMethods(['test', 'test2'])
                ->getMock();

        $file = new File(['test' => function(){
            return 'something';
        }], $parent);

        $parent->expects($this->never())
                ->method('test')
                ->willReturn('pass');

        $parent->expects($this->once())
                ->method('test2')
                ->willReturn('pass');

        $this->assertSame('something', $file->test());
        $this->assertSame('pass', $file->test2());
    }

    public function testMagicMethod()
    {
        $file = new File(['__call' => function($method, $params) {
            return 'test:' . $method . '->' . $params[0];
        }]);

        $this->assertSame('test:piet->param', $file->__call('piet', ['param']));
        $this->assertSame('test:piet->param', $file->piet('param'));
    }

    public function testEmptyMethodsAndParentSetterGetter()
    {
        $file = new File();

        $this->assertNull($file->__set('test', 'param'));
        $file->test = 'param';
        $this->assertNull($file->__get('test'));
        $this->assertNull($file->test);
    }

    public function testMethodsAndParentSetterGetter()
    {
        $parent = $this->getMockBuilder(\stdClass::class)
                ->getMock();
        $parent->test = 'piet';

        $file = new File([], $parent);

        $this->assertSame('piet', $file->__get('test'));
        $this->assertNull($file->__set('test', 'pass'));
        $this->assertSame('pass', $file->__get('test'));
    }

    public function testRenderContent()
    {
        $file = new File();

        $content = $file->___render(__DIR__ . '/mocks/Content.php');

        $this->assertInstanceOf(Content::class, $content);
        $this->assertSame("test\n", $content->content());
        $this->assertFalse($content->isReturn());
    }

    public function testRenderParam()
    {
        $file = new File();

        $content = $file->___render(__DIR__ . '/mocks/Param.php', new Data(['hello' => 'world!']));
        $this->assertSame("world!", $content->content());
        $this->assertFalse($content->isReturn());
    }

    public function testRenderReturn()
    {
        $file = new File();

        $content = $file->___render(__DIR__ . '/mocks/Return.php', new Data(['hello' => 'world!']));
        $this->assertSame("world!", $content->content());
        $this->assertTrue($content->isReturn());
    }

}
