<?php

namespace Cheppers\GatherContent\DataTypes;

class Usage extends Base
{
    /**
     * @var int
     */
    public $itemCount = 0;

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        $this->propertyMapping += [
            'item_count' => 'itemCount',
        ];

        return parent::initPropertyMapping();
    }
}
