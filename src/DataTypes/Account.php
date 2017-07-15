<?php

namespace Cheppers\GatherContent\DataTypes;

class Account extends Base
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $slug = '';

    /**
     * @var string
     */
    public $timezone = '';

    protected function initPropertyMapping()
    {
        $this->propertyMapping += [
            'name' => 'name',
            'slug' => 'slug',
            'timezone' => 'timezone',
        ];

        return parent::initPropertyMapping();
    }
}
