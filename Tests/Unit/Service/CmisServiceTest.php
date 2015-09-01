<?php
namespace Dkd\CmisService\Tests\Unit\Service;

use Dkd\CmisService\Configuration\Definitions\CmisConfiguration;
use Dkd\PhpCmis\Exception\CmisObjectNotFoundException;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class CmisServiceTest
 */
class CmisServiceTest extends UnitTestCase {

	/**
	 * @return void
	 */
	public function testResolveObjectByTableAndUid() {
		$document = $this->getMock('Dkd\\PhpCmis\\DataObjects\\Document', array(), array(), '', FALSE);
		$session = $this->getMock('Dkd\\PhpCmis\\Session', array('dummy'), array(), '', FALSE);
		$type = $this->getMockForAbstractClass('Dkd\\PhpCmis\\Data\\ObjectTypeInterface');
		$folder = $this->getMock('Dkd\\PhpCmis\\DataObjects\\Folder', array(), array(), '', FALSE);
		$instance = $this->getCmisServiceMock(
			array('createCmisDocument', 'resolveObjectByUuid', 'getUuidForLocalRecord'),
			$session
		);
		$instance->expects($this->once())->method('resolveObjectByUuid')->willReturn($document);
		$instance->expects($this->never())->method('createCmisDocument');
		$result = $instance->resolveObjectByTableAndUid('table', 1);
		$this->assertEquals($document, $result);
	}

	/**
	 * @return void
	 */
	public function testResolveCmisDocumentByTableAndUidCatchesDocumentNotFoundExceptionsAndCreatesDocument() {
		$exception = new CmisObjectNotFoundException();
		$document = $this->getMock('Dkd\\PhpCmis\\DataObjects\\Document', array(), array(), '', FALSE);
		$uuid = 'abc';
		$folder = $this->getMock('Dkd\\PhpCmis\\DataObjects\\Folder', array(), array(), '', FALSE);
		$session = $this->getMock('Dkd\\PhpCmis\\Session', array('getRootFolder'), array(), '', FALSE);
		$session->expects($this->once())->method('getRootFolder')->willReturn($folder);
		$instance = $this->getCmisServiceMock(
			array(
				'createCmisObject',
				'loadRecordFromDatabase',
				'resolveObjectByUuid',
				'getUuidForLocalRecord',
				'resolvePropertiesForTableAndUid'
			),
			$session
		);
		$instance->expects($this->once())->method('getUuidForLocalRecord')->willReturn($uuid);
		$instance->expects($this->once())->method('loadRecordFromDatabase')->willReturn(array());
		$instance->expects($this->once())->method('resolveObjectByUuid')->willThrowException($exception);
		$instance->expects($this->once())->method('createCmisObject')->willReturn($document);
		$result = $instance->resolveObjectByTableAndUid('table', 1);
		$this->assertEquals($document, $result);
	}

	/**
	 * @return void
	 */
	public function testResolveObjectByTableAndUidDoesNotCatchOtherExceptions() {
		$exception = new \RuntimeException();
		$type = $this->getMockForAbstractClass('Dkd\\PhpCmis\\Data\\ObjectTypeInterface');
		$session = $this->getMock('Dkd\\PhpCmis\\Session', array('dummy'), array(), '', FALSE);
		$instance = $this->getCmisServiceMock(
			array('createCmisDocument', 'loadRecordFromDatabase', 'resolveObjectByUuid', 'getUuidForLocalRecord'),
			$session
		);
		$instance->expects($this->once())->method('resolveObjectByUuid')->willThrowException($exception);
		$instance->expects($this->never())->method('createCmisDocument');
		$this->setExpectedException('RuntimeException');
		$instance->resolveObjectByTableAndUid('table', 1);
	}

	/**
	 * @param array $methods
	 * @param mixed $session
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getCmisServiceMock(array $methods, $session) {
		$methods[] = 'getCmisObjectFactory';
		$methods[] = 'getObjectFactory';
		$cmisObjectFactory = $this->getMock('Dkd\\CmisService\\Factory\\CmisObjectFactory', array('getSession'));
		$cmisObjectFactory->expects($this->any())->method('getSession')->willReturn($session);
		$cmisConfiguration = new CmisConfiguration();
		$configuration = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Definitions\\MasterConfiguration',
			array('getCmisConfiguration')
		);
		$configuration->expects($this->any())->method('getCmisConfiguration')->willReturn($cmisConfiguration);
		$objectFactory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory', array('getConfiguration'));
		$objectFactory->expects($this->any())->method('getConfiguration')->willReturn($configuration);
		$instance = $this->getMockForAbstractClass(
			'Dkd\\CmisService\\Service\\CmisService',
			array(), '', FALSE, FALSE, TRUE,
			$methods
		);
		$instance->expects($this->any())->method('getCmisObjectFactory')->willReturn($cmisObjectFactory);
		$instance->expects($this->any())->method('getObjectFactory')->willReturn($objectFactory);
		return $instance;
	}

}
