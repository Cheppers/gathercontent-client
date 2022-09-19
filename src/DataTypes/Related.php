<?php

namespace GatherContent\DataTypes;

class Related extends Base
{
    /**
     * @var \GatherContent\DataTypes\Structure
     */
    public $structure = null;

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
