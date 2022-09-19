<?php

namespace GatherContent\DataTypes;

class Structure extends Base
{
    /**
     * @var \GatherContent\DataTypes\Group[]
     */
    public $groups = [];

    /**
     * {@inheritdoc}
     */
    protected $unusedProperties = ['id'];

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
                'groups' => [
                    'type' => 'subConfigs',
                    'class' => Group::class,
                ],
            ]
        );

        return $this;
    }
}
