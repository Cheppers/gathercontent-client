<?php

namespace Cheppers\GatherContent\DataTypes;

use Cheppers\GatherContent\DataTypes\Group;

class ElementComponentMeta extends Base
{
    /**
     * {@inheritdoc}
     */
    protected $unusedProperties = ['id'];

    /**
     * @var \Cheppers\GatherContent\DataTypes\Element[]
     */
    public $fields = [];

    /**
     * @var array
     */
    public $metaData = [];

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
                'metadata' => 'metaData',
            ]
        );

        return $this;
    }
}
