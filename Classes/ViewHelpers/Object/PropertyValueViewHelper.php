<?php
namespace Dkd\CmisService\ViewHelpers\Object;

use Dkd\PhpCmis\CmisObject\CmisObjectInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class PropertyValuesViewHelper
 */
class PropertyValueViewHelper extends AbstractViewHelper {
	/**
	 * @param CmisObjectInterface $cmisObject
	 * @param string $property
	 * @return string
	 */
	public function render($property, CmisObjectInterface $cmisObject = NULL) {
		if ($cmisObject === NULL) {
			$cmisObject = $this->renderChildren();
		}
		$property = $cmisObject->getProperty($property);
		$propertyValue = NULL;
		if ($property !== NULL) {
			$propertyValue = $property->getFirstValue();
		}
		return $propertyValue;
	}
}
