<?php
namespace Dkd\CmisService\ViewHelpers\Object;

use Dkd\CmisService\Factory\CmisObjectFactory;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetViewHelper
 */
class GetViewHelper extends AbstractViewHelper {
	/**
	 * @param string $cmisObjectId
	 * @return string
	 */
	public function render($cmisObjectId = NULL) {
		if ($cmisObjectId === NULL) {
			$cmisObjectId = $this->renderChildren();
		}
		$sessionFactory = new CmisObjectFactory();
		$session = $sessionFactory->getSession();

		$cmisObject = $session->getObject(
			$session->createObjectId($cmisObjectId)
		);

		return $cmisObject;
	}
}
