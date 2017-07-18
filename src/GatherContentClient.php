<?php

namespace Cheppers\GatherContent;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class GatherContentClient implements GatherContentClientInterface
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    //region response
    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
    //endregion

    // region Option - email.
    /**
     * @var string
     */
    protected $email = '';

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return $this
     */
    public function setEmail(string $value)
    {
        $this->email = $value;

        return $this;
    }
    // endregion

    //region Option - apiKey
    /**
     * @var string
     */
    protected $apiKey = '';

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }
    //endregion

    // region Option - baseUri.
    /**
     * @var string
     */
    protected $baseUri = 'https://api.gathercontent.com';

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * @return $this
     */
    public function setBaseUri(string $value)
    {
        $this->baseUri = $value;

        return $this;
    }
    // endregion

    /**
     * {@inheritdoc}
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'baseUri':
                    $this->setBaseUri($value);
                    break;

                case 'email':
                    $this->setEmail($value);
                    break;

                case 'apiKey':
                    $this->setApiKey($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function projectTypes(): array
    {
        return [
            static::PROJECT_TYPE_WEBSITE_BUILDING,
            static::PROJECT_TYPE_ONGOING_WEBSITE_CONTENT,
            static::PROJECT_TYPE_MARKETING_EDITORIAL_CONTENT,
            static::PROJECT_TYPE_EMAIL_MARKETING_CONTENT,
            static::PROJECT_TYPE_OTHER,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function meGet(): DataTypes\User
    {
        $this->response = $this->client->request(
            'GET',
            $this->getUri('me'),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
            ]
        );

        $this->validateResponse();
        $body = $this->parseResponse();

        return new DataTypes\User($body['data']);
    }

    /**
     * {@inheritdoc}
     */
    public function accountsGet(): array
    {
        $this->response = $this->client->request(
            'GET',
            $this->getUri('accounts'),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
            ]
        );

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Account::class);
    }

    /**
     * {@inheritdoc}
     */
    public function accountGet(int $accountId): DataTypes\Account
    {
        $this->response = $this->client->request(
            'GET',
            $this->getUri("accounts/$accountId"),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
            ]
        );

        $this->validateResponse();
        $body = $this->parseResponse();

        return new DataTypes\Account($body['data']);
    }

    /**
     * {@inheritdoc}
     */
    public function projectsGet(int $accountId): array
    {
        $this->response = $this->client->request(
            'GET',
            $this->getUri('projects'),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
                'query' => [
                    'account_id' => $accountId,
                ],
            ]
        );

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Project::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectGet(int $projectId): DataTypes\Project
    {
        $this->response = $this->client->request(
            'GET',
            $this->getUri("projects/$projectId"),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
            ]
        );

        $this->validateResponse();
        $body = $this->parseResponse();
        $body += ['meta' => []];

        return new DataTypes\Project($body['data'] + ['meta' => $body['meta']]);
    }

    /**
     * {@inheritdoc}
     */
    public function projectsPost(int $accountId, string $projectName, string $projectType): int
    {
        $this->response = $this->client->request(
            'POST',
            $this->getUri('projects'),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ]),
                'form_params' => [
                    'account_id' => $accountId,
                    'name' => $projectName,
                    'type' => $projectType,
                ],
            ]
        );

        if ($this->response->getStatusCode() !== 202) {
            throw new \Exception('@todo ' . __METHOD__);
        }

        $locations = $this->response->getHeader('Location');
        $locationPath = parse_url(reset($locations), PHP_URL_PATH);
        $matches = [];
        if (!preg_match('@/projects/(?P<projectId>\d+)$@', $locationPath, $matches)) {
            throw new \Exception('@todo Where is the new project ID?');
        }

        return $matches['projectId'];
    }

    /**
     * {@inheritdoc}
     */
    public function projectStatusesGet(int $projectId): array
    {
        $this->response = $this->client->request(
            'GET',
            $this->getUri("projects/$projectId/statuses"),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
            ]
        );

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Status::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectStatusGet(int $projectId, int $statusId): DataTypes\Status
    {
        $this->response = $this->client->request(
            'GET',
            $this->getUri("projects/$projectId/statuses/$statusId"),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
            ]
        );

        $this->validateResponse();
        $body = $this->parseResponse();

        return new DataTypes\Status($body['data']);
    }

    /**
     * {@inheritdoc}
     */
    public function itemsGet(int $projectId): array
    {
        $this->response = $this->client->request(
            'GET',
            $this->getUri('items'),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
                'query' => [
                    'project_id' => $projectId,
                ],
            ]
        );

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemGet(int $itemId): ?DataTypes\Item
    {
        $this->response = $this->client->request(
            'GET',
            $this->getUri("items/$itemId"),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
            ]
        );

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : new DataTypes\Item($body['data']);
    }

    public function itemSavePost(int $itemId)
    {
        //
    }

    public function itemApplyTemplatePost(int $itemId, int $templateId)
    {
        $this->response = $this->client->request(
            'POST',
            $this->getUri('items/' . $itemId . '/apply_template'),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
                'form_params' => [
                    'template_id' => $templateId,
                ],
            ]
        );

        if ($this->response->getStatusCode() !== 202) {
            throw new \Exception('@todo ' . __METHOD__);
        }

        return $itemId;
    }

    public function itemChooseStatusPost(int $itemId)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function itemFilesGet(int $itemId): array
    {

        $this->response = $this->client->request(
            'GET',
            $this->getUri("items/$itemId/files"),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
            ]
        );

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\File::class);
    }

    public function templatesGet(int $projectId): array
    {
        $this->response = $this->client->request(
            'GET',
            $this->getUri('templates'),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
                'query' => [
                    'project_id' => $projectId,
                ],
            ]
        );

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Template::class);
    }

    public function templateGet(int $templateId): ?DataTypes\Template
    {
        $this->response = $this->client->request(
            'GET',
            $this->getUri("templates/$templateId"),
            [
                'auth' => $this->getRequestAuth(),
                'headers' => $this->getRequestHeaders(),
            ]
        );

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : new DataTypes\Template($body['data']);
    }

    protected function getUri(string $path): string
    {
        return $this->getBaseUri() . "/$path";
    }

    protected function getRequestAuth(): array
    {
        return [
            $this->getEmail(),
            $this->getApiKey(),
        ];
    }

    protected function getRequestHeaders(array $base = []): array
    {
        return $base + [
            'Accept' => 'application/vnd.gathercontent.v0.5+json',
        ];
    }

    protected function parseResponse(): array
    {
        $body = \GuzzleHttp\json_decode($this->response->getBody(), true);
        if (!empty($body['data']['message'])) {
            throw new \Exception('API Error: "' . $body['data']['message'] . '"', $this->response->getStatusCode());
        }

        return $body;
    }

    /**
     * @return \Cheppers\GatherContent\DataTypes\Base[]
     */
    protected function parseResponseDataItems(array $data, string $class): array
    {
        $items = [];
        foreach ($data as $itemData) {
            $item = $this->parseResponseDataItem($itemData, $class);
            $items[$item->id] = $item;
        }

        return $items;
    }

    protected function parseResponseDataItem(array $data, string $class): DataTypes\Base
    {
        return $item = new $class($data);
    }

    protected function validateResponse(): void
    {
        $responseContentType = $this->response->getHeader('Content-Type');
        $responseContentType = end($responseContentType);
        if ($responseContentType !== 'application/json') {
            throw new \Exception("Unexpected Content-Type: '$responseContentType'", 1);
        }
    }
}
