<?php

namespace Cheppers\GatherContent;

use GuzzleHttp\ClientInterface;

interface GatherContentClientInterface
{
    const PROJECT_TYPE_WEBSITE_BUILDING = 'website-build';

    const PROJECT_TYPE_ONGOING_WEBSITE_CONTENT = 'ongoing-website-content';

    const PROJECT_TYPE_MARKETING_EDITORIAL_CONTENT = 'marketing-editorial-content';

    const PROJECT_TYPE_EMAIL_MARKETING_CONTENT = 'email-marketing-content';

    const PROJECT_TYPE_OTHER = 'other';

    /**
     * GatherContentClientInterface constructor.
     */
    public function __construct(ClientInterface $client);

    /**
     * @return string[]
     */
    public function projectTypes(): array;

    /**
     * @see https://docs.gathercontent.com/reference#get-me
     */
    public function meGet(): ?DataTypes\User;

    /**
     * @see https://docs.gathercontent.com/reference#get-accounts
     *
     * @return \Cheppers\GatherContent\DataTypes\Account[]
     */
    public function accountsGet(): array;

    /**
     * @see https://docs.gathercontent.com/reference#get-accounts
     */
    public function accountGet(int $accountId): ?DataTypes\Account;

    /**
     * @see https://docs.gathercontent.com/reference#get-projects
     *
     * @return \Cheppers\GatherContent\DataTypes\Project[]
     */
    public function projectsGet(int $accountId): array;

    /**
     * @see https://docs.gathercontent.com/reference#get-projects
     */
    public function projectGet(int $projectId): ?DataTypes\Project;

    /**
     * @see https://docs.gathercontent.com/reference#post-projects
     *
     * @return int
     *   Id of the newly created project.
     */
    public function projectsPost(int $accountId, string $projectName, string $projectType): int;

    /**
     * @see https://docs.gathercontent.com/reference#get-project-statuses
     *
     * @return \Cheppers\GatherContent\DataTypes\Status[]
     */
    public function projectStatusesGet(int $projectId): array;

    /**
     * @see https://docs.gathercontent.com/reference#get-project-statuses-by-id
     */
    public function projectStatusGet(int $projectId, int $statusId): ?DataTypes\Status;

    /**
     * @see https://docs.gathercontent.com/reference#get-items
     *
     * @return \Cheppers\GatherContent\DataTypes\Item[]
     */
    public function itemsGet(int $projectId): array;

    /**
     * @see https://docs.gathercontent.com/reference#get-items-by-id
     */
    public function itemGet(int $itemId): ?DataTypes\Item;

    /**
     * @see https://docs.gathercontent.com/reference#post-items
     */
    public function itemsPost(
        int $projectId,
        string $name,
        int $parentId = 0,
        int $templateId = 0,
        array $config = []
    ): int;

    /**
     * @see https://docs.gathercontent.com/reference#post-item-save
     */
    public function itemSavePost(int $itemId, array $config): void;

    /**
     * @return \Cheppers\GatherContent\DataTypes\File[]
     */
    public function itemFilesGet(int $itemId): array;

    /**
     * @see https://docs.gathercontent.com/reference#post-item-apply_template
     */
    public function itemApplyTemplatePost(int $itemId, int $templateId): void;

    /**
     * @see https://docs.gathercontent.com/reference#post-item-choose_status
     */
    public function itemChooseStatusPost(int $itemId, int $statusId): void;

    /**
     * @see https://docs.gathercontent.com/reference#get-templates
     *
     * @return \Cheppers\GatherContent\DataTypes\Template[]
     */
    public function templatesGet(int $projectId): array;

    /**
     * @see https://docs.gathercontent.com/reference#get-template-by-id
     */
    public function templateGet(int $templateId): ?DataTypes\Template;
}
