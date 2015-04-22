<?php
namespace Dkd\CmisService\Tests\Unit\Execution\Cmis;

use Dkd\CmisService\Resolving\UUIDResolver;
use Dkd\PhpCmis\Exception\CmisObjectNotFoundException;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class AbstractCmisExecutionTest
 */
class AbstractCmisExecutionTest extends UnitTestCase {

	/**
	 * @return void
	 */
	public function testResolveCmisDocumentByTableAndUid() {
		$document = $this->getMock('Dkd\\PhpCmis\\DataObjects\\Document', array(), array(), '', FALSE);
		$session = $this->getMock('Dkd\\PhpCmis\\Session', array('dummy'), array(), '', FALSE);
		$type = $this->getMockForAbstractClass('Dkd\\PhpCmis\\Data\\ObjectTypeInterface');
		$folder = $this->getMock('Dkd\\PhpCmis\\DataObjects\\Folder', array(), array(), '', FALSE);
		$instance = $this->getAbstractCmisExecutionMock(array('createCmisDocument', 'resolveCmisObjectByUuid'), $session);
		$instance->expects($this->once())->method('resolveCmisObjectByUuid')->willReturn($document);
		$instance->expects($this->never())->method('createCmisDocument');
		$instance->expects($this->never())->method('getIdentityMap');
		$result = $this->callInaccessibleMethod($instance, 'resolveCmisDocumentByTableAndUid', 'table', 1);
		$this->assertEquals($document, $result);
	}

	/**
	 * @return void
	 */
	public function testResolveCmisDocumentByTableAndUidCatchesDocumentNotFoundExceptionsAndCreatesDocument() {
		$exception = new CmisObjectNotFoundException();
		$document = $this->getMock('Dkd\\PhpCmis\\DataObjects\\Document', array(), array(), '', FALSE);
		$uuid = 'abc';
		$type = $this->getMockForAbstractClass('Dkd\\PhpCmis\\Data\\ObjectTypeInterface');
		$folder = $this->getMock('Dkd\\PhpCmis\\DataObjects\\Folder', array(), array(), '', FALSE);
		$session = $this->getMock(
			'Dkd\\PhpCmis\\Session',
			array('getTypeDefinition', 'getRootFolder'),
			array(), '', FALSE
		);
		$session->expects($this->once())->method('getRootFolder')->willReturn($folder);
		$instance = $this->getAbstractCmisExecutionMock(
			array(
				'createCmisDocument',
				'loadRecordFromDatabase',
				'resolveCmisObjectTypeForTableAndUid',
				'resolveCmisObjectByUuid'
			),
			$session
		);
		$instance->expects($this->once())->method('resolveCmisObjectByUuid')->willThrowException($exception);
		$instance->expects($this->once())->method('createCmisDocument')->willReturn($document);
		$instance->expects($this->once())->method('resolveCmisObjectTypeForTableAndUid')->willReturn($type);
		$result = $this->callInaccessibleMethod($instance, 'resolveCmisDocumentByTableAndUid', 'table', 1);
		$this->assertEquals($document, $result);
	}

	/**
	 * @return void
	 */
	public function testResolveCmisDocumentByTableAndUidDoesNotCatchOtherExceptions() {
		$exception = new \RuntimeException();
		$type = $this->getMockForAbstractClass('Dkd\\PhpCmis\\Data\\ObjectTypeInterface');
		$session = $this->getMock('Dkd\\PhpCmis\\Session', array('dummy'), array(), '', FALSE);
		$instance = $this->getAbstractCmisExecutionMock(
			array('createCmisDocument', 'loadRecordFromDatabase', 'resolveCmisObjectByUuid'),
			$session
		);
		$instance->expects($this->once())->method('resolveCmisObjectByUuid')->willThrowException($exception);
		$instance->expects($this->never())->method('createCmisDocument');
		$this->setExpectedException('RuntimeException');
		$result = $this->callInaccessibleMethod($instance, 'resolveCmisDocumentByTableAndUid', 'table', 1);
	}

	/**
	 * @param array $methods
	 * @param mixed $session
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getAbstractCmisExecutionMock(array $methods, $session) {
		$methods[] = 'getCmisObjectFactory';
		$methods[] = 'getObjectFactory';
		$cmisObjectFactory = $this->getMock('Dkd\\CmisService\\Factory\\CmisObjectFactory', array('getSession'));
		$cmisObjectFactory->expects($this->any())->method('getSession')->willReturn($session);
		$identityMap = $this->getMock('Maroschik\\Identity\\IdentityMap', array('getIdentifierForResourceLocation'));
		$identityMap->expects($this->once())->method('getIdentifierForResourceLocation')->willReturn('foobar-identifier');
		$objectFactory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory', array('getIdentityMap'));
		$objectFactory->expects($this->once())->method('getIdentityMap')->willReturn($identityMap);
		$instance = $this->getMockForAbstractClass(
			'Dkd\\CmisService\\Execution\\Cmis\\AbstractCmisExecution',
			array(), '', FALSE, FALSE, TRUE,
			$methods
		);
		$instance->expects($this->any())->method('getCmisObjectFactory')->willReturn($cmisObjectFactory);
		$instance->expects($this->any())->method('getObjectFactory')->willReturn($objectFactory);
		return $instance;
	}

}
