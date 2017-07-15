<?php

namespace Cheppers\GatherContent\DataTypes;

class Status extends Base
{
    /**
     * @var bool
     */
    public $isDefault = false;

    /**
     * @var string
     */
    public $position = '';

    /**
     * @var string
     */
    public $color = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var bool
     */
    public $canEdit = true;

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        $this->propertyMapping += [
            'is_default' => 'isDefault',
            'position' => 'position',
            'color' => 'color',
            'name' => 'name',
            'description' => 'description',
            'can_edit' => 'canEdit',
        ];

        return parent::initPropertyMapping();
    }
}
