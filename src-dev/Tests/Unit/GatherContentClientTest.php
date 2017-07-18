<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Account;
use Cheppers\GatherContent\DataTypes\Item;
use Cheppers\GatherContent\DataTypes\Project;
use Cheppers\GatherContent\DataTypes\Status;
use Cheppers\GatherContent\DataTypes\Template;
use Cheppers\GatherContent\DataTypes\User;
use Cheppers\GatherContent\GatherContentClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * @group GatherContentClient
 *
 * @covers \Cheppers\GatherContent\GatherContentClient
 */
class GatherContentClientTest extends GcBaseTestCase
{
    /**
     * @var array
     */
    protected $gcClientOptions = [
        'baseUri' => 'https://api.example.com',
        'email' => 'a@b.com',
        'apiKey' => 'a-b-c-d',
    ];

    public function testGetSetEmail(): void
    {
        $client = new Client();
        $gc = new GatherContentClient($client);

        static::assertEquals('', $gc->getEmail());
        $gc->setEmail('a@b.c');
        static::assertEquals('a@b.c', $gc->getEmail());
    }

    public function testGetSetApiKey(): void
    {
        $client = new Client();
        $gc = new GatherContentClient($client);

        static::assertEquals('', $gc->getApiKey());
        $gc->setApiKey('a-b-c-d');
        static::assertEquals('a-b-c-d', $gc->getApiKey());
    }

    public function testGetSetBaseUri(): void
    {
        $client = new Client();
        $gc = new GatherContentClient($client);

        static::assertEquals('https://api.gathercontent.com', $gc->getBaseUri());
        $gc->setBaseUri('https://example.com');
        static::assertEquals('https://example.com', $gc->getBaseUri());
    }

    public function testSetOptions(): void
    {
        $client = new Client();
        $gc = new GatherContentClient($client);
        $gc->setOptions([
            'email' => 'a@b.c',
            'apiKey' => 'a-b-c-d',
            'baseUri' => 'https://example.com',
        ]);
        static::assertEquals('a@b.c', $gc->getEmail());
        static::assertEquals('a-b-c-d', $gc->getApiKey());
        static::assertEquals('https://example.com', $gc->getBaseUri());
    }

    public function testProjectTypes(): void
    {
        $client = new Client();
        $gc = new GatherContentClient($client);
        static::assertEquals(
            [
                'website-build',
                'ongoing-website-content',
                'marketing-editorial-content',
                'email-marketing-content',
                'other',
            ],
            $gc->projectTypes()
        );
    }

    public function casesMeGet(): array
    {
        $userData = static::getUniqueResponseUser();
        $userExpected = $userData;
        $userExpected['announcements'] = [];
        foreach ($userData['announcements'] as $announcement) {
            $userExpected['announcements'][$announcement['id']] = $announcement;
        }

        return [
          'basic' => [
            $userExpected,
            ['data' => $userData],
          ],
        ];
    }

