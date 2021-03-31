<?php namespace spitfire\defer;

class Result
{
	
	/**
	 * This is the message that will be written to the log as the result of the
	 * task being executed. This is expected to be a human readable string
	 * 
	 * @var string
	 */
	private $payload;
	
	
	/**
	 * The result of the operation. This contains a message that will be recorded 
	 * for the maintainer to understand what the task did.
	 * 
	 * @param string $payload
	 */
	public function __construct($payload) {
		$this->payload = $payload;
	}
	
	/**
	 * Returns the message that will be written to the log as the result of the
	 * task being executed. This is expected to be a human readable string
	 * 
	 * @return string
	 */
	public function getPayload() {
		return $this->payload;
	}
	
}
