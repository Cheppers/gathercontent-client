<?php

namespace Cheppers\GatherContent\DataTypes;

class Related extends Base
{
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
                'structure' => [
                    'type' => 'subConfig',
                    'class' => Structure::class,
                ],
            ]
        );

        return $this;
    }
}
