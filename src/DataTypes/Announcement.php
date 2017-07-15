<?php

namespace Cheppers\GatherContent\DataTypes;

class Announcement extends Base
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var bool
     */
    public $acknowledged = false;

    protected function initPropertyMapping()
    {
        $this->propertyMapping += [
            'name' => 'name',
            'acknowledged' => 'acknowledged',
        ];

        return parent::initPropertyMapping();
    }
}
