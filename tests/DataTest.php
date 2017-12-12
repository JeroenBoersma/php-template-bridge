<?php
/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 12-12-17
 * Time: 21:20
 */

use Srcoder\TemplateBridge\Data;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{

    protected $content = ['test' => 'content'];

    /** @var Data */
    protected $data;

    public function setUp()
    {
        $this->data = new Data($this->content);
    }

    public function testConstructor()
    {
        $data = $this->data;
        $this->assertSame($this->content, $data->data(), 'Data should be set in object');
    }

    public function testSetter()
    {
        $data = $this->data;

        $new = $data->setData('new', 'value');

        $this->assertInstanceOf(Data::class, $new);
        $this->assertNotSame($data, $new, 'Object should be immutable');

        $this->assertFalse($data->hasData('new'), 'Original object should not have "new" key');
        $this->assertTrue($new->hasData('new'), 'New object should have "new" key');
    }

    public function testHasData()
    {
        $data = $this->data;
        $this->assertTrue($data->hasData('test'), 'test should return true on hasData');
        $this->assertFalse($data->hasData('notdefined'), 'notdefined should return false on hasData');
    }

    public function testGetData()
    {
        $data = $this->data;
        $this->assertSame($this->content['test'], $data->getData('test'), 'Getter should return correct content');
    }

    public function testGetDataOnUndefined()
    {
        $data = $this->data;
        $this->assertNull($data->getData('undefined'), 'Getter on undefined key should return null');
    }

}
