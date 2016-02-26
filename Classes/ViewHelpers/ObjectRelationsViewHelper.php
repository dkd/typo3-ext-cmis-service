<?php
namespace Dkd\CmisService\ViewHelpers;

use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\PhpCmis\Enum\RelationshipDirection;
use Dkd\PhpCmis\SessionInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class ObjectTagsViewHelper
 */
class ObjectRelationsViewHelper extends AbstractViewHelper {

	/**
	 * @param string $cmisObjectId
	 * @return array
	 */
	public function render($cmisObjectId) {
		$session = $this->getCmisSession();
		$object = $session->getObject($session->createObjectId($cmisObjectId));
		return $session->getRelationships(
			$session->createObjectId($cmisObjectId),
			TRUE,
			RelationshipDirection::cast(RelationshipDirection::EITHER),
			$session->getTypeDefinition('cmis:relationship')
		);
	}

	/**
	 * @return SessionInterface
	 */
	protected function getCmisSession() {
		return ObjectFactory::getInstance()->getCmisService()->getCmisSession();
	}

}
