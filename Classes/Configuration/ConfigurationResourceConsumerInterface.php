<?php
namespace Dkd\CmisService\Configuration;

/**
 * Configuration Resource Consumer Interface
 *
 * Interface implemented by classes which are capable
 * of interacting with configuration resource identifiers
 * in stream or database reference formats.
 *
 * Implemented when the class contains methods to stat
 * resource identifiers, for example determining when they
 * were last updated or whether or not they exist.
 *
 * @package Dkd\CmisService\Configuration
 */
interface ConfigurationResourceConsumerInterface {

	/**
	 * Load the specified resource and return an
	 * object, Array or string representation as chosen
	 * by the implementation. The standard representation
	 * is an Array or object implementing ArrayAccess.
	 *
	 * @param string $resourceIdentifier
	 * @return mixed
	 */
	public function read($resourceIdentifier);

	/**
	 * Returns TRUE if the resource identified by the
	 * argument exists, FALSE if it does not.
	 *
	 * @param string $resourceIdentifier
	 * @return boolean
	 */
	public function exists($resourceIdentifier);

	/**
	 * Performs a checksum calculation of the resource
	 * identifier (optionally incorporating additional
	 * factors depending on the implementation).
	 *
	 * @param string $resourceIdentifier
	 * @return string
	 */
	public function checksum($resourceIdentifier);

	/**
	 * Returns a DateTime instance reflecting the last
	 * modification date of the resource identified in
	 * the argument.
	 *
	 * @param string $resourceIdentifier
	 * @return \DateTime
	 */
	public function lastModified($resourceIdentifier);

}