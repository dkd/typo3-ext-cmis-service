<?php
namespace Dkd\CmisService\ViewHelpers;

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class VariableViewHelper
 */
class VariableViewHelper extends AbstractViewHelper {

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function render($name) {
		return ObjectAccess::getPropertyPath($this->templateVariableContainer->getAll(), $name);
	}
}
