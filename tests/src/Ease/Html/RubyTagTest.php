<?php

namespace Test\Ease\Html;

use Ease\Html\RubyTag;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-17 at 23:59:27.
 */
class RubyTagTest extends PairTagTest
{
    /**
     * @var RubyTag
     */
    protected $object;
    public $rendered = '<ruby></ruby>';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new RubyTag();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }
}
