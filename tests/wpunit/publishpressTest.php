<?php

class publishpressTest extends \Codeception\TestCase\WPTestCase
{

    public function setUp()
    {
        // before
        parent::setUp();

        // your set up methods here
    }

    public function tearDown()
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    // tests
    public function testClassExists()
    {
        $this->assertTrue(class_exists('publishpress'));
    }

    public function testGetInstanceReturnsCorrectClass()
    {
        $instance = publishpress::instance();
        $this->assertInstanceOf('publishpress', $instance);
    }

    public function testGetInstanceAlwaysReturnsSameInstance()
    {
        $instance1 = publishpress::instance();
        $instance2 = publishpress::instance();

        $this->assertSame($instance1, $instance2);
    }
}