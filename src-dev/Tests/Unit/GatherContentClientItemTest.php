<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\ElementText;
use Cheppers\GatherContent\DataTypes\Item;
use Cheppers\GatherContent\DataTypes\Tab;
use Cheppers\GatherContent\GatherContentClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Class GatherContentClientItemTest.
 *
 * @package Cheppers\GatherContent\Tests\Unit
 */
class GatherContentClientItemTest extends GcBaseTestCase
{
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
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/{$itemId}/apply_template",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $queryString = $requestBody->getContents();
        $sentQueryVariables = [];
        parse_str($queryString, $sentQueryVariables);

        if ($templateId) {
            static::assertArrayHasKey('template_id', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['template_id'], $templateId);
        } else {
            static::assertArrayNotHasKey('template_id', $sentQueryVariables);
        }
    }

    public function casesItemApplyTemplatePostFail(): array
    {
        $cases = static::basicFailCasesPost(['id' => 0]);
        $cases['missing_item'] = [
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
            0,
            423
        ];
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

    public function casesItemChooseStatusPost(): array
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
     * @dataProvider casesItemChooseStatusPost
     */
    public function testItemChooseStatusPost(array $expected, array $response, int $itemId, int $statusId): void
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
            ->itemChooseStatusPost($itemId, $statusId);


        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals($expected['code'], $client->getResponse()->getStatusCode());
        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/{$itemId}/choose_status",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $queryString = $requestBody->getContents();
        $sentQueryVariables = [];
        parse_str($queryString, $sentQueryVariables);

        if ($statusId) {
            static::assertArrayHasKey('status_id', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['status_id'], $statusId);
        } else {
            static::assertArrayNotHasKey('status_id', $sentQueryVariables);
        }
    }

    public function casesItemChooseStatusPostFail(): array
    {
        $cases = static::basicFailCasesPost(['id' => 0]);
        $cases['missing_item'] = [
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
            0,
            423
        ];
        $cases['empty'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing status_id","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing status_id',
                    'code' => 400
                ],
            ],
            42,
            0
        ];

        return $cases;
    }

    /**
     * @dataProvider casesItemChooseStatusPostFail
     */
    public function testItemChooseStatusPostFail(array $expected, array $response, int $itemId, int $statusId): void
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

        $gc->itemChooseStatusPost($itemId, $statusId);
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
                234,
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

    public function casesItemSavePost()
    {
        $item = new Item();

        $tab = new Tab();
        $tab->id = 'tab1';
        $tab->label = 'Tab 1';
        $tab->hidden = false;

        $text = new ElementText();
        $text->id = 'test-text';
        $text->label = 'Test';
        $text->value = 'Test text';

        $tab->elements[$text->id] = $text;
        $item->config[$tab->id] = $tab;

        return [
            'empty' => [
                131313,
                [],
            ],
            'basic' => [
                131313,
                $item->config,
            ],
        ];
    }

    /**
     * @dataProvider casesItemSavePost
     */
    public function testItemSavePost($itemId, $config)
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(202),
            new RequestException('Error Communicating with Server', new Request('POST', "items/$itemId/save"))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client([
            'handler' => $handlerStack,
        ]);

        (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->itemSavePost($itemId, $config);

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/$itemId/save",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $queryString = $requestBody->getContents();
        $sentQueryVariables = [];
        parse_str($queryString, $sentQueryVariables);

        $config = array_values($config);
        $jsonConfig = \GuzzleHttp\json_encode($config);
        $encodedConfig = base64_encode($jsonConfig);

        static::assertArrayHasKey('config', $sentQueryVariables);
        static::assertEquals($encodedConfig, $sentQueryVariables['config']);
    }

    public function testItemSavePostUnexpectedStatusCode()
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
            ->itemSavePost(131313, []);
    }
}
