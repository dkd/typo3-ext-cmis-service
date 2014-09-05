<?php
namespace Dkd\CmisService;

/**
 * Interface SingletonInterface
 *
 * This interface is, in this default implementation, simply
 * a subclass of TYPO3 CMS' SingletonInterface.
 *
 * The interface is subclassed and used by all classes inside
 * this package scope in order to make it more portable by
 * making a single point of coupling to TYPO3 CMS for all
 * Singleton classes.
 */
interface SingletonInterface extends \TYPO3\CMS\Core\SingletonInterface {

}
