<?php

namespace Cheppers\GatherContent;

use Cheppers\GatherContent\DataTypes\Item;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

interface GatherContentClientInterface
{
    const PROJECT_TYPE_WEBSITE_BUILDING = 'website-build';

    const PROJECT_TYPE_ONGOING_WEBSITE_CONTENT = 'ongoing-website-content';

    const PROJECT_TYPE_MARKETING_EDITORIAL_CONTENT = 'marketing-editorial-content';

    const PROJECT_TYPE_EMAIL_MARKETING_CONTENT = 'email-marketing-content';

    const PROJECT_TYPE_OTHER = 'other';

    const INTEGRATION_VERSION = '1.0';

    public function getResponse();

    public function getEmail();

    /**
     * @return $this
     */
    public function setEmail($value);

    public function getApiKey();

    /**
     * @return $this
     */
    public function setApiKey($apiKey);

    public function getBaseUri();

    /**
     * @return $this
     */
    public function setBaseUri($value);

    public function getUseLegacy();

    /**
     * @return $this
     */
    public function setUseLegacy($value);

    /**
     * @return string[]
     */
    public function getIntegrationVersion();

    /**
     * @return $this
     */
    public function setFrameworkVersion($value);

    /**
     * @return string[]
     */
    public function getFrameworkVersion();

    /**
     * @return $this
     */
    public function setFrameworkName($value);

    /**
     * @return string[]
     */
    public function getFrameworkName();

    /**
     * @return string[]
     */
    public function getVersionString();

    /**
     * GatherContentClientInterface constructor.
     */
    public function __construct(ClientInterface $client);

    /**
     * @return string[]
     */
    public function projectTypes();

    /**
     * @see https://docs.gathercontent.com/v0.5/reference#get-me
     */
    public function meGet();

    /**
     * @see https://docs.gathercontent.com/v0.5/reference#get-accounts
     *
     * @return \Cheppers\GatherContent\DataTypes\Account[]
     */
    public function accountsGet();

    /**
     * @see https://docs.gathercontent.com/v0.5/reference#get-accounts
     */
    public function accountGet($accountId);

    /**
     * @see https://docs.gathercontent.com/v0.5/reference#get-projects
     *
     * @return \Cheppers\GatherContent\DataTypes\Project[]
     */
    public function projectsGet($accountId);

    /**
     * @see https://docs.gathercontent.com/v0.5/reference#get-projects
     */
    public function projectGet($projectId);

    /**
     * @see https://docs.gathercontent.com/v0.5/reference#post-projects
     *
     * @return int
     *   Id of the newly created project.
     */
    public function projectsPost($accountId, $projectName, $projectType);

    /**
     * @see https://docs.gathercontent.com/v0.5/reference#get-project-statuses
     *
     * @return \Cheppers\GatherContent\DataTypes\Status[]
     */
    public function projectStatusesGet($projectId);

    /**
     * @see https://docs.gathercontent.com/v0.5/reference#get-project-statuses-by-id
     */
    public function projectStatusGet($projectId, $statusId);

    /**
     * @see https://docs.gathercontent.com/reference#listitems
     *
     * @return \Cheppers\GatherContent\DataTypes\Item[]
     */
    public function itemsGet($projectId);

    /**
     * @see https://docs.gathercontent.com/reference#getitem
     *
     * @return \Cheppers\GatherContent\DataTypes\Item
     */
    public function itemGet($itemId);

    /**
     * @see https://docs.gathercontent.com/reference#createitem
     *
     * @return \Cheppers\GatherContent\DataTypes\Item
     */
    public function itemPost(
        $projectId,
        Item $item
    );

    /**
     * @see https://docs.gathercontent.com/reference#updateitemcontent
     */
    public function itemUpdatePost(
        $itemId,
        array $content = []
    );

    /**
     * @see https://docs.gathercontent.com/reference#renameitem
     *
     * @return \Cheppers\GatherContent\DataTypes\Item
     */
    public function itemRenamePost($itemId, $name);

    /**
     * @see https://docs.gathercontent.com/reference#moveitem
     *
     * @return \Cheppers\GatherContent\DataTypes\Item
     */
    public function itemMovePost(
        $itemId,
        $position = null,
        $folderUuid = ''
    );

    /**
     * @see https://docs.gathercontent.com/reference#applytemplate
     *
     * @return \Cheppers\GatherContent\DataTypes\Item
     */
    public function itemApplyTemplatePost($itemId, $templateId);

    /**
     * @see https://docs.gathercontent.com/reference#disconnecttemplate
     *
     * @return \Cheppers\GatherContent\DataTypes\Item
     */
    public function itemDisconnectTemplatePost($itemId);

    /**
     * @see https://docs.gathercontent.com/reference#duplicateitem
     *
     * @return \Cheppers\GatherContent\DataTypes\Item
     */
    public function itemDuplicatePost($itemId);

    /**
     * @see https://docs.gathercontent.com/v0.5/reference#post-item-choose_status
     */
    public function itemChooseStatusPost($itemId, $statusId);

    /**
     * @see https://docs.gathercontent.com/reference#listtemplates
     *
     * @return \Cheppers\GatherContent\DataTypes\Template[]
     */
    public function templatesGet($projectId);

    /**
     * @see https://docs.gathercontent.com/reference#gettemplate
     */
    public function templateGet($templateId);

    /**
     * @see https://docs.gathercontent.com/reference#createtemplate
     */
    public function templatePost($projectId, $name, $structure);

    /**
     * @see https://docs.gathercontent.com/reference#renametemplate
     */
    public function templateRenamePost($templateId, $name);

    /**
     * @see https://docs.gathercontent.com/reference#duplicatetemplate
     */
    public function templateDuplicatePost($templateId, $projectId = null);

    /**
     * @see https://docs.gathercontent.com/reference#deletetemplate
     */
    public function templateDelete($templateId);

    /**
     * @see https://docs.gathercontent.com/reference#get-folders
     *
     * @return \Cheppers\GatherContent\DataTypes\Folder[]
     */
    public function foldersGet($projectId);
}
