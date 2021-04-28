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
	 */
	abstract function body() : Result;
	
	public function setSettings($settings) {
		$this->settings = $settings;
	}
	
	public function getSettings() {
		return $this->settings;
	}
}
