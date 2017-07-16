<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementSection extends Element
{
    /**
     * @var string
     */
    public $title = '';

    /**
     * @var string
     */
    public $subtitle = '';

    /**
     * {@inheritdoc}
     */
    protected $unusedProperties = ['id', 'label', 'required', 'microcopy'];

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'title' => 'title',
                'subtitle' => 'subtitle',
            ]
        );

        return $this;
    }
}
