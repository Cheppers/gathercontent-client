<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementFiles extends Element
{
    /**
     * @var null|int
     */
    public $userId = null;

    /**
     * @var null|int
     */
    public $itemId = null;

    /**
     * @var null|string
     */
    public $field = null;

    /**
     * @var null|string
     */
    public $url = null;

    /**
     * @var null|string
     */
    public $fileName = null;

    /**
     * @var null|int
     */
    public $size = null;

    /**
     * @var null|string
     */
    public $createdAt = null;

    /**
     * @var null|string
     */
    public $updatedAt = null;

    /**
     * {@inheritdoc}
     */
    public $type = 'files';

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

    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'user_id' => 'userId',
                'item_id' => 'itemId',
                'field' => 'field',
                'url' => 'url',
                'filename' => 'fileName',
                'size' => 'size',
                'created_at' => 'createdAt',
                'updated_at' => 'updatedAt',
            ]
        );

        return $this;
    }
}
