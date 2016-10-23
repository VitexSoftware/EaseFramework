<?php

namespace Test\Ease;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-10-23 at 14:10:35.
 */
class MailerTest extends PageTest
{
    /**
     * @var Mailer
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new \Ease\Mailer('info@vitexsoftware.cz', 'Unit Test');
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
        $mock->__construct('info@vitexsoftware.cz', 'Unit Test');

        $mock->__construct('vitex@hippy.cz', 'Hallo', 'PHPUnit works well!');
    }

    /**
     * @covers Ease\Mailer::getMailHeader
     * @todo   Implement testGetMailHeader().
     */
    public function testGetMailHeader()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\Mailer::setMailHeaders
     * @todo   Implement testSetMailHeaders().
     */
    public function testSetMailHeaders()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\Mailer::addItem
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
     * @covers Ease\Mailer::addFile
     * @todo   Implement testAddFile().
     */
    public function testAddFile()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\Mailer::finalize
     * @todo   Implement testFinalize().
     */
    public function testFinalize()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\Mailer::draw
     * @todo   Implement testDraw().
     */
    public function testDraw()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\Mailer::send
     * @todo   Implement testSend().
     */
    public function testSend()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }



    /**
     * @covers Ease\Mailer::setUserNotification
     * @todo   Implement testSetUserNotification().
     */
    public function testSetUserNotification()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\Mailer::getItemsCount
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
     * @covers Ease\Mailer::isEmpty
     */
    public function testIsEmpty()
    {
        $this->object->emptyContents();
        $this->assertTrue($this->object->isEmpty());
        $this->object->addItem('@');
        $this->assertTrue($this->object->isEmpty($this->object));
    }
}
