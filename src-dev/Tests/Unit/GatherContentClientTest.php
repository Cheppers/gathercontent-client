<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Account;
use Cheppers\GatherContent\DataTypes\ElementText;
use Cheppers\GatherContent\DataTypes\Item;
use Cheppers\GatherContent\DataTypes\Project;
use Cheppers\GatherContent\DataTypes\Status;
use Cheppers\GatherContent\DataTypes\Tab;
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
        return static::basicFailCasesGet();
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
                $response['headers'],
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
        return static::basicFailCasesGet();
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
                $response['headers'],
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
        $cases = static::basicFailCasesGet($data);

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => 'API Error: "Account not found"',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
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
                $response['headers'],
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
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => 'API Error: "Account not found"',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
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
                $response['headers'],
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
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => 'API Error: "Project Not Found"',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
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
                $response['headers'],
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

    public function casesProjectsPost(): array
    {
        return [
            'basic' => [
                [
                    'code' => 202,
                    'id' => 42,
                ],
                [
                    'code' => 202,
                    'body' => [],
                    'id' => 42,
                ],
                42,
                'Project name',
                'Project type'
            ],
        ];
    }

    /**
     * @dataProvider casesProjectsPost
     */
    public function testProjectsPost(
        array $expected,
        array $response,
        int $accountId,
        string $projectName,
        string $projectType
    ): void {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                [
                    'Content-Type' => 'application/json',
                    'Location' => "{$this->gcClientOptions['baseUri']}/projects/{$response['id']}"
                ],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $client = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);
        $actual = $client->projectsPost($accountId, $projectName, $projectType);

        static::assertEquals($expected['id'], $actual);


        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals($expected['code'], $client->getResponse()->getStatusCode());
        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['application/x-www-form-urlencoded'], $request->getHeader('Content-Type'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $queryString = $requestBody->getContents();
        $sentQueryVariables = [];
        parse_str($queryString, $sentQueryVariables);

        if ($accountId) {
            static::assertArrayHasKey('account_id', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['account_id'], $accountId);
        } else {
            static::assertArrayNotHasKey('account_id', $sentQueryVariables);
        }

        if ($projectName) {
            static::assertArrayHasKey('name', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['name'], $projectName);
        } else {
            static::assertArrayNotHasKey('name', $sentQueryVariables);
        }

        if ($projectType) {
            static::assertArrayHasKey('type', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['type'], $projectType);
        } else {
            static::assertArrayNotHasKey('type', $sentQueryVariables);
        }
    }

    public function casesProjectsPostFail(): array
    {
        $cases = static::basicFailCasesPost(['name' => 'Project name', 'type' => 'Project type']);
        $cases['missing_item'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => 'API Error: "Account not found"',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'data' => [
                        'message' => 'Account not found'
                    ]
                ],
            ],
            0,
            'Project name',
            'Project type'
        ];
        $cases['empty_name'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing name","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing name',
                    'code' => 400
                ],
            ],
            42,
            '',
            'Project type'
        ];
        $cases['missing_project_id'] = [
            [
                'class' => \Exception::class,
                'code' => 1,
                'msg' => 'Invalid response header the project ID is missing',
            ],
            [
                'code' => 202,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [],
            ],
            42,
            '',
            'Project type'
        ];

        return $cases;
    }

    /**
     * @dataProvider casesProjectsPostFail
     */
    public function testProjectsPostFail(
        array $expected,
        array $response,
        int $accountId,
        string $projectName,
        string $projectType
    ): void {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                $response['headers'],
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

        $gc->projectsPost($accountId, $projectName, $projectType);
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
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 404,
                'msg' => '{"error":"Project Not Found","code":404}',
            ],
            [
                'code' => 404,
                'headers' => ['Content-Type' => 'application/json'],
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
                $response['headers'],
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
        return static::basicFailCasesGet($data);
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
                $response['headers'],
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
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => 'API Error: "Project Not Found"',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
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
                $response['headers'],
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
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => 'API Error: "Item Not Found"',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
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
                $response['headers'],
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
                $response['headers'],
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

    public function casesItemApplyTemplatePost(): array
    {
        return [
            'basic' => [
                [
                    'code' => 202,
                ],
                [
                    'code' => 202,
                    'body' => [],
                ],
                42,
                423
            ],
        ];
    }

    /**
     * @dataProvider casesItemApplyTemplatePost
     */
    public function testItemApplyTemplatePost(array $expected, array $response, int $itemId, int $templateId): void
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

        $client = (new GatherContentClient($client));
        $client->setOptions($this->gcClientOptions)
            ->itemApplyTemplatePost($itemId, $templateId);


        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals($expected['code'], $client->getResponse()->getStatusCode());
        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
    }

    public function casesItemApplyTemplatePostFail(): array
    {
        $cases = static::basicFailCasesPost(['id' => 0]);
        $cases['empty'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing template_id","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing template_id',
                    'code' => 400
                ],
            ],
            42,
            0
        ];

        return $cases;
    }

    /**
     * @dataProvider casesItemApplyTemplatePostFail
     */
    public function testItemApplyTemplatePostFail(array $expected, array $response, int $itemId, int $templateId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                $response['headers'],
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

        $gc->itemApplyTemplatePost($itemId, $templateId);
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
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => 'API Error: "Project Not Found"',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
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
                $response['headers'],
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
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => \Exception::class,
                'code' => 200,
                'msg' => 'API Error: "Template Not Found"',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
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
                $response['headers'],
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

    public function casesItemsPost()
    {
        $item = new Item();

        $tab = new Tab();
        $tab->label = 'Test tab';
        $tab->id = 'test_tab';
        $tab->hidden = false;

        $item->config[$tab->id] = $tab;

        $text = new ElementText();
        $text->label = 'Test text';
        $text->id = 'test_text';
        $text->type = 'text';
        $text->limitType = 'words';
        $text->limit = 1000;
        $text->value = 'Test value';

        $item->config[$tab->id]->elements[$text->id] = $text;

        return [
            'empty' => [
                131313,
                'test item empty',
                0,
                0,
                [],
                13,
            ],
            'custom' => [
                131313,
                'test item custom',
                500,
                0,
                $item->config,
                13,
            ],
        ];
    }

    /**
     * @dataProvider casesItemsPost
     */
    public function testItemsPost($projectId, $name, $parentId, $templateId, $config, $resultItemId): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                202,
                [
                    'Content-Type' => 'application/json',
                    'Location' => "{$this->gcClientOptions['baseUri']}/items/$resultItemId",
                ]
            ),
            new RequestException('Error Communicating with Server', new Request('POST', 'items'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $actual = (new GatherContentClient($client))
          ->setOptions($this->gcClientOptions)
          ->itemsPost($projectId, $name, $parentId, $templateId, $config);

        static::assertEquals($resultItemId, $actual);

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $queryString = $requestBody->getContents();
        $sentQueryVariables = [];
        parse_str($queryString, $sentQueryVariables);

        if ($parentId) {
            static::assertArrayHasKey('parent_id', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['parent_id'], $parentId);
        } else {
            static::assertArrayNotHasKey('parent_id', $sentQueryVariables);
        }

        if ($templateId) {
            static::assertArrayHasKey('template_id', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['template_id'], $templateId);
        } else {
            static::assertArrayNotHasKey('template_id', $sentQueryVariables);
        }

        if ($config) {
            static::assertArrayHasKey('config', $sentQueryVariables);

            $config = array_values($config);
            $jsonConfig = \GuzzleHttp\json_encode($config);
            $encodedConfig = base64_encode($jsonConfig);

            static::assertEquals($sentQueryVariables['config'], $encodedConfig);
        } else {
            static::assertArrayNotHasKey('config', $sentQueryVariables);
        }
    }

    public function testItemsPostNoPath()
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                202,
                [
                    'Content-Type' => 'application/json',
                    'Location' => $this->gcClientOptions['baseUri'],
                ]
            ),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client([
          'handler' => $handlerStack,
        ]);

        static::expectException(\Exception::class);
        (new GatherContentClient($client))
          ->setOptions($this->gcClientOptions)
          ->itemsPost(0, 'test');
    }

    public function testItemsPostUnexpectedStatusCode()
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
          new Response(200, []),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client([
          'handler' => $handlerStack,
        ]);

        static::expectException(\Exception::class);
        (new GatherContentClient($client))
          ->setOptions($this->gcClientOptions)
          ->itemsPost(0, 'test');
    }
}
