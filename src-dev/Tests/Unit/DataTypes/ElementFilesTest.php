<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\ElementFiles;

/**
 * @group GatherContentClient
 */
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
            'type' => 'file'
        ];
        $cases['basic'][1] = [
            'type' => 'file'
        ];

        return $cases;
    }
}
