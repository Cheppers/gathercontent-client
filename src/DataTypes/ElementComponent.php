<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementComponent extends Element
{
    /**
     * {@inheritdoc}
     */
    public $type = 'component';

    /**
     * @var array
     */
    public $component = [];

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'component' => [
                    'type' => 'subConfig',
                    'class' => ElementComponentMeta::class,
                    'destination' => 'component',
                ],
            ]
        );

        return $this;
    }
}
