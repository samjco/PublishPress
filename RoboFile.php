<?php

require 'vendor/autoload.php';

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends PressShack\Builder\Robo_Task {

	public function __construct() {
		$this->plugin_name      = 'publishpress';
		$this->version_constant = 'PUBLISHPRESS_VERSION';
	}
	
	public function testUnit() {
		$return = $this->taskCodecept()
			->suite('unit')
			->html('report_unit.html')
			->run();

		return $return;
	}

	public function testWPUnit() {
		$this->taskCodecept()
			->suite('wpunit')
			->html('report_wpunit.html')
			->run();
	}

	public function testFunctional() {
		$this->taskCodecept()
			->suite('functional')
			->html('report_functional.html')
			->run();
	}

	public function testAcceptance() {
		$this->taskCodecept()
			->suite('acceptance')
			->html('report_acceptance.html')
			->run();
	}

	public function testAll() {
		$this->testUnit();
		$this->testWPUnit();
		$this->testFunctional();
		$this->testAcceptance();
	}
}
