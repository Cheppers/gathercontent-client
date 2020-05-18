<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Template;
use Cheppers\GatherContent\GatherContentClient;
use Cheppers\GatherContent\GatherContentClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class GatherContentClientTemplateTest extends GcBaseTestCase
{
    public function casesTemplatesGet()
    {
        $data = [
            static::getUniqueResponseTemplate(),
            static::getUniqueResponseTemplate()
        ];

        $templates = static::reKeyArray($data, 'id');

        foreach ($templates as &$template) {
            $template = new Template($template);
        }

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $templates,
                ['data' => $templates],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesTemplatesGet
     */
    public function testTemplatesGet(array $expected, array $responseBody, $projectId)
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
            ->templatesGet($projectId);

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
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/templates",
            (string) $request->getUri()
        );
    }

    public function casesTemplatesGetFail()
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
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesTemplatesGetFail
     */
    public function testTemplatesGetFail(array $expected, array $response, $projectId)
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

        $gc->templatesGet($projectId);
    }

    public function casesTemplateGet()
    {
        $data = static::getUniqueResponseTemplate();

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $data,
                ['data' => $data],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesTemplateGet
     */
    public function testTemplateGet(array $expected, array $responseBody, $templateId)
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
            ->templateGet($templateId);

        if ($expected) {
            static::assertTrue($actual instanceof Template, 'Data type of the return is Template');
            static::assertEquals(
                \GuzzleHttp\json_encode($expected, JSON_PRETTY_PRINT),
                \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
            );
        } else {
            static::assertNull($actual);
        }

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/templates/$templateId",
            (string) $request->getUri()
        );
    }

    public function casesTemplateGetFail()
    {
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Template Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Template Not Found',
                    'code' => 404
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesTemplateGetFail
     */
    public function testTemplateGetFail(array $expected, array $response, $templateId)
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

        $gc->templateGet($templateId);
    }

    // TODO: Update templatePost test when Structure is ready!

    public function casesItemPost()
    {
        $templateArray = static::getUniqueResponseTemplate();
        $template = new Template($templateArray);

        return [
            'basic' => [
                $template,
                $template->name,
                [],
                131313,
                $template->id,
            ],
            'empty' => [
                $template,
                $template->name,
                [],
                131313,
                $template->id,
            ],
        ];
    }

    /**
     * @dataProvider casesItemPost
     */
    public function testItemPost(Template $expected, $name, $structure, $projectId, $resultItemId)
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
            ->templatePost($projectId, $name, $structure);

        $actual->setSkipEmptyProperties(true);

        static::assertEquals($resultItemId, $actual->id);

        static::assertTrue($actual instanceof Template, 'Data type of the return is Item');
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
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/templates",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        static::assertArrayHasKey('name', $sentQueryVariables);
        static::assertEquals($sentQueryVariables['name'], $expected->name);
    }

    public function casesTemplateRenamePost()
    {
        $templateArray = static::getUniqueResponseTemplate();
        $template = new Template($templateArray);

        return [
            'basic' => [
                $template,
                13,
                $template->name,
            ],
        ];
    }

    /**
     * @dataProvider casesTemplateRenamePost
     */
    public function testTemplateRenamePost(Template $template, $templateId, $name)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $template])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->templateRenamePost($templateId, $name);

        static::assertTrue($actual instanceof Template, 'Data type of the return is Item');
        static::assertEquals(
            \GuzzleHttp\json_encode($template, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/templates/$templateId/rename",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        static::assertArrayHasKey('name', $sentQueryVariables);
        static::assertArrayNotHasKey('content', $sentQueryVariables);
        static::assertEquals($sentQueryVariables['name'], $name);
    }

    public function casesTemplateRenamePostFail()
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
                'msg' => 'API Error: "Item Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Item Not Found',
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
            1,
            ''
        ];

        return $cases;
    }

    /**
     * @dataProvider casesTemplateRenamePostFail
     */
    public function testTemplateRenamePostFail(array $expected, array $response, $templateId, $name)
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

        $gc->templateRenamePost($templateId, $name);
    }

    public function casesTemplateDuplicatePost()
    {
        $templateArray = static::getUniqueResponseItem();
        $template = new Template($templateArray);

        return [
            'basic' => [
                $template,
                $template->id,
                $template->projectId
            ],
            'empty_project' => [
                $template,
                $template->id,
                null
            ],
        ];
    }

    /**
     * @dataProvider casesTemplateDuplicatePost
     */
    public function testTemplateDuplicateTemplatePost(Template $template, $templateId, $projectId = null)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                201,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode(['data' => $template])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $client = (new GatherContentClient($client));
        $actual = $client->setOptions($this->gcClientOptions)
            ->templateDuplicatePost($templateId, $projectId);

        static::assertTrue($actual instanceof Template, 'Data type of the return is Item');
        static::assertEquals(
            \GuzzleHttp\json_encode($template, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/templates/$templateId/duplicate",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        if (!empty($projectId)) {
            static::assertArrayHasKey('project_id', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['project_id'], $projectId);
        } else {
            static::assertArrayNotHasKey('project_id', $sentQueryVariables);
        }
    }

    public function casesTemplateDuplicatePostFail()
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
            1
        ];
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Item Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Item Not Found',
                    'code' => 404
                ],
            ],
            0
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
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesTemplateDuplicatePostFail
     */
    public function testTemplateDuplicatePostFail(array $expected, array $response, $templateId)
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

        $gc->templateDuplicatePost($templateId);
    }

    public function casesTemplateDelete()
    {
        return [
            'basic' => [
                13
            ],
        ];
    }

    /**
     * @dataProvider casesTemplateDelete
     */
    public function testTemplateDelete($templateId)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                204,
                [
                    'Content-Type' => 'application/json',
                ],
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->templateDelete($templateId);

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('DELETE', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/templates/$templateId",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        static::assertEmpty($requestBody->getContents());
    }

    public function casesTemplateDeleteFail()
    {
        $cases['wrong_type'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::UNEXPECTED_CONTENT_TYPE,
                'msg' => 'Unexpected Content-Type',
            ],
            [
                'code' => 204,
                'headers' => ['Content-Type' => 'image/jpeg'],
                'body' => [],
            ],
            1
        ];
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Item Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Item Not Found',
                    'code' => 404
                ],
            ],
            0
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
            42
        ];

        return $cases;
    }

    // TODO: reactivate test when the response got fixed on GC.
    /**
     * @dataProvider casesTemplateDeleteFail
     */
    public function _testTemplateDeleteFail(array $expected, array $response, $templateId)
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

        $gc->templateDelete($templateId);
    }
}
