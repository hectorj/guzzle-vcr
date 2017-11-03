<?php
declare(strict_types=1);

namespace Examples\Standard;

use GuzzleVCR\GuzzleVCRTestCase;

/**
 * We extend GuzzleVCR\GuzzleVCRTestCase instead of PHPUnit\Framework\TestCase
 */
class StandardExample extends GuzzleVCRTestCase
{
	/** @test */
	public function testing_a_simple_github_client()
	{
		/*
		 * Arrange
		 * =======
		 *
		 * Here we inject a Guzzle client (created via GuzzleVcr) into our Github client.
		 *
		 * For this we need to choose a file path where to store the records.
		 * I recommend using the class and test name, but you can choose any *readable and writable* path.
		 */
		$recordFilepath = __DIR__.DIRECTORY_SEPARATOR.(new \ReflectionClass(static::class))->getShortName().DIRECTORY_SEPARATOR.$this->getName().'.record.php';
		$guzzleClient = $this->newGuzzleVCRClient($recordFilepath);

		$githubClient = new GithubClient($guzzleClient);

		/*
		 * Act
		 * ===
		 *
		 * We use our Github client normally.
		 *
		 * If we have no records yet, the request will be sent, and the response will be recorded.
		 *
		 * If we already have a record on disk, then we won't actually make an http request to Github servers:
		 * instead, the customized Guzzle client will return the response we previously recorded.
		 */
		$repositories = $githubClient->getMostStarredRepositories('test');

		/*
		 * Assert
		 * ======
		 *
		 * This part does exactly the same, no matter if we are recording or replaying.
		 * It is just your standard test assertions.
		 */
		self::assertCount(30, $repositories);
		foreach ($repositories as $repository) {
			self::assertObjectHasAttribute('id', $repository);
			self::assertObjectHasAttribute('name', $repository);
		}
	}
}
