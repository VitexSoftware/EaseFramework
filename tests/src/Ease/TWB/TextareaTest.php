<?php

namespace Test\Ease\TWB;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-17 at 23:59:37.
 */
class TextareaTest extends \Test\Ease\Html\TextareaTagTest
{
    /**
     * @var Textarea
     */
    protected $object;

    public $rendered = '<textarea name="test" class="form-control"></textarea>';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new \Ease\TWB\Textarea('test');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    /**
     * @covers Ease\TWB\Textarea::getTagName
     */
    public function testGetTagName()
    {
        $this->assertEquals('test', $this->object->getTagName());
    }
}
