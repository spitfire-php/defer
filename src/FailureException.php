<?php namespace spitfire\defer;

use Exception;

class FailureException extends Exception
{
	
	private $extended;
	
	public function __construct(string $message = "", int $code = 0, string $extended = "") {
		$this->extended = $extended;
		parent::__construct($message, $code, null);
	}

	
	public function getExtended() {
		return $this->extended;
	}

	
}
