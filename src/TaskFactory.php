<?php namespace spitfire\defer;

class TaskFactory
{
	
	private $db;
	
	public function __construct(\spitfire\storage\database\DB $db)
	{
		$this->db = $db;
	}
	
	public function defer($defer, $task, $ttl = 10) {
		$copy = $this->db->table('spitfire\core\async\Async')->newRecord();
		$copy->status = 'pending';
		$copy->ttl = $ttl;
		$copy->scheduled = $defer < 86400 * 365 * 50? time() + $defer : $defer; #It's been the timestamp's 50th aniversary this year
		$copy->task = serialize($task);
		$copy->store();
	}
	
}
