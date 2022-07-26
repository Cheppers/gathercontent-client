<?php

namespace Cheppers\GatherContent\DataTypes;

class Item extends Base
{
    /**
     * @var int
     */
    public $projectId = 0;

    /**
     * @var string
     */
    public $folderUuid = '';

    /**
     * @var int
     */
    public $templateId = 0;

    /**
     * @var string
     */
    public $structureUuid = '';

    /**
     * @var \Cheppers\GatherContent\DataTypes\Structure
     */
    public $structure = null;

    /**
     * @var string
     */
    public $position = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var null|int
     */
    public $archivedBy = null;

    /**
     * @var string
     */
    public $archivedAt = '';

    /**
     * @var string
     */
    public $createdAt = '';

    /**
     * @var string
     */
    public $updatedAt = '';

    /**
     * @var string
     */
    public $nextDueAt = '';

    /**
     * @var string
     */
    public $completedAt = '';

    /**
     * @var \Cheppers\GatherContent\DataTypes\ElementBase[]
     */
    public $content = [];

    /**
     * @var null|int
     */
    public $statusId = null;

    /**
     * @var array
     */
    public $assignedUserIds = [];

    /**
     * @var null|int
     */
    public $assigneeCount = null;

    /**
     * @var null|int
     */
    public $approvalCount = null;

    /**
     * @var array
     */
    public $assets = [];

    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'project_id' => 'projectId',
                'folder_uuid' => 'folderUuid',
                'template_id' => 'templateId',
                'structure' => [
                    'type' => 'subConfig',
                    'class' => Structure::class,
                ],
                'structure_uuid' => 'structureUuid',
                'position' => 'position',
                'name' => 'name',
                'archived_by' => 'archivedBy',
                'archived_at' => 'archivedAt',
                'created_at' => 'createdAt',
                'updated_at' => 'updatedAt',
                'next_due_at' => 'nextDueAt',
                'completed_at' => 'completedAt',
                'status_id' => 'statusId',
                'assigned_user_ids' => 'assignedUserIds',
                'assignee_count' => 'assigneeCount',
                'approval_count' => 'approvalCount',
                'content' => 'content',
                'assets' => 'assets',
            ]
        );

        return $this;
    }
}
