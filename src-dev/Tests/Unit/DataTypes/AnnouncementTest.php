<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\Announcement;

class AnnouncementTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = Announcement::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor(): array
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] += [
            'name' => 'a',
            'acknowledged' => 'b',
        ];
        $cases['basic'][1] += [
            'name' => 'a',
            'acknowledged' => 'b',
        ];

        return $cases;
    }
}
