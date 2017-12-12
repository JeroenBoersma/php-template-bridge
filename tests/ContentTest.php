<?php
/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 12-12-17
 * Time: 20:47
 */

use Srcoder\TemplateBridge\Content;
use PHPUnit\Framework\TestCase;

class ContentTest extends TestCase
{

    public function testEmptyContent()
    {
        $content = new Content('');
        $this->assertEmpty($content->content(),'->content() should return empty');
    }

    public function testContent()
    {
        $content = new Content('content test');
        $this->assertSame('content test', $content->content(),'->content() should return expected value "content test');
    }

    public function testToString()
    {
        $content = $this->getMockBuilder(Content::class)
                ->setConstructorArgs(['testje'])
                ->setMethods(['content'])
                ->getMock();

        $content->expects($this->exactly(2))
                ->willReturn('test')
                ->method('content');

        $this->assertSame('test', $content->toString());
        $this->assertSame('test', $content->__toString());
    }

    public function testImmutable()
    {
        $content = new Content('test');
        $append = $content->append(' append');

        $this->assertSame('test', $content->content(), 'Expects original object content unchanged.');

        $this->assertInstanceOf(Content::class, $append, 'Expects append type to of Content.');
        $this->assertSame('test append', $append->content(), 'Expects append content to be "test append".');

        $this->assertNotSame($content, $append, 'Content and append to be immutable.');
    }

    public function testReturnTypeDefault()
    {
        $content = new Content('');
        $this->assertSame(false, $content->isReturn(),'Return should be false by default');
    }

    public function testReturnTypeEnabled()
    {
        $content = new Content('', true);
        $this->assertSame(true, $content->isReturn(),'Return should be true');
    }


    public function testReturnTypeDisabledAfterAppend()
    {
        $content = new Content('test', true);
        $append = $content->append(' append');

        $this->assertSame(true, $content->isReturn(),'Content should still be true');
        $this->assertSame(false, $append->isReturn(),'Append should not be true for return');
    }

}
