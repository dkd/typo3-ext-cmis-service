<?php
namespace Dkd\CmisService\Cache;

use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

/**
 * TYPO3 CMS VariableFrontend
 *
 * Intermediate class to enable the use of the TYPO3 CMS
 * caching fromtend type called "VariableFrontend"; a
 * standard DB table cache implementation which supports
 * value serialization to preserve arrays, instances etc.
 *
 * No methods required since the TYPO3 CMS VariableFrontend
 * is already fully compatible with the interface.
 *
 * @package Dkd\CmisService\Cache
 */
class CmsVariableFrontend extends VariableFrontend implements VariableFrontendInterface {

}
