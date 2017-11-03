<?php
declare(strict_types=1);

namespace Examples\Standard;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

/**
 * Simple Github client, here just for the example
 */
class GithubClient
{
	/**
	 * @var Client
	 */
	private $client;

	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	public function getMostStarredRepositories(string $query): array
	{
		$response = $this->client->get('https://api.github.com/search/repositories', [
			RequestOptions::QUERY => [
				'q' => $query,
				'sort' => 'stars',
			],
		]);

		$body = \GuzzleHttp\json_decode($response->getBody());

		return $body->items;
	}
}
