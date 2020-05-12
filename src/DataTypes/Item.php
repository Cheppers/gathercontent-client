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
     * @var \Cheppers\GatherContent\DataTypes\Element[]
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

    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'project_id' => 'projectId',
                'folder_uuid' => 'folderUuid',
                'template_id' => 'templateId',
                'structure_uuid' => 'structureUuid',
                'position' => 'position',
                'name' => 'name',
                'archived_by' => 'archivedBy',
                'archived_at' => 'archivedAt',
                'created_at' => 'createdAt',
                'updated_at' => 'updatedAt',
                'next_due_at' => 'nextDueAt',
                'completed_at' => 'completedAt',
                'content' => [
                    'type' => 'closure',
                    'closure' => function (array $data) {
                        $elements = [];
                        foreach ($data as $key => $elementData) {
                            $class = Element::$type2Class[$elementData['type']];
                            /** @var \Cheppers\GatherContent\DataTypes\Element $element */
                            $element = new $class($elementData);
                            $elements[$key] = $element;
                        }

                        return $elements;
                    },
                ],
                'status_id' => 'statusId',
                'assigned_user_ids' => 'assignedUserIds',
                'assignee_count' => 'assigneeCount',
                'approval_count' => 'approvalCount',
            ]
        );

        return $this;
    }
}
