<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\Item;

/**
 * @group GatherContentClient
 */
class ItemTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = Item::class;

    public function testJsonSerialize(): void
    {
        $pairs = [
            'project_id' => [1, 1],
            'parent_id' => [2, 2],
            'template_id' => [3, 3],
            'custom_state_id' => [4, 4],
            'position' => ['a', 'a'],
            'name' => ['b', 'b'],
            'notes' => ['c', 'c'],
            'type' => ['e', 'e'],
            'overdue' => [true, true],
            'archived_by' => [5, 5],
            'archived_at' => [6, 6],
            'config' => [
                [
                    'tab-01-id' => [
                        'name' => 'tab-01-id',
                        'label' => 'tab-01-label',
                        'hidden' => false,
                        'elements' => [
                            'element-01-id' => [
                                'name' => 'element-01-id',
                                'type' => 'text',
                                'label' => 'element-01-label',
                                'required' => true,
                                'microcopy' => '',
                                'limit_type' => 'element-01-limit_type',
                                'limit' => 42,
                                'plain_text' => true,
                                'value' => 'element-01-value',
                            ],
                            'element-02-id' => [
                                'name' => 'element-02-id',
                                'type' => 'files',
                                'label' => 'element-02-label',
                                'required' => false,
                                'microcopy' => '',
                                'user_id' => 43,
                                'item_id' => 44,
                                'field' => 'element-02-field',
                                'url' => 'element-02-url',
                                'filename' => 'element-02-fileName',
                                'size' => 45,
                                'created_at' => 'element-02-createdAt',
                                'updated_at' => 'element-02-updatedAt',
                            ],
                        ],
                    ],
                ],
                [
                    [
                        'name' => 'tab-01-id',
                        'label' => 'tab-01-label',
                        'hidden' => false,
                        'elements' => [
                            [
                                'name' => 'element-01-id',
                                'type' => 'text',
                                'label' => 'element-01-label',
                                'required' => true,
                                'microcopy' => '',
                                'limit_type' => 'element-01-limit_type',
                                'limit' => 42,
                                'plain_text' => true,
                                'value' => 'element-01-value',
                            ],
                            [
                                'name' => 'element-02-id',
                                'type' => 'files',
                                'label' => 'element-02-label',
                                'required' => false,
                                'microcopy' => '',
                                'user_id' => 43,
                                'item_id' => 44,
                                'field' => 'element-02-field',
                                'url' => 'element-02-url',
                                'filename' => 'element-02-fileName',
                                'size' => 45,
                                'created_at' => 'element-02-createdAt',
                                'updated_at' => 'element-02-updatedAt',
                            ],
                        ],
                    ],
                ],
            ],
            'created_at' => [
                [
                    'date' => 'g',
                    'timezone_type' => 7,
                    'timezone' => 'h',
                ],
                [
                    'date' => 'g',
                    'timezone_type' => 7,
                    'timezone' => 'h',
                ],
            ],
            'updated_at' => [
                [
                    'date' => 'i',
                    'timezone_type' => 8,
                    'timezone' => 'j',
                ],
                [
                    'date' => 'i',
                    'timezone_type' => 8,
                    'timezone' => 'j',
                ],
            ],
            'status' => [
                [
                    'data' => [
                        'id' => 'status-01-id',
                        'is_default' => 'status-01-is_default',
                        'position' => 'status-01-position',
                        'color' => 'status-01-color',
                        'name' => 'status-01-name',
                        'description' => 'status-01-description',
                        'can_edit' => 'status-01-can_edit',
                    ],
                ],
                [
                    'data' => [
                        'id' => 'status-01-id',
                        'is_default' => 'status-01-is_default',
                        'position' => 'status-01-position',
                        'color' => 'status-01-color',
                        'name' => 'status-01-name',
                        'description' => 'status-01-description',
                        'can_edit' => 'status-01-can_edit',
                    ],
                ],
            ],
            'due_dates' => [
                [
                    'data' => [
                        [
                            'date' => 'a',
                            'timezone_type' => 1,
                            'timezone' => 'b',
                        ],
                        [
                            'date' => 'c',
                            'timezone_type' => 2,
                            'timezone' => 'd',
                        ],
                    ],
                ],
                [
                    'data' => [
                        [
                            'date' => 'a',
                            'timezone_type' => 1,
                            'timezone' => 'b',
                        ],
                        [
                            'date' => 'c',
                            'timezone_type' => 2,
                            'timezone' => 'd',
                        ],
                    ],
                ],
            ],
        ];

        $expected = [];
        $data = [];
        foreach ($pairs as $key => $pair) {
            $expected[$key] = $pair[0];
            $data[$key] = $pair[1];
        }
        /** @var \Cheppers\GatherContent\DataTypes\Item $item1 */
        $item1 = new $this->className($data);

        $item1->name .= '-MODIFIED';
        $expected['name'] .= '-MODIFIED';

        $item1->status->color .= '-MODIFIED';
        $expected['status']['data']['color'] .= '-MODIFIED';

        $item1->config['tab-01-id']->elements['element-01-id']->label .= '-MODIFIED';
        $expected['config']['tab-01-id']['elements']['element-01-id']['label'] .= '-MODIFIED';

        $json1 = json_encode($item1);
        $actual1 = json_decode($json1, true);
        foreach ($expected as $key => $value) {
            static::assertEquals($value, $actual1[$key], "JSON encode.decode - $key");
        }

        /** @var \Cheppers\GatherContent\DataTypes\Item $item2 */
        $item2 = new $this->className($actual1);
        $json2 = json_encode($item2);
        $actual2 = json_decode($json2, true);
        static::assertEquals($actual1, $actual2);
    }
}
