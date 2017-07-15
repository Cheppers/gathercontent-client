<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementOptions extends Element
{
    /**
     * @var array
     */
    public $options = [];

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        $this->propertyMapping += [
            'options' => '',
        ];

        return parent::initPropertyMapping();
    }
}
