<?php

namespace Test\Ease\TWB;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-17 at 23:59:31.
 */
class NavbarTest extends \Test\Ease\Html\DivTest
{
    /**
     * @var Navbar
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new \Ease\TWB\Navbar('Navbar');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testConstructor()
    {
        $classname = get_class($this->object);

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mock->__construct('Name');

        $mock->__construct('Name', 'Brand', ['class' => 'test']);
    }

    /**
     * @covers Ease\TWB\Navbar::NavBarHeader
     *
     * @todo   Implement testNavBarHeader().
     */
    public function testNavBarHeader()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\TWB\Navbar::addItem
     *
     * @todo   Implement testAddItem().
     */
    public function testAddItem()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\TWB\Navbar::addMenuItem
     *
     * @todo   Implement testAddMenuItem().
     */
    public function testAddMenuItem()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\TWB\Navbar::addDropDownSubmenu
     *
     * @todo   Implement testAddDropDownSubmenu().
     */
    public function testAddDropDownSubmenu()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\TWB\Navbar::addDropDownMenu
     *
     * @todo   Implement testAddDropDownMenu().
     */
    public function testAddDropDownMenu()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\TWB\Navbar::isEmpty
     */
    public function testIsEmpty()
    {
        $this->object->emptyContents();
        $this->assertTrue($this->object->isEmpty());
        $this->object->addItem('@');
        $this->assertTrue($this->object->isEmpty($this->object));
    }

    /**
     * @covers Ease\TWB\Navbar::getItemsCount
     */
    public function testGetItemsCount()
    {
        $this->object->emptyContents();
        $this->assertEquals(0, $this->object->getItemsCount());
        $this->object->addItem('@');
        $this->assertEquals(0, $this->object->getItemsCount());
        $this->assertEquals(2,
            $this->object->getItemsCount(new \Ease\Html\Div(['a', 'b'])));
    }

    /**
     * @covers Ease\TWB\Navbar::getTagName
     */
    public function testGetTagName()
    {
        $this->assertEquals('Navbar', $this->object->getTagName());
    }

    /**
     * @covers Ease\TWB\Navbar::draw
     */
    public function testDraw($whatWant = null)
    {
        parent::testDraw('
<nav class="navbar navbar-default" role="navigation" name="Navbar">
<div class="navbar-inner">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-Navbar-collapse">
<span class="sr-only">Switch navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span></button></div>
<div class="collapse navbar-collapse navbar-Navbar-collapse">
<ul class="nav navbar-nav"></ul>
<div class="pull-right">
<ul class="nav navbar-nav nav-right"></ul></div></div></div></nav>');
    }
}
