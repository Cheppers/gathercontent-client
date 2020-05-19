<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Folder;
use Cheppers\GatherContent\GatherContentClient;
use Cheppers\GatherContent\GatherContentClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class GatherContentClientFolderTest extends GcBaseTestCase
{
    public function casesFoldersGet()
    {
        $data = [
            static::getUniqueResponseFolder(),
            static::getUniqueResponseFolder(),
            static::getUniqueResponseFolder(),
        ];

        $folders = [];
        foreach ($data as $folder) {
            $folders[] = new Folder($folder);
        }

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
                1,
            ],
            'basic' => [
                $folders,
                ['data' => $data],
                42,
                0,
            ],
        ];
    }

    /**
     * @dataProvider casesFoldersGet
     */
    public function testFoldersGet(array $expected, array $responseBody, int $projectId, $includeTrashed)
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

        $folders = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->foldersGet($projectId, $includeTrashed);

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($folders['data'], JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/folders?include_trashed=$includeTrashed",
            (string) $request->getUri()
        );
    }

    public function casesFoldersGetFail()
    {
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Folders not found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Folders not found',
                    'code' => 404
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesFoldersGetFail
     */
    public function testFoldersGetFail(array $expected, array $response, int $projectId)
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

        $gc->foldersGet($projectId);
    }
}
