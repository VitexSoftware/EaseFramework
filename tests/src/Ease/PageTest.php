<?php

namespace Test\Ease;

use Ease\Page;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-17 at 23:58:37.
 */
class PageTest extends ContainerTest
{
    /**
     * @var Page
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new Page();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        
    }

    /**
     * @covers Ease\Page::singleton
     */
    public function testSingleton()
    {
        if (get_class($this->object) == 'Ease\Page') {
            $this->assertInstanceOf(get_class($this->object), Page::singleton());
        }
    }

    /**
     * @covers Ease\Page::addJavaScript
     */
    public function testAddJavaScript()
    {
        $this->object->addJavaScript('alert("hallo");');
        $this->object->addJavaScript('alert("world");', false);
    }

    /**
     * @covers Ease\Page::includeJavaScript
     */
    public function testIncludeJavaScript()
    {
        $this->object->includeJavaScript('test.js');
    }

    /**
     * @covers Ease\Page::addCSS
     */
    public function testAddCSS()
    {
        $this->object->addCSS('.test {color:red;}');
    }

    /**
     * @covers Ease\Page::includeCss
     */
    public function testIncludeCss()
    {
        $this->object->includeCss('test.css');
    }

    /**
     * @covers Ease\Page::redirect
     */
    public function testRedirect()
    {
        $this->object->redirect('http://v.s.cz/');
    }

    /**
     * @covers Ease\Page::getUri
     */
    public function testGetUri()
    {
        $_SERVER['REQUEST_URI'] = 'test';
        Page::getUri();
    }

    /**
     * @covers Ease\Page::phpSelf
     */
    public function testPhpSelf()
    {
        Page::phpSelf();
    }

    /**
     * @covers Ease\Page::onlyForLogged
     */
    public function testOnlyForLogged()
    {
        $this->object->onlyForLogged();
    }

    /**
     * @covers Ease\Page::getRequestValues
     */
    public function testGetRequestValues()
    {
        $this->object->getRequestValues();
    }

    /**
     * @covers Ease\Page::isPosted
     */
    public function testIsPosted()
    {
        $_SERVER['REQUEST_METHOD'] = 'test';
        $this->assertFalse(\Ease\Page::isPosted());
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(\Ease\Page::isPosted());
    }

    /**
     * @covers Ease\Page::sanitizeAsType
     */
    public function testSanitizeAsType()
    {
        $this->assertInternalType('string',
            $this->object->sanitizeAsType('123', 'string'));
        $this->assertInternalType('integer',
            $this->object->sanitizeAsType('123', 'int'));
        $this->assertInternalType('boolean',
            $this->object->sanitizeAsType('0', 'boolean'));
        $this->assertFalse($this->object->sanitizeAsType('FALSE', 'boolean'));
        $this->assertTrue($this->object->sanitizeAsType('true', 'boolean'));
        $this->assertInternalType('float',
            $this->object->sanitizeAsType('1.45', 'float'));
        $this->assertNull($this->object->sanitizeAsType('', 'int'));
        $this->assertEquals('test', $this->object->sanitizeAsType('test', 'null'));
    }

    /**
     * @covers Ease\Page::getRequestValue
     */
    public function testGetRequestValue()
    {
        $_REQUEST['test'] = 'lala';
        $this->assertEquals('lala', $this->object->getRequestValue('test'));
    }

    /**
     * @covers Ease\Page::getGetValue
     */
    public function testGetGetValue()
    {
        $_GET['test'] = 'lolo';
        $this->assertEquals('lolo', $this->object->getGetValue('test'));
    }

    /**
     * @covers Ease\Page::getPostValue
     */
    public function testGetPostValue()
    {
        $_POST['test'] = 'lili';
        $this->assertEquals('lili', $this->object->getPostValue('test'));
    }

    /**
     * @covers Ease\Page::isFormPosted
     */
    public function testIsFormPosted()
    {
        unset($_POST);
        $this->assertFalse($this->object->isFormPosted());
        $_POST['test'] = 'lili';
        $this->assertTrue($this->object->isFormPosted());
    }

    /**
     * @covers Ease\Page::setOutputFormat
     */
    public function testSetOutputFormat()
    {
        $this->object->setOutputFormat('html');
    }

    /**
     * @covers Ease\Page::getOutputFormat
     */
    public function testGetOutputFormat()
    {
        $this->object->getOutputFormat();
    }

    /**
     * @covers Ease\Page::takeStatusMessages
     */
    public function testTakeStatusMessages()
    {
        $this->object->takeStatusMessages(['info' => ['test', 'test2']]);
    }

    /**
     * @covers Ease\Page::arrayToUrlParams
     */
    public function testArrayToUrlParams()
    {
        $this->object->arrayToUrlParams(['a' => 1, 'b' => 2], 'http://v.s.cz/');
    }

    public function testAddItem()
    {
        $items1                   = $this->object->getItemsCount();
        $this->object->addItem(new \Ease\Html\DivTag('test'));
        $items2                   = $this->object->getItemsCount();
        $this->assertEquals($items1 + 1, $items2);
        $this->object->pageClosed = true;
        $this->object->addItem(new \Ease\Html\DivTag('test'));
        $items3                   = $this->object->getItemsCount();
        $this->assertEquals($items3, $items2);
    }
}
