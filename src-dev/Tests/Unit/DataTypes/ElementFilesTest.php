<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\ElementFiles;

class ElementFilesTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = ElementFiles::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor(): array
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] = [
            'type' => 'file',
            'userId' => 1,
            'itemId' => 2,
            'field' => 'a',
            'url' => 'b',
            'fileName' => 'c',
            'size' => 3,
            'createdAt' => 4,
            'updatedAt' => 5,
        ];
        $cases['basic'][1] = [
            'type' => 'file',
            'user_id' => 1,
            'item_id' => 2,
            'field' => 'a',
            'url' => 'b',
            'filename' => 'c',
            'size' => 3,
            'created_at' => 4,
            'updated_at' => 5,
        ];

        return $cases;
    }
}
