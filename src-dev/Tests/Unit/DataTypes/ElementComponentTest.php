<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\ElementComponent;
use Cheppers\GatherContent\DataTypes\ElementComponentMeta;

/**
 * @group GatherContentClient
 */
class ElementComponentTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = ElementComponent::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] = [
            'id' => 'uuid-123',
            'type' => 'component',
            'label' => 'label string',
            'instructions' => 'instruction string',
            'component' => new ElementComponentMeta([
                'uuid' => 'uuid-234',
                'fields' => [
                    [
                        'uuid' => 'uuid-345',
                        'label' => 'label string',
                        'instructions' => 'instruction string',
                        'field_type' => 'text',
                    ]
                ],
            ]),
        ];
        $cases['basic'][1] = [
            'uuid' => 'uuid-123',
            'field_type' => 'component',
            'label' => 'label string',
            'instructions' => 'instruction string',
            'component' => [
                'uuid' => 'uuid-234',
                'fields' => [
                    [
                        'uuid' => 'uuid-345',
                        'label' => 'label string',
                        'instructions' => 'instruction string',
                        'field_type' => 'text',
                    ]
                ],
            ],
        ];

        return $cases;
    }
}
