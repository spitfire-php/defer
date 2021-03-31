<?php namespace spitfire\defer;

use Exception;

class FailureException extends Exception
{
	
	/**
	 * @var string
	 */
	private $extended;
	
	/**
	 * 
	 * @param string $message
	 * @param int $code
	 * @param string $extended
	 */
	public function __construct(string $message = "", int $code = 0, string $extended = "") {
		$this->extended = $extended;
		parent::__construct($message, $code, null);
	}

	/**
	 * 
	 * @return string
	 */
	public function getExtended() : string
	{
		return $this->extended;
	}

	
}
