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
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'item_count' => 'itemCount',
            ]
        );

        return $this;
    }
}
