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
     * @return $this
     */
    protected function sendGet(string $path, array $options = [])
    {
        return $this->sendRequest('GET', $path, $options);
    }

    /**
     * @return $this
     */
    protected function sendPost(string $path, array $options = [])
    {
        return $this->sendRequest('POST', $path, $options);
    }

    /**
     * @return $this
     */
    protected function sendRequest(string $method, string $path, array $options = [])
    {
        $options += [
            'auth' => $this->getRequestAuth(),
            'headers' => [],
        ];

        $options['headers'] += $this->getRequestHeaders();

        $uri = $this->getUri($path);
        $this->response = $this->client->request($method, $uri, $options);

        return $this;
    }

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
    public function meGet(): ?DataTypes\User
    {
        $this->sendGet('me');

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : new DataTypes\User($body['data']);
    }

    /**
     * {@inheritdoc}
     */
    public function accountsGet(): array
    {
        $this->sendGet('accounts');

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Account::class);
    }

    /**
     * {@inheritdoc}
     */
    public function accountGet(int $accountId): ?DataTypes\Account
    {
        $this->sendGet("accounts/$accountId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : new DataTypes\Account($body['data']);
    }

    /**
     * {@inheritdoc}
     */
    public function projectsGet(int $accountId): array
    {
        $this->sendGet('projects', ['query' => ['account_id' => $accountId]]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Project::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectGet(int $projectId): ?DataTypes\Project
    {
        $this->sendGet("projects/$projectId");

        $this->validateResponse();
        $body = $this->parseResponse();
        $body += ['meta' => []];

        return empty($body['data']) ? null : new DataTypes\Project($body['data'] + ['meta' => $body['meta']]);
    }

    /**
     * {@inheritdoc}
     */
    public function projectsPost(int $accountId, string $projectName, string $projectType): int
    {
        $this->sendPost('projects', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'account_id' => $accountId,
                'name' => $projectName,
                'type' => $projectType,
            ],
        ]);

        if ($this->response->getStatusCode() !== 202) {
            $responseContentType = $this->response->getHeader('Content-Type');
            $responseContentType = end($responseContentType);

            if ($responseContentType === 'application/json') {
                $this->parseResponse();
            }

            throw new \Exception('Unexpected answer', 1);
        }

        $locations = $this->response->getHeader('Location');
        $locationPath = parse_url(reset($locations), PHP_URL_PATH);
        $matches = [];
        if (!preg_match('@/projects/(?P<projectId>\d+)$@', $locationPath, $matches)) {
            throw new \Exception('Invalid response header the project ID is missing', 1);
        }

        return $matches['projectId'];
    }

    /**
     * {@inheritdoc}
     */
    public function projectStatusesGet(int $projectId): array
    {
        $this->sendGet("projects/$projectId/statuses");

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Status::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectStatusGet(int $projectId, int $statusId): ?DataTypes\Status
    {
        $this->sendGet("projects/$projectId/statuses/$statusId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : new DataTypes\Status($body['data']);
    }

    /**
     * {@inheritdoc}
     */
    public function itemsGet(int $projectId): array
    {
        $this->sendGet('items', ['query' => ['project_id' => $projectId]]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemGet(int $itemId): ?DataTypes\Item
    {
        $this->sendGet("items/$itemId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : new DataTypes\Item($body['data']);
    }

    public function itemsPost(
        int $projectId,
        string $name,
        int $parentId = 0,
        int $templateId = 0,
        array $config = []
    ): int {
        $form_params = [
            'project_id' => $projectId,
            'name' => $name,
        ];

        if ($parentId) {
            $form_params['parent_id'] = $parentId;
        }

        if ($templateId) {
            $form_params['template_id'] = $templateId;
        }

        if ($config) {
            $config = array_values($config);
            $form_params['config'] = base64_encode(\GuzzleHttp\json_encode($config));
        }

        $this->sendPost('items', [
            'form_params' => $form_params,
        ]);

        if ($this->response->getStatusCode() !== 202) {
            throw new \Exception("Unexpected status code: {$this->response->getStatusCode()}");
        }

        $locations = $this->response->getHeader('Location');
        $locationPath = parse_url(reset($locations), PHP_URL_PATH);
        $matches = [];
        if (!preg_match('@/items/(?P<itemId>\d+)$@', $locationPath, $matches)) {
            throw new \Exception('@todo Where is the new item ID?');
        }

        return $matches['itemId'];
    }

    public function itemSavePost(int $itemId, array $config): void
    {
        $formParams = [];
        $config = array_values($config);
        $jsonConfig = \GuzzleHttp\json_encode($config);
        $encodedConfig = base64_encode($jsonConfig);
        $formParams['config'] = $encodedConfig;

        $this->sendPost("items/$itemId/save", [
            'form_params' => $formParams,
        ]);

        if ($this->response->getStatusCode() !== 202) {
            throw new \Exception("Unexpected status code: {$this->response->getStatusCode()}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function itemApplyTemplatePost(int $itemId, int $templateId): void
    {
        $this->sendPost("items/$itemId/apply_template", [
            'form_params' => [
                'template_id' => $templateId,
            ],
        ]);

        if ($this->response->getStatusCode() !== 202) {
            $responseContentType = $this->response->getHeader('Content-Type');
            $responseContentType = end($responseContentType);

            if ($responseContentType === 'application/json') {
                $this->parseResponse();
            }

            throw new \Exception('Unexpected answer', 1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function itemChooseStatusPost(int $itemId, int $statusId): void
    {
        $this->sendPost("items/$itemId/choose_status", [
            'form_params' => [
                'status_id' => $statusId,
            ],
        ]);

        if ($this->response->getStatusCode() !== 202) {
            $responseContentType = $this->response->getHeader('Content-Type');
            $responseContentType = end($responseContentType);

            if ($responseContentType === 'application/json') {
                $this->parseResponse();
            }

            throw new \Exception('Unexpected answer', 1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function itemFilesGet(int $itemId): array
    {
        $this->sendGet("items/$itemId/files");

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\File::class);
    }

    public function templatesGet(int $projectId): array
    {
        $this->sendGet('templates', ['query' => ['project_id' => $projectId]]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Template::class);
    }

    public function templateGet(int $templateId): ?DataTypes\Template
    {
        $this->sendGet("templates/$templateId");

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
