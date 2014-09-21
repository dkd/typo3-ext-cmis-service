<?php
namespace Dkd\CmisService\Execution;

/**
 * Class Result
 */
class Result {

	const OK = 0;
	const ERR = 1;

	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @var integer
	 */
	protected $code;

	/**
	 * @var array
	 */
	protected $payload = array();

	/**
	 * Create a Result instance
	 *
	 * @param string $message
	 * @param integer $code
	 * @param array $payload
	 */
	public function __construct($message = NULL, $code = self::OK, $payload = array()) {
		$this->setMessage($message);
		$this->setCode($code);
		$this->setPayload($payload);
	}

	/**
	 * Sets the execution result code (0=success, 1+ errors occurred)
	 *
	 * @param integer $code
	 * @return void
	 */
	public function setCode($code) {
		$this->code = $code;
	}

	/**
	 * Get the execution result code
	 *
	 * @return integer
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Set the message associated with this Result
	 *
	 * @param string $message
	 * @return void
	 */
	public function setMessage($message) {
		$this->message = $message;
	}

	/**
	 * Get the message stored in this Result
	 *
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Set the payload (array data) associated with this Result
	 *
	 * @param array $payload
	 * @return void
	 */
	public function setPayload(array $payload) {
		$this->payload = $payload;
	}

	/**
	 * Get the Result payload data array
	 *
	 * @return array
	 */
	public function getPayload() {
		return $this->payload;
	}

}
