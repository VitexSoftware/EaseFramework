<?php

namespace Test\Ease\Html;

use Ease\Html\Form;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-17 at 23:59:19.
 */
class FormTest extends PairTagTest
{
    /**
     * @var Form
     */
    protected $object;
    public $rendered = '<form method="post" name="test"></form>';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new \Ease\Html\Form('test');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    /**
     * @covers Ease\Html\Form::setFormTarget
     *
     * @todo   Implement testSetFormTarget().
     */
    public function testSetFormTarget()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\Html\Form::changeActionParameter
     *
     * @todo   Implement testChangeActionParameter().
     */
    public function testChangeActionParameter()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\Html\Form::objectContentSearch
     *
     * @todo   Implement testObjectContentSearch().
     */
    public function testObjectContentSearch()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ease\Html\Form::finalize
     *
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
     * @covers Ease\Html\Form::getTagName
     */
    public function testGetTagName()
    {
        $this->assertEquals('test', $this->object->getTagName());
        $this->object->setName = true;
        $this->object->setTagName('Test');
        $this->assertEquals('Test', $this->object->getTagName());
    }

    /**
     * @covers Ease\Html\Form::fillUp
     */
    public function testFillUp()
    {
        $this->object->fillUp(['a' => 1, 'b' => 2]);
    }

    /**
     * @covers Ease\Html\Form::fillMeUp
     *
     * @todo   Implement testFillMeUp().
     */
    public function testFillMeUp()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
