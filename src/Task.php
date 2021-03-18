<?php namespace spitfire\defer;

use Serializable;

abstract class Task implements Serializable
{
	
	private $settings;
	
	public function serialize() {
		return serialize(['settings' => $this->settings]);
	}
	
	public function unserialize($serialized) {
		$data = unserialize($serialized);
		$this->settings = $data['settings'];
	}
	
	/**
	 * 
	 * @throws FailureException
	 */
	abstract function body() : Result;
	
	/**
	 * 
	 * @param FailureException $e
	 * @return Result
	 */
	public function handleFailure(FailureException$e): Result {
		return new Result(sprintf('%s %s%s%s', $e->getCode(), $e->getMessage(), PHP_EOL, $e->getExtended()));
	}
	
	public function setSettings($settings) {
		$this->settings = $settings;
	}
	
	public function getSettings() {
		return $this->settings;
	}
}
