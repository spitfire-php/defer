<?php namespace spitfire\defer\tests;

use AndrewBreksa\RSMQ\Exceptions\QueueAlreadyExistsException;
use AndrewBreksa\RSMQ\RSMQClient;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use spitfire\cli\Console;
use spitfire\defer\Result;
use spitfire\defer\Task;
use spitfire\defer\TaskFactory;
use spitfire\defer\WorkerFactory;
use spitfire\provider\Container;

class WorkerTest extends TestCase
{
	
	private $container;
	private $client;
	
	public function setUp() : void
	{
		
		$this->container = new Container();
		$this->container->set('TestClass', new class() implements Task 
		{
			public function body($settings): Result {
				echo 'Task is being executed';
				return new Result('Success');
			}
		});
		
		$this->client = new RSMQClient(new Client([
			'host' => getenv('REDIS_HOST'),
			'post' => 6379
		]));
		
		/**
		 * We attempt to create the queue, assuming that our testing queue does never exist.
		 * If we cannot create it, the queue already exists and could be polluted with data
		 * that distorts our expected outcome.
		 */
		$this->client->createQueue('test');
	}
	
	public function testWorker() 
	{
		
		$factory = new TaskFactory($this->client, 'test');
		
		for ($i = 0; $i < 10; $i++) {
			$factory->defer(3, 'TestClass', []);
		}
		
		$worker = new WorkerFactory(new Console(), $this->container, $this->client, 'test');
		$worker->make()->work(true);
		
		/**
		 * We got here...
		 */
		$this->assertEquals(true, true);
	}
	
	public function tearDown() : void
	{
		
		/**
		 * Spring cleaning please
		 */
		$this->client->deleteQueue('test');
	}
}
