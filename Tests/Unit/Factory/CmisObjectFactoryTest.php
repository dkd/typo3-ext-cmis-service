<?php
namespace Dkd\CmisService\Tests\Unit\Factory;

use Dkd\CmisService\Factory\CmisObjectFactory;
use Dkd\PhpCmis\Enum\BindingType;
use Dkd\PhpCmis\SessionParameter;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class CmisObjectFactoryTest
 */
class CmisObjectFactoryTest extends UnitTestCase {

	/**
	 * @return void
	 */
	public function testGetSessionGetsSessionWithDefinedParameters() {
		$objectFactory = $this->getMock('Dkd\\CmisService\\Factory\\CmisObjectFactory', array('getConfiguration'));
		$masterConfiguration = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Definitions\\MasterConfiguration',
			array('getCmisConfiguration')
		);
		$cmisConfiguration = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Definitions\\MasterConfiguration',
			array('getSessionParameters')
		);
		$instance = $this->getMock(
			'Dkd\\CmisService\\Factory\\CmisObjectFactory',
			array('getObjectFactory', 'createSessionObject')
		);
		$dummy = $this->getMock('Dkd\\PhpCmis\\Session', array(), array(), '', FALSE);
		$cmisConfiguration->expects($this->once())->method('getSessionParameters')->willReturn(array('foo' => 'bar'));
		$masterConfiguration->expects($this->once())->method('getCmisConfiguration')->willReturn($cmisConfiguration);
		$objectFactory->expects($this->once())->method('getConfiguration')->willReturn($masterConfiguration);
		$instance->expects($this->once())->method('createSessionObject')->with(array('foo' => 'bar'))->willReturn($dummy);
		$instance->expects($this->once())->method('getObjectFactory')->willReturn($objectFactory);
		$result = $instance->getSession();
		$this->assertSame($dummy, $result);
	}

	/**
	 * @return void
	 */
	public function testGetObjectFactoryReturnsObjectFactoryInstance() {
		$instance = new CmisObjectFactory();
		$method = new \ReflectionMethod($instance, 'getObjectFactory');
		$method->setAccessible(TRUE);
		$result = $method->invoke($instance);
		$this->assertInstanceOf('Dkd\\CmisService\\Factory\\ObjectFactory', $result);
	}

}
