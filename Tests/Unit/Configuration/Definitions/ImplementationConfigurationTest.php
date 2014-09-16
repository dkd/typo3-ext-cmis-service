<?php
namespace Dkd\CmisService\Tests\Unit\Configuration\Definitions;

use Dkd\CmisService\Configuration\Definitions\ImplementationConfiguration;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ImplementationConfigurationTest
 */
class ImplementationConfigurationTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsQueueImplementationSetting() {
		$instance = new ImplementationConfiguration();
		$class = 'foobar';
		$instance->setDefinitions(array(
			ImplementationConfiguration::OBJECT_CLASS_QUEUE => $class
		));
		$this->assertEquals('foobar', $instance->get(ImplementationConfiguration::OBJECT_CLASS_QUEUE));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsWorkerImplementationSetting() {
		$instance = new ImplementationConfiguration();
		$class = 'foobar';
		$instance->setDefinitions(array(
			ImplementationConfiguration::OBJECT_CLASS_WORKER => $class
		));
		$this->assertEquals('foobar', $instance->get(ImplementationConfiguration::OBJECT_CLASS_WORKER));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsLoggerImplementationSetting() {
		$instance = new ImplementationConfiguration();
		$class = 'foobar';
		$instance->setDefinitions(array(
			ImplementationConfiguration::OBJECT_CLASS_LOGGER => $class
		));
		$this->assertEquals('foobar', $instance->get(ImplementationConfiguration::OBJECT_CLASS_LOGGER));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function containsValidDefaults() {
		$instance = new ImplementationConfiguration();
		$this->assertTrue(class_exists($instance->get(ImplementationConfiguration::OBJECT_CLASS_QUEUE)));
		$this->assertTrue(class_exists($instance->get(ImplementationConfiguration::OBJECT_CLASS_WORKER)));
		$this->assertTrue(class_exists($instance->get(ImplementationConfiguration::OBJECT_CLASS_LOGGER)));
	}

}
