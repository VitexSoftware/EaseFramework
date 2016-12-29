<?php

namespace Test\Ease\TWB;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-17 at 23:59:41.
 */
class StatusMessagesTest extends \Test\Ease\Html\DivTest
{
    /**
     * @var StatusMessages
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new \Ease\TWB\StatusMessages();
        \Ease\Shared::instanced()->addStatusMessage('test');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Ease\TWB\StatusMessages::draw
     */
    public function testDraw($whatWant = null)
    {
        \Ease\Shared::instanced()->statusMessages = [];
        \Ease\Shared::instanced()->addStatusMessage('test');
        $this->assertEquals("\n".'<div><div class="MessageForUser" style="color: blue;" >test</div></div>',$this->object->getRendered());
        \Ease\Shared::instanced()->statusMessages = [];
        $this->assertEmpty($this->object->getRendered());
    }
}
