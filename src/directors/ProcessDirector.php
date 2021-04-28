<?php namespace spitfire\defer\directors;

use spitfire\cli\Console;
use spitfire\defer\Task;
use spitfire\mvc\Director;
use spitfire\provider\Container;
use Throwable;

class ProcessDirector extends Director
{
	
	private $db;
	private $console;
	private $services;
	
	public function __construct(\spitfire\storage\database\DB $db, Console $console, Container $services)
	{
		$this->db = $db;
		$this->console = $console;
		$this->services = $services;
	}
	
	/**
	 * The process function does not yet receive any parameters.
	 */
	public function parameters() {
		return [];
	}
	
	public function body() {
		$pending = $this->db->table('spitfire\core\async\Async')->get('status', 'pending')
			->where('scheduled', '<', time())
			->all();
		
		foreach ($pending as $record) {
			/*@var $task \spitfire\defer\Task*/
			$task = $this->services->get($record->task);
			
			/**
			 * If a task is not a task that we can execute, we need to not execute it since it may
			 * cause behavior that we did not anticipate.
			 */
			if (!($task instanceof Task)) { 
				$record->status = 'aborted';
				$record->store();
				$this->console->error('Task is not a valid Task. Cannot process this task')->ln();
				continue;
			}
			
			$task->setSettings(json_decode($task->setttings));
			
			if ($record->ttl < 1) {
				$record->status = 'aborted';
				$record->store();
				$this->console->error('Task was abandoned for too many failures')->ln();
				continue;
			}
			
			$record->status = 'processing';
			$record->started = time();
			$record->store();
			
			try {
				$result = $task->body();
				$record->result = $result->getPayload();
				$record->status = 'success';
				$record->store();
				$this->console->success('Task processed successfully')->ln();
			} 
			catch (Throwable$e) {
				$record->result = $e->getCode() . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
				$record->status = 'error';
				$record->store();
				
				$copy = $this->db->table('spitfire\core\async\async')->newRecord();
				$copy->status = 'pending';
				$copy->ttl = $record->ttl - 1;
				$copy->scheduled = $record->scheduled + 300;
				$copy->task = $record->task;
				$copy->supersedes = $record;
				$copy->store();
				$this->console->error('Task failed - Unknown reason')->ln();
			}
		}
	}
	
}
