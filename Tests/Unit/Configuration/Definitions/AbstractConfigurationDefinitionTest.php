<?php
namespace Dkd\CmisService\Tests\Unit\Configuration\Definitions;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class AbstractConfigurationDefinitionTest
 */
class AbstractConfigurationDefinitionTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function setDefinitionsUpdatesInternalProperty() {
		$mock = $this->getMockForAbstractClass('Dkd\\CmisService\\Configuration\\Definitions\\AbstractConfigurationDefinition');
		$definitions = array('foo' => 'bar');
		$mock->setDefinitions($definitions);
		$this->assertAttributeEquals($definitions, 'definitions', $mock);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getSupportsDottedPath() {
		$mock = $this->getMockForAbstractClass('Dkd\\CmisService\\Configuration\\Definitions\\AbstractConfigurationDefinition');
		$definitions = array('foo' => array('bar' => 'baz'));
		$mock->setDefinitions($definitions);
		$result = $mock->get('foo.bar');
		$this->assertEquals('baz', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getReadsFromDefaultsIfValueIsEmptyButNotZero() {
		$mock = $this->getAccessibleMockForAbstractClass(
			'Dkd\\CmisService\\Configuration\\Definitions\\AbstractConfigurationDefinition'
		);
		$mock->_set('defaults', array('foo' => 'baz', 'foo2' => 'bar'));
		$definitions = array('foo' => '0', 'foo2' => NULL);
		$mock->setDefinitions($definitions);
		$this->assertEquals('0', $mock->get('foo'));
		$this->assertEquals('bar', $mock->get('foo2'));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function readingFromDefaultsSupportsDottedPath() {
		$mock = $this->getAccessibleMockForAbstractClass(
			'Dkd\\CmisService\\Configuration\\Definitions\\AbstractConfigurationDefinition'
		);
		$mock->_set('defaults', array('foo' => array('bar' => 'baz')));
		$definitions = array('foo' => array('bar' => NULL));
		$mock->setDefinitions($definitions);
		$this->assertEquals('baz', $mock->get('foo.bar'));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function readingFromDefaultsSupportsDottedKeys() {
		$mock = $this->getAccessibleMockForAbstractClass(
			'Dkd\\CmisService\\Configuration\\Definitions\\AbstractConfigurationDefinition'
		);
		$mock->_set('defaults', array('foo.bar' => 'baz'));
		$definitions = array('foo' => array('bar' => NULL));
		$mock->setDefinitions($definitions);
		$this->assertEquals('baz', $mock->get('foo.bar'));
	}

}