    /**
     * @dataProvider casesMeGet
     */
    public function testMeGet(array $expected, array $responseBody): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
          'handler' => $handlerStack,
        ]);

        $user = (new GatherContentClient($client))
          ->setOptions($this->gcClientOptions)
          ->meGet();

        static::assertTrue($user instanceof User, 'Return data type is User');
        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($user, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/me",
            (string) $request->getUri()
        );
    }

    public function casesMeGetFail(): array
    {
        return static::basicStatusCodeCases();
    }

    /**
     * @dataProvider casesMeGetFail
     */
    public function testMeGetFail(array $expected, array $response): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
          'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
          ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->meGet();
    }

    public function casesAccountsGet(): array
    {
        $data = [
            static::getUniqueResponseAccount(),
            static::getUniqueResponseAccount(),
            static::getUniqueResponseAccount(),
        ];

        $expected = [];
        foreach ($data as $account) {
            $expected[$account['id']] = $account;
        }

        return [
            'basic' => [
                $expected,
                ['data' => $data],
            ],
        ];
    }

    /**
     * @dataProvider casesAccountsGet
     */
    public function testAccountsGet(array $expected, array $responseBody): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'accounts'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $accounts = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->accountsGet();

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($accounts, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/accounts",
            (string) $request->getUri()
        );
    }

    public function casesAccountsGetFail(): array
    {
        return static::basicStatusCodeCases();
    }

    /**
     * @dataProvider casesAccountsGetFail
     */
    public function testAccountsGetFail(array $expected, array $response): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->accountsGet();
    }

    public function casesAccountGet(): array
    {
        $data = static::getUniqueResponseAccount();

        return [
            'basic' => [
                $data,
                ['data' => $data],
            ],
        ];
    }

    /**
     * @dataProvider casesAccountGet
     */
    public function testAccountGet(array $expected, array $responseBody): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'account'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->accountGet($responseBody['data']['id']);

        static::assertTrue($actual instanceof Account, 'Data type of the return is Account');
        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/accounts/" . (int) $responseBody['data']['id'],
            (string) $request->getUri()
        );
    }

    public function casesAccountGetFail(): array
    {
        $data = static::getUniqueResponseAccount();
        $cases = static::basicStatusCodeCases($data);

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => '@todo Account not found',
            ],
            [
                'code' => 200,
                'body' => [
                    'data' => [
                        'message' => 'Account not found'
                    ]
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesAccountGetFail
     */
    public function testAccountGetFail(array $expected, array $response, int $account_id): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->accountGet($account_id);
    }

    public function casesProjectsGet(): array
    {
        $data = [
            static::getUniqueResponseProject(),
            static::getUniqueResponseProject(),
            static::getUniqueResponseProject(),
        ];

        $expected = [];
        foreach ($data as $project) {
            $expected[$project['id']] = $project;
            $expected[$project['id']]['meta'] = [];
            $expected[$project['id']]['statuses'] = [];
            foreach ($project['statuses']['data'] as $status) {
                $expected[$project['id']]['statuses']['data'][$status['id']] = $status;
            }
        }

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $expected,
                ['data' => $data],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesProjectsGet
     */
    public function testProjectsGet(array $expected, array $responseBody, int $accountId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'accounts'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $projects = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->projectsGet($accountId);

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($projects, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects?account_id=$accountId",
            (string) $request->getUri()
        );
    }

    public function casesProjectsGetFail(): array
    {
        $cases = static::basicStatusCodeCases();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => '@todo Account not found',
            ],
            [
                'code' => 200,
                'body' => [
                    'data' => [
                        'message' => 'Account not found'
                    ]
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesProjectsGetFail
     */
    public function testProjectsGetFail(array $expected, array $response, int $accountId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->projectsGet($accountId);
    }

    public function casesProjectGet(): array
    {
        $data = static::getUniqueResponseProject();

        $expected = $data;
        $expected['meta'] = [];
        $expected['statuses'] = [];
        foreach ($data['statuses']['data'] as $status) {
            $expected['statuses']['data'][$status['id']] = $status;
        }

        return [
            'basic' => [
                $expected,
                ['data' => $data],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesProjectGet
     */
    public function testProjectGet(array $expected, array $responseBody, int $projectId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'project'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->projectGet($projectId);

        static::assertTrue($actual instanceof Project, 'Data type of the return is Project');
        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects/$projectId",
            (string) $request->getUri()
        );
    }

    public function casesProjectGetFail(): array
    {
        $cases = static::basicStatusCodeCases();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => '@todo Project Not Found',
            ],
            [
                'code' => 200,
                'body' => [
                    'data' => [
                        'message' => 'Project Not Found'
                    ]
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesProjectGetFail
     */
    public function testProjectGetFail(array $expected, array $response, int $projectId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->projectGet($projectId);
    }

    public function casesProjectStatusesGet(): array
    {
        $data = [
            static::getUniqueResponseStatus(),
            static::getUniqueResponseStatus(),
            static::getUniqueResponseStatus(),
        ];

        $expected = static::reKeyArray($data, 'id');

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $expected,
                ['data' => $data],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesProjectStatusesGet
     */
    public function testProjectStatusesGet(array $expected, array $responseBody, int $projectId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'accounts'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $statuses = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->projectStatusesGet($projectId);

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($statuses, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/statuses",
            (string) $request->getUri()
        );
    }

    public function casesProjectStatusesGetFail(): array
    {
        $cases = static::basicStatusCodeCases();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 404,
                'msg' => '{"error":"Project Not Found","code":404}',
            ],
            [
                'code' => 404,
                'body' => [
                    'error' => 'Project Not Found',
                    'code' => 404
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesProjectStatusesGetFail
     */
    public function testProjectStatusesGetFail(array $expected, array $response, int $projectId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->projectStatusesGet($projectId);
    }

    public function casesProjectStatusGet(): array
    {
        $data = static::getUniqueResponseStatus();

        return [
            'basic' => [
                $data,
                ['data' => $data],
                42,
                $data['id']
            ],
        ];
    }

    /**
     * @dataProvider casesProjectStatusGet
     */
    public function testProjectStatusGet(array $expected, array $responseBody, int $projectId, int $statusId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'project'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->projectStatusGet($projectId, $statusId);

        static::assertTrue($actual instanceof Status, 'Data type of the return is Status');
        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/statuses/$statusId",
            (string) $request->getUri()
        );
    }

    public function casesProjectStatusGetFail(): array
    {
        $data = static::getUniqueResponseStatus();
        return static::basicStatusCodeCases($data);
    }

    /**
     * @dataProvider casesProjectStatusGetFail
     */
    public function testProjectStatusGetFail(
        array $expected,
        array $response,
        int $projectId,
        int $statusId
    ): void {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->projectStatusGet($projectId, $statusId);
    }

    public function casesItemsGet(): array
    {
        $data = [
            static::getUniqueResponseItem([
                ['text', 'files', 'choice_radio', 'choice_checkbox'],
            ]),
            static::getUniqueResponseItem([
                ['text', 'choice_radio', 'choice_checkbox'],
                ['text', 'choice_radio'],
            ]),
            static::getUniqueResponseItem([
                ['choice_radio', 'choice_checkbox'],
            ]),
        ];

        $items = static::reKeyArray($data, 'id');
        foreach (array_keys($items) as $itemId) {
            $items[$itemId]['config'] = static::reKeyArray($items[$itemId]['config'], 'name');
            foreach (array_keys($items[$itemId]['config']) as $tabId) {
                $items[$itemId]['config'][$tabId]['elements'] = static::reKeyArray(
                    $items[$itemId]['config'][$tabId]['elements'],
                    'name'
                );
            }
        }

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $items,
                ['data' => $data],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesItemsGet
     */
    public function testItemsGet(array $expected, array $responseBody, int $projectId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'accounts'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->itemsGet($projectId);

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items?project_id=$projectId",
            (string) $request->getUri()
        );
    }

    public function casesItemsGetFail(): array
    {
        $cases = static::basicStatusCodeCases();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => '@todo Project Not Found',
            ],
            [
                'code' => 200,
                'body' => [
                    'data' => [
                        'message' => 'Project Not Found'
                    ]
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesItemsGetFail
     */
    public function testItemsGetFail(array $expected, array $response, int $projectId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->itemsGet($projectId);
    }

    public function casesItemGet(): array
    {
        $item = static::getUniqueResponseItem([
            ['text', 'choice_checkbox'],
        ]);

        $item['config'] = static::reKeyArray($item['config'], 'name');
        foreach (array_keys($item['config']) as $tabId) {
            $item['config'][$tabId]['elements'] = static::reKeyArray(
                $item['config'][$tabId]['elements'],
                'name'
            );
        }

        return [
            'empty' => [
                null,
                ['data' => []],
                42,
            ],
            'basic' => [
                $item,
                ['data' => $item],
                $item['id']
            ],
        ];
    }

    /**
     * @dataProvider casesItemGet
     */
    public function testItemGet(?array $expected, array $responseBody, int $itemId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'project'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->itemGet($itemId);

        if ($expected) {
            static::assertTrue($actual instanceof Item, 'Data type of the return is Item');
            static::assertEquals(
                json_encode($expected, JSON_PRETTY_PRINT),
                json_encode($actual, JSON_PRETTY_PRINT)
            );
        } else {
            static::assertNull($actual);
        }

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/$itemId",
            (string) $request->getUri()
        );
    }

    public function casesItemGetFail(): array
    {
        $cases = static::basicStatusCodeCases();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => '@todo Item Not Found',
            ],
            [
                'code' => 200,
                'body' => [
                    'data' => [
                        'message' => 'Item Not Found'
                    ]
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesItemGetFail
     */
    public function testItemGetFail(array $expected, array $response, int $itemId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->itemGet($itemId);
    }

    public function casesItemFilesGet(): array
    {
        $data = [
            static::getUniqueResponseFile(),
            static::getUniqueResponseFile(),
            static::getUniqueResponseFile(),
        ];

        $expected = static::reKeyArray($data, 'id');

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $expected,
                ['data' => $data],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesItemFilesGet
     */
    public function testItemFilesGet(array $expected, array $responseBody, int $itemId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'accounts'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->itemFilesGet($itemId);

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/$itemId/files",
            (string) $request->getUri()
        );
    }

    /**
     * @dataProvider casesItemGetFail
     */
    public function testItemFilesGetFail(array $expected, array $response, int $itemId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->itemFilesGet($itemId);
    }

    public function casesTemplatesGet(): array
    {
        $data = [
            static::getUniqueResponseTemplate([
                ['text', 'files', 'choice_radio', 'choice_checkbox'],
            ]),
            static::getUniqueResponseTemplate([
                ['text', 'choice_radio', 'choice_checkbox'],
                ['text', 'choice_radio'],
            ]),
            static::getUniqueResponseTemplate([
                ['choice_radio', 'choice_checkbox'],
            ]),
        ];

        $templates = static::reKeyArray($data, 'id');
        foreach (array_keys($templates) as $templateId) {
            $templates[$templateId]['config'] = static::reKeyArray($templates[$templateId]['config'], 'name');
            foreach (array_keys($templates[$templateId]['config']) as $tabId) {
                $templates[$templateId]['config'][$tabId]['elements'] = static::reKeyArray(
                    $templates[$templateId]['config'][$tabId]['elements'],
                    'name'
                );
            }
        }

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $templates,
                ['data' => $data],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesTemplatesGet
     */
    public function testTemplatesGet(array $expected, array $responseBody, int $projectId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'accounts'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->templatesGet($projectId);

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/templates?project_id=$projectId",
            (string) $request->getUri()
        );
    }

    public function casesTemplatesGetFail(): array
    {
        $cases = static::basicStatusCodeCases();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => '@todo Project Not Found',
            ],
            [
                'code' => 200,
                'body' => [
                    'data' => [
                        'message' => 'Project Not Found'
                    ]
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesTemplatesGetFail
     */
    public function testTemplatesGetFail(array $expected, array $response, int $projectId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->templatesGet($projectId);
    }

    public function casesTemplateGet(): array
    {
        $data = static::getUniqueResponseTemplate([
            ['text', 'files', 'section', 'choice_radio', 'choice_checkbox'],
        ]);

        $template = $data;
        $template['config'] = static::reKeyArray($template['config'], 'name');
        foreach (array_keys($template['config']) as $tabId) {
            $template['config'][$tabId]['elements'] = static::reKeyArray(
                $template['config'][$tabId]['elements'],
                'name'
            );
        }

        return [
            'empty' => [
                null,
                ['data' => []],
                42,
            ],
            'basic' => [
                $template,
                ['data' => $data],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesTemplateGet
     */
    public function testTemplateGet(?array $expected, array $responseBody, int $templateId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'accounts'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->templateGet($templateId);

        if ($expected) {
            static::assertTrue($actual instanceof Template, 'Data type of the return is Template');
            static::assertEquals(
                json_encode($expected, JSON_PRETTY_PRINT),
                json_encode($actual, JSON_PRETTY_PRINT)
            );
        } else {
            static::assertNull($actual);
        }

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/templates/$templateId",
            (string) $request->getUri()
        );
    }

    public function casesTemplateGetFail(): array
    {
        $cases = static::basicStatusCodeCases();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => '@todo Template Not Found',
            ],
            [
                'code' => 200,
                'body' => [
                    'data' => [
                        'message' => 'Template Not Found'
                    ]
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesTemplateGetFail
     */
    public function testTemplateGetFail(array $expected, array $response, int $templateId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->templateGet($templateId);
    }
}
