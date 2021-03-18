<?php namespace spitfire\defer;

use spitfire\Model;
use spitfire\storage\database\Schema;

class AsyncModel extends Model
{
	
	/**
	 * 
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema) {
		$schema->task = new StringField(255);
		$schema->settings = new \TextField();
		$schema->result = new \TextField();
		$schema->status = new \EnumField('pending', 'processing', 'success', 'error', 'aborted');
		$schema->ttl = new \IntegerField(true);
		$schema->started = new \IntegerField(true);
		$schema->scheduled = new \IntegerField(true);
		$schema->supersedes = new \Reference(AsyncModel::class);
		
		$schema->index($schema->status, $schema->scheduled);
	}

}
