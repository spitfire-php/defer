<?php namespace spitfire\defer;

use Serializable;

/**
 * Classes that implement this interface can be executed in the background, 
 * allowing our application to attach logic to a certain task.
 */
interface Task
{
	/**
	 * 
	 * @param mixed $settings
	 * @return Result
	 */
	function body($settings) : Result;
}
