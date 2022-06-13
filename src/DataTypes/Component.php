<?php

namespace Cheppers\GatherContent\DataTypes;

use Cheppers\GatherContent\DataTypes\Group;

class Component extends Base
{
    /**
     * {@inheritdoc}
     */
    protected $unusedProperties = ['id'];

    /**
     * @var int
     */
    public $projectId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var int
     */
    public $fieldCount = 0;

    /**
     * @var int
     */
    public $updatedAt = 0;

    /**
     * @var int
     */
    public $updatedBy = 0;

    /**
     * @var string
     */
    public $updatedByName = '';

    /**
     * @var int
     */
    public $createdAt = 0;

    /**
     * @var int
     */
    public $createdBy = 0;

    /**
     * @var array
     */
    public $fields = [];

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'uuid' => 'id',
                'project_id' => 'projectId',
                'name' => 'name',
                'field_count' => 'fieldCount',
                'updated_at' => 'updatedAt',
                'updated_by' => 'updatedBy',
                'updated_by_name' => 'updatedByName',
                'created_at' => 'createdAt',
                'created_by' => 'createdBy',
                'fields' => [
                    'type' => 'closure',
                    'closure' => function (array $data) {
                        $elements = [];
                        foreach ($data as $elementData) {
                            $class = Group::$type2Class[$elementData['field_type']];
                            /** @var \Cheppers\GatherContent\DataTypes\Base $element */
                            $element = new $class($elementData);
                            $elements[] = $element;
                        }

                        return $elements;
                    },
                ],
            ]
        );

        return $this;
    }
}
