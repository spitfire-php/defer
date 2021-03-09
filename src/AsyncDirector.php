<?php namespace spitfire\defer;

use spitfire\cli\Console;
use spitfire\mvc\Director;
use Throwable;
use function console;
use function db;

class AsyncDirector extends Director
{
	
	private $db;
	private $console;
	
	public function __construct(\spitfire\storage\database\DB $db, Console $console)
	{
		$this->db = $db;
		$this->console = $console;
	}
	
	public function pending() {
		
	}
	
	public function process() {
		$pending = $this->db->table('spitfire\core\async\Async')->get('status', 'pending')
			->where('scheduled', '<', time())
			->all();
		
		foreach ($pending as $record) {
			/*@var $task Task*/
			$task = unserialize($record->task);
			
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
			catch (FailureException$ex) {
				$result = $task->handleFailure($ex);
				$record->result = $result->getPayload();
				$record->status = 'error';
				$record->store();
				
				$copy = $this->db->table('spitfire\core\async\Async')->newRecord();
				$copy->status = 'pending';
				$copy->ttl = $record->ttl - 1;
				$copy->scheduled = $record->scheduled + 300;
				$copy->task = $record->task;
				$copy->supersedes = $record;
				$copy->store();
				$this->console->error('Task failed')->ln();
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
