<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Component;
use Cheppers\GatherContent\GatherContentClient;
use Cheppers\GatherContent\GatherContentClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class GatherContentClientComponentTest extends GcBaseTestCase
{
    public function casesComponentsGet()
    {
        $data = [
            static::getUniqueResponseComponent(['text', 'files', 'choice_radio', 'choice_checkbox']),
            static::getUniqueResponseComponent(['text', 'files', 'choice_radio', 'choice_checkbox'])
        ];

        $components = [];
        foreach ($data as $component) {
            $components[] = new Component($component);
        }

        return [
            'empty' => [
                [],
                ['data' => []],
                'uuid-42',
            ],
            'basic' => [
                $components,
                ['data' => $components],
                'uuid-42',
            ],
        ];
    }

    /**
     * @dataProvider casesComponentsGet
     */
    public function testComponentsGet(array $expected, array $responseBody, $projectId)
    {
        $tester = $this->getBasicHttpClientTester(
            [
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    \GuzzleHttp\json_encode($responseBody)
                ),
            ]
        );
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->componentsGet($projectId);

        static::assertEquals(
            \GuzzleHttp\json_encode($expected, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual['data'], JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/components",
            (string) $request->getUri()
        );
    }

    public function casesComponentsGetFail()
    {
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Project Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Project Not Found',
                    'code' => 404
                ],
            ],
            'uuid-42'
        ];

        return $cases;
    }

    /**
     * @dataProvider casesComponentsGetFail
     */
    public function testComponentsGetFail(array $expected, array $response, $projectId)
    {
        $tester = $this->getBasicHttpClientTester(
            [
                new Response(
                    $response['code'],
                    $response['headers'],
                    \GuzzleHttp\json_encode($response['body'])
                ),
            ]
        );
        $client = $tester['client'];

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->componentsGet($projectId);
    }

    public function casesComponentGet()
    {
        $data = static::getUniqueResponseComponent(['text', 'files', 'choice_radio', 'choice_checkbox']);

        return [
            'empty' => [
                ['data' => []],
                ['data' => []],
                'uuid-42',
            ],
            'basic' => [
                ['data' => $data],
                ['data' => $data],
                'uuid-42',
            ],
        ];
    }

    /**
     * @dataProvider casesComponentGet
     */
    public function testComponentGet(array $expected, array $responseBody, $componentUuid)
    {
        $tester = $this->getBasicHttpClientTester(
            [
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    \GuzzleHttp\json_encode($responseBody)
                ),
            ]
        );
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->componentGet($componentUuid);

        if (!empty($expected['data'])) {
            static::assertTrue($actual['data'] instanceof Component, 'Data type of the return is Component');
            static::assertEquals(
                \GuzzleHttp\json_encode($expected['data'], JSON_PRETTY_PRINT),
                \GuzzleHttp\json_encode($actual['data'], JSON_PRETTY_PRINT)
            );
        } else {
            static::assertNull($actual['data']);
        }

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/components/$componentUuid",
            (string) $request->getUri()
        );
    }

    public function casesComponentGetFail()
    {
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Component Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Component Not Found',
                    'code' => 404
                ],
            ],
            'uuid-42'
        ];

        return $cases;
    }

    /**
     * @dataProvider casesComponentGetFail
     */
    public function testComponentGetFail(array $expected, array $response, $componentUuid)
    {
        $tester = $this->getBasicHttpClientTester(
            [
                new Response(
                    $response['code'],
                    $response['headers'],
                    \GuzzleHttp\json_encode($response['body'])
                ),
            ]
        );
        $client = $tester['client'];

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->componentGet($componentUuid);
    }

    public function casesComponentPost()
    {
        $componentFields = ['text', 'files', 'choice_radio', 'choice_checkbox'];
        $component = new Component(static::getUniqueResponseComponent($componentFields));

        $emptyComponent = new Component(static::getUniqueResponseComponent([]));

        return [
            'basic' => [
                $component,
                $component->name,
                $component->id,
                $componentFields,
                131313,
                $component->id,
            ],
            'empty' => [
                $emptyComponent,
                $emptyComponent->name,
                $emptyComponent->id,
                [],
                131313,
                $emptyComponent->id,
            ],
        ];
    }

    /**
     * @dataProvider casesComponentPost
     */
    public function testComponentPost(Component $expected, $name, $componentUuid, $fields, $projectId, $resultUuid)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                201,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $expected])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->componentPost($projectId, $name, $componentUuid, $fields);

        $actual->setSkipEmptyProperties(false);

        static::assertEquals($resultUuid, $actual->id);

        static::assertTrue($actual instanceof Component, 'Data type of the return is Component');
        static::assertEquals(
            \GuzzleHttp\json_encode($expected, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/components",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        static::assertArrayHasKey('name', $sentQueryVariables);
        static::assertEquals($sentQueryVariables['name'], $expected->name);
    }

    public function casesComponentPostFail()
    {
        $cases['wrong_type'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::UNEXPECTED_CONTENT_TYPE,
                'msg' => 'Unexpected Content-Type',
            ],
            [
                'code' => 201,
                'headers' => ['Content-Type' => 'image/jpeg'],
                'body' => [],
            ],
            1,
        ];
        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Component Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Component Not Found',
                    'code' => 404
                ],
            ],
            42,
        ];

        return $cases;
    }

    /**
     * @dataProvider casesComponentPostFail
     */
    public function testComponentPostFail(array $expected, array $response, $projectId)
    {
        $tester = $this->getBasicHttpClientTester(
            [
                new Response(
                    $response['code'],
                    $response['headers'],
                    \GuzzleHttp\json_encode($response['body'])
                ),
            ]
        );
        $client = $tester['client'];

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->componentPost($projectId, 'name', 'uuid-123', []);
    }

    public function casesComponentRenamePost()
    {
        $componentFields = ['text', 'files', 'choice_radio', 'choice_checkbox'];
        $component = new Component(static::getUniqueResponseComponent($componentFields));

        return [
            'basic' => [
                $component,
                'uuid-13',
                $component->name,
            ],
        ];
    }

    /**
     * @dataProvider casesComponentRenamePost
     */
    public function testComponentRenamePost(Component $component, $componentUuid, $name)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $component])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->componentRenamePost($componentUuid, $name);

        static::assertTrue($actual instanceof Component, 'Data type of the return is Component');
        static::assertEquals(
            \GuzzleHttp\json_encode($component, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/components/$componentUuid/rename",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        static::assertArrayHasKey('name', $sentQueryVariables);
        static::assertArrayNotHasKey('content', $sentQueryVariables);
        static::assertEquals($sentQueryVariables['name'], $name);
    }

    public function casesComponentRenamePostFail()
    {
        $cases['wrong_type'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::UNEXPECTED_CONTENT_TYPE,
                'msg' => 'Unexpected Content-Type',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'image/jpeg'],
                'body' => [],
            ],
            1,
            ''
        ];
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Component Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Component Not Found',
                    'code' => 404
                ],
            ],
            1,
            ''
        ];
        $cases['empty'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing component_uuid","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing component_uuid',
                    'code' => 400
                ],
            ],
            1,
            ''
        ];

        return $cases;
    }

    /**
     * @dataProvider casesComponentRenamePostFail
     */
    public function testComponentRenamePostFail(array $expected, array $response, $componentUuid, $name)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                $response['code'],
                $response['headers'],
                \GuzzleHttp\json_encode($response['body'])
            ),
        ]);
        $client = $tester['client'];

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->componentRenamePost($componentUuid, $name);
    }

    public function casesComponentDelete()
    {
        return [
            'basic' => [
                'uuid-13'
            ],
        ];
    }

    /**
     * @dataProvider casesComponentDelete
     */
    public function testComponentDelete($componentUuid)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                204,
                [
                    'Content-Type' => 'application/json',
                ]
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->componentDelete($componentUuid);

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('DELETE', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/components/$componentUuid",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        static::assertEmpty($requestBody->getContents());
    }

    public function casesComponentUpdatePut()
    {
        $componentFields = ['text', 'files', 'choice_radio', 'choice_checkbox'];
        $component = new Component(static::getUniqueResponseComponent($componentFields));

        $emptyComponent = new Component(static::getUniqueResponseComponent([]));

        return [
            'basic' => [
                $component,
                $component->id,
                $componentFields,
                $component->id,
            ],
            'empty' => [
                $emptyComponent,
                $emptyComponent->id,
                [],
                $emptyComponent->id,
            ],
        ];
    }

    /**
     * @dataProvider casesComponentUpdatePut
     */
    public function testComponentUpdatePut(Component $expected, $componentUuid, $fields, $resultUuid)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                201,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $expected])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->componentUpdatePut($componentUuid, $fields);

        $actual->setSkipEmptyProperties(false);

        static::assertEquals($resultUuid, $actual->id);

        static::assertTrue($actual instanceof Component, 'Data type of the return is Component');
        static::assertEquals(
            \GuzzleHttp\json_encode($expected, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('PUT', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/components/$componentUuid/fields",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        static::assertArrayHasKey('fields', $sentQueryVariables);
        static::assertEquals(count($sentQueryVariables['fields']), count($expected->fields));
    }
}
