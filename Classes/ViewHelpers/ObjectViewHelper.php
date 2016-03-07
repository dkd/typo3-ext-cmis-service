<?php
namespace Dkd\CmisService\ViewHelpers;

use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\PhpCmis\Enum\RelationshipDirection;
use Dkd\PhpCmis\SessionInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class ObjectViewHelper
 */
class ObjectViewHelper extends AbstractViewHelper {

	/**
	 * @param string $cmisObjectId
	 * @return array
	 */
	public function render($cmisObjectId = NULL) {
		if (NULL === $cmisObjectId) {
			$cmisObjectId = $this->renderChildren();
		}
		if (empty($cmisObjectId)) {
			return NULL;
		}
		$session = $this->getCmisSession();
		return $session->getObject($session->createObjectId($cmisObjectId));
	}

	/**
	 * @return SessionInterface
	 */
	protected function getCmisSession() {
		return ObjectFactory::getInstance()->getCmisService()->getCmisSession();
	}

}
