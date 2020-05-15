<?php

namespace Cheppers\GatherContent;

use Cheppers\GatherContent\DataTypes\Item;
use Cheppers\GatherContent\DataTypes\Pagination;
use GuzzleHttp\ClientInterface;

class GatherContentClient implements GatherContentClientInterface
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @var string
     */
    protected $email = '';

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($value)
    {
        $this->email = $value;

        return $this;
    }

    /**
     * @var string
     */
    protected $apiKey = '';

    /**
     * {@inheritdoc}
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @var string
     */
    protected $baseUri = 'https://api.gathercontent.com';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseUri($value)
    {
        $this->baseUri = $value;

        return $this;
    }

    /**
     * @var bool
     */
    protected $useLegacy = false;

    /**
     * {@inheritdoc}
     */
    public function getUseLegacy()
    {
        return $this->useLegacy;
    }

    /**
     * {@inheritdoc}
     */
    public function setUseLegacy($value)
    {
        $this->useLegacy = $value;

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

                case 'frameworkVersion':
                    $this->setFrameworkVersion($value);
                    break;

                case 'frameworkName':
                    $this->setFrameworkName($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function projectTypes()
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
    public function meGet()
    {
        $this->setUseLegacy(true);
        $this->sendGet('me');

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\User::class);
    }

    /**
     * {@inheritdoc}
     */
    public function accountsGet()
    {
        $this->setUseLegacy(true);
        $this->sendGet('accounts');

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseItems($body, DataTypes\Account::class);
    }

    /**
     * {@inheritdoc}
     */
    public function accountGet($accountId)
    {
        $this->setUseLegacy(true);
        $this->sendGet("accounts/$accountId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Account::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectsGet($accountId)
    {
        $this->setUseLegacy(true);
        $this->sendGet('projects', ['query' => ['account_id' => $accountId]]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseItems($body, DataTypes\Project::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectGet($projectId)
    {
        $this->setUseLegacy(true);
        $this->sendGet("projects/$projectId");

        $this->validateResponse();
        $body = $this->parseResponse();
        $body += ['meta' => []];

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'] + ['meta' => $body['meta']],
            DataTypes\Project::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectsPost($accountId, $projectName, $projectType)
    {
        $this->setUseLegacy(true);
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

        $this->validatePostResponse(202);

        $locations = $this->response->getHeader('Location');
        $locationPath = parse_url(reset($locations), PHP_URL_PATH);
        $matches = [];
        if (!preg_match('@/projects/(?P<projectId>\d+)$@', $locationPath, $matches)) {
            throw new GatherContentClientException(
                'Invalid response header the project ID is missing',
                GatherContentClientException::INVALID_RESPONSE_HEADER
            );
        }

        return $matches['projectId'];
    }

    /**
     * {@inheritdoc}
     */
    public function projectStatusesGet($projectId)
    {
        $this->setUseLegacy(true);
        $this->sendGet("projects/$projectId/statuses");

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseItems($body, DataTypes\Status::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectStatusGet($projectId, $statusId)
    {
        $this->setUseLegacy(true);
        $this->sendGet("projects/$projectId/statuses/$statusId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Status::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemsGet($projectId, $query = [])
    {
        $this->sendGet("projects/$projectId/items", ['query' => $query]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseItems($body, DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemGet($itemId)
    {
        $this->sendGet("items/$itemId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemPost($projectId, Item $item)
    {
        $item->setSkipEmptyProperties(true);
        $this->sendPost("projects/$projectId/items", [
            'body' => \GuzzleHttp\json_encode($item),
        ]);

        $this->validatePostResponse(201);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemUpdatePost($itemId, array $content = [])
    {
        $this->sendPost("items/$itemId/content", [
            'body' => \GuzzleHttp\json_encode(['content' => $content]),
        ]);

        $this->validatePostResponse(202);
    }

    /**
     * {@inheritdoc}
     */
    public function itemRenamePost($itemId, $name)
    {
        $this->sendPost("items/$itemId/rename", [
            'body' => \GuzzleHttp\json_encode(['name' => $name]),
        ]);

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemMovePost($itemId, $position = null, $folderUuid = '')
    {
        $request = [];

        if ($position !== null) {
            $request['position'] = $position;
        }

        if (!empty($folderUuid)) {
            $request['folder_uuid'] = $folderUuid;
        }

        $this->sendPost("items/$itemId/move", [
            'body' => \GuzzleHttp\json_encode($request),
        ]);

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        // TODO: change later, because now the data is not returned even though the documentation says so.
        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemApplyTemplatePost($itemId, $templateId)
    {
        $this->sendPost("items/$itemId/apply_template", [
            'body' => \GuzzleHttp\json_encode([
                'template_id' => $templateId
            ]),
        ]);

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemDisconnectTemplatePost($itemId)
    {
        $this->sendPost("items/$itemId/disconnect_template");

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemDuplicatePost($itemId)
    {
        $this->sendPost("items/$itemId/duplicate");

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemChooseStatusPost($itemId, $statusId)
    {
        $this->setUseLegacy(true);
        $this->sendPost("items/$itemId/choose_status", [
            'form_params' => [
                'status_id' => $statusId,
            ],
        ]);

        $this->validatePostResponse(202);
    }

    public function templatesGet($projectId)
    {
        $this->sendGet('templates', ['query' => ['project_id' => $projectId]]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseItems($body, DataTypes\Template::class);
    }

    public function templateGet($templateId)
    {
        $this->sendGet("templates/$templateId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Template::class);
    }

    protected function getUri($path)
    {
        return $this->getBaseUri()."/$path";
    }

    public function foldersGet($projectId)
    {
        $this->sendGet('folders', ['query' => ['project_id' => $projectId]]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseItems($body, DataTypes\Folder::class);
    }

    protected function getRequestAuth()
    {
        return [
            $this->getEmail(),
            $this->getApiKey(),
        ];
    }

    protected function getRequestHeaders(array $base = [])
    {
        $accept = 'application/vnd.gathercontent.v2+json';
        if ($this->useLegacy) {
            $accept = 'application/vnd.gathercontent.v0.5+json';
        }

        return $base + [
            'Accept' => $accept,
            'User-Agent' => $this->getVersionString(),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * @return string[]
     */
    public function getFrameworkName()
    {
        return $this->framework['name'];
    }

    protected $framework = ['name' => 'PHP', 'version' => 'UKNOWN'];

    /**
     * {@inheritdoc}
     */
    public function setFrameworkName($value)
    {
        $this->framework['name'] = $value;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getFrameworkVersion()
    {
        return $this->framework['version'];
    }

    /**
     * {@inheritdoc}
     */
    public function setFrameworkVersion($version)
    {
        $this->framework['version'] = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getIntegrationVersion()
    {
        return static::INTEGRATION_VERSION;
    }

    /**
     * @return string[]
     */
    public function getVersionString()
    {
        $frameworkName = $this->getFrameworkName();
        $frameworkVersion = $this->getFrameworkVersion();
        $integrationVersion = $this->getIntegrationVersion();
        return sprintf('Integration-%s-%s/%s', $frameworkName, $frameworkVersion, $integrationVersion);
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendGet($path, array $options = [])
    {
        return $this->sendRequest('GET', $path, $options);
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendPost($path, array $options = [])
    {
        return $this->sendRequest('POST', $path, $options);
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendRequest($method, $path, array $options = [])
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

    protected function parseResponse()
    {
        if ($this->getUseLegacy()) {
            return $this->parseLegacyResponse();
        }

        $body = \GuzzleHttp\json_decode($this->response->getBody(), true);
        if (!empty($body['error'])) {
            throw new GatherContentClientException(
                'API Error: "'.$body['error'].'", Code: '.$body['code'],
                GatherContentClientException::API_ERROR
            );
        }

        return $body;
    }

    /**
     * @deprecated Will be removed when v2 API fully developed.
     */
    protected function parseLegacyResponse()
    {
        $body = \GuzzleHttp\json_decode($this->response->getBody(), true);
        if (!empty($body['data']['message'])) {
            throw new GatherContentClientException(
                'API Error: "'.$body['data']['message'].'"',
                GatherContentClientException::API_ERROR
            );
        }

        return $body;
    }

    /**
     * @return \Cheppers\GatherContent\DataTypes\Base[]
     */
    protected function parseResponseItems(array $data, $class)
    {
        $items = ['data' => []];

        foreach ($data['data'] as $itemData) {
            $item = $this->parseResponseDataItem($itemData, $class);
            $items['data'][$item->id] = $item;
        }

        if (!empty($data['pagination'])) {
            $items['pagination'] = $this->parsePagination($data['pagination']);
        }

        return $items;
    }

    protected function parseResponseDataItem(array $data, $class)
    {
        return new $class($data);
    }

    protected function parsePagination(array $data)
    {
        return new Pagination($data);
    }

    protected function validateResponse()
    {
        $responseContentType = $this->response->getHeader('Content-Type');
        $responseContentType = end($responseContentType);
        if ($responseContentType !== 'application/json') {
            throw new GatherContentClientException(
                "Unexpected Content-Type: '$responseContentType'",
                GatherContentClientException::UNEXPECTED_CONTENT_TYPE
            );
        }
    }

    protected function validatePostResponse($code)
    {
        if ($this->response->getStatusCode() !== $code) {
            $responseContentType = $this->response->getHeader('Content-Type');
            $responseContentType = end($responseContentType);

            if ($responseContentType === 'application/json') {
                $this->parseResponse();
            }

            throw new GatherContentClientException(
                'Unexpected answer',
                GatherContentClientException::UNEXPECTED_ANSWER
            );
        }

        $this->validateResponse();
    }
}
