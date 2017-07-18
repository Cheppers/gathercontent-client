<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\Project;

/**
 * @group GatherContentClient
 */
class ProjectTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = Project::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor(): array
    {
        $cases = parent::casesConstructor();

        $cases['empty'][0] += [
            'name' => '',
            'type' => '',
            'example' => false,
            'accountId' => 0,
            'active' => true,
            'textDirection' => '',
            'allowedTags' => [],
            'createdAt' => 0,
            'updatedAt' => 0,
            'overdue' => false,
            'statuses' => [],
            'meta' => [],
        ];

        $allowedTags = [
            'a' => [],
            'p' => ['class' => '*'],
        ];

        $cases['basic'][0] += [
            'name' => 'project-name',
            'type' => 'project-type',
            'example' => false,
            'accountId' => 42,
            'active' => true,
            'textDirection' => 'ltr',
            'allowedTags' => $allowedTags,
            'createdAt' => 43,
            'updatedAt' => 44,
            'overdue' => true,
            'meta' => [],
        ];
        $cases['basic'][1] += [
            'name' => 'project-name',
            'type' => 'project-type',
            'example' => false,
            'account_id' => 42,
            'active' => true,
            'text_direction' => 'ltr',
            'allowed_tags' => json_encode($allowedTags),
            'created_at' => 43,
            'updated_at' => 44,
            'overdue' => true,
            'meta' => [],
        ];

        return $cases;
    }

    public function testJsonSerialize(): void
    {
        $allowedTags = [
            'a' => [],
            'p' => ['class' => '*'],
        ];

        $allowedTagsJson = json_encode($allowedTags, JSON_PRETTY_PRINT);

        $pairs = [
            'id' => ['a', 'a'],
            'name' => ['b', 'b'],
            'type' => ['c', 'c'],
            'example' => [false, false],
            'account_id' => [1, 1],
            'active' => [true, true],
            'text_direction' => ['d', 'd'],
            'allowed_tags' => [$allowedTagsJson, $allowedTagsJson],
            'created_at' => [2, 2],
            'updated_at' => [3, 3],
            'overdue' => [true, true],
            'statuses' => [
                [
                    'data' => [
                        [
                            'id' => 'status-01-id',
                            'is_default' => true,
                            'position' => 'p1',
                            'color' => 'c1',
                            'name' => 'n1',
                            'description' => 'd1',
                            'can_edit' => true,
                        ],
                        [
                            'id' => 'status-02-id',
                            'is_default' => true,
                            'position' => 'p2',
                            'color' => 'c2',
                            'name' => 'n2',
                            'description' => 'd2',
                            'can_edit' => false,
                        ],
                    ],
                ],
                [
                    'data' => [
                        [
                            'id' => 'status-01-id',
                            'is_default' => true,
                            'position' => 'p1',
                            'color' => 'c1',
                            'name' => 'n1',
                            'description' => 'd1',
                            'can_edit' => true,
                        ],
                        [
                            'id' => 'status-02-id',
                            'is_default' => true,
                            'position' => 'p2',
                            'color' => 'c2',
                            'name' => 'n2',
                            'description' => 'd2',
                            'can_edit' => false,
                        ],
                    ],
                ],
            ],
            'meta' => [[], []],
        ];

        $expected = [];
        $data = [];
        foreach ($pairs as $key => $pair) {
            $expected[$key] = $pair[0];
            $data[$key] = $pair[1];
        }
        /** @var \Cheppers\GatherContent\DataTypes\Project $project1 */
        $project1 = new $this->className($data);

        static::assertEquals($allowedTags, $project1->allowedTags);

        $project1->name .= '-MODIFIED';
        $expected['name'] .= '-MODIFIED';

        $project1->textDirection .= '-MODIFIED';
        $expected['text_direction'] .= '-MODIFIED';

        $project1->statuses['status-01-id']->color .= '-MODIFIED';
        $expected['statuses']['data'][0]['color'] .= '-MODIFIED';

        $json1 = json_encode($project1);
        $actual1 = json_decode($json1, true);
        foreach ($expected as $key => $value) {
            static::assertEquals($value, $actual1[$key], "JSON encode.decode - $key");
        }

        /** @var \Cheppers\GatherContent\DataTypes\Project $project2 */
        $project2 = new $this->className($actual1);
        $json2 = json_encode($project2);
        $actual2 = json_decode($json2, true);
        static::assertEquals($actual1, $actual2);
    }
}
