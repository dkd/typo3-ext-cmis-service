<?php
namespace Dkd\CmisService\Tests\Unit\Configuration\Definitions;

use Dkd\CmisService\Configuration\Definitions\MasterConfiguration;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class MasterConfigurationTest
 */
class MasterConfigurationTest extends UnitTestCase {

	/**
	 * Gets instances of all required sub-Definitions
	 * as mocks.
	 *
	 * @return array
	 */
	protected function getSubDefinitionMocks() {
		$implementationMock = $this->getMock('Dkd\\CmisService\\Configuration\\Definitions\\ImplementationConfiguration');
		$tableMock = $this->getMock('Dkd\\CmisService\\Configuration\\Definitions\\TableConfiguration');
		$networkMock = $this->getMock('Dkd\\CmisService\\Configuration\\Definitions\\NetworkConfiguration');
		$cmisMock = $this->getMock('Dkd\\CmisService\\Configuration\\Definitions\\CmisConfiguration');
		$stanbolMock = $this->getMock('Dkd\\CmisService\\Configuration\\Definitions\\StanbolConfiguration');
		return array($implementationMock, $tableMock, $networkMock, $cmisMock, $stanbolMock);
	}

	/**
	 * Initialize an instance of MasterConfiguration with
	 * mocks as sub-Definitions.
	 *
	 * @return MasterConfiguration
	 */
	protected function getInitializedConfiguration() {
		list ($implementationMock, $tableMock, $networkMock, $cmisMock, $stanbolMock) = $this->getSubDefinitionMocks();
		$configuration = new MasterConfiguration();
		$configuration->initialize($implementationMock, $tableMock, $networkMock, $cmisMock, $stanbolMock);
		return $configuration;
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function initializeSetsInternalProperties() {
		$configuration = $this->getInitializedConfiguration();
		list ($implementationMock, $tableMock, $networkMock, $cmisMock, $stanbolMock) = $this->getSubDefinitionMocks();
		$this->assertAttributeEquals($implementationMock, 'implementationConfiguration', $configuration);
		$this->assertAttributeEquals($tableMock, 'tableConfiguration', $configuration);
		$this->assertAttributeEquals($networkMock, 'networkConfiguration', $configuration);
		$this->assertAttributeEquals($cmisMock, 'cmisConfiguration', $configuration);
		$this->assertAttributeEquals($stanbolMock, 'stanbolConfiguration', $configuration);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getImplementationConfigurationReturnsImplementationConfiguration() {
		$configuration = $this->getInitializedConfiguration();
		$result = $configuration->getImplementationConfiguration();
		$this->assertInstanceOf('Dkd\\CmisService\\Configuration\\Definitions\\ImplementationConfiguration', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getTableConfigurationReturnsImplementationConfiguration() {
		$configuration = $this->getInitializedConfiguration();
		$result = $configuration->getTableConfiguration();
		$this->assertInstanceOf('Dkd\\CmisService\\Configuration\\Definitions\\TableConfiguration', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getNetworkConfigurationReturnsImplementationConfiguration() {
		$configuration = $this->getInitializedConfiguration();
		$result = $configuration->getNetworkConfiguration();
		$this->assertInstanceOf('Dkd\\CmisService\\Configuration\\Definitions\\NetworkConfiguration', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getCmisConfigurationReturnsImplementationConfiguration() {
		$configuration = $this->getInitializedConfiguration();
		$result = $configuration->getCmisConfiguration();
		$this->assertInstanceOf('Dkd\\CmisService\\Configuration\\Definitions\\CmisConfiguration', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getStanbolConfigurationReturnsImplementationConfiguration() {
		$configuration = $this->getInitializedConfiguration();
		$result = $configuration->getStanbolConfiguration();
		$this->assertInstanceOf('Dkd\\CmisService\\Configuration\\Definitions\\StanbolConfiguration', $result);
	}

}
