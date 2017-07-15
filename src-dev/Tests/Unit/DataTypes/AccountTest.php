<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\Account;

class AccountTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = Account::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor(): array
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] += [
            'name' => 'a',
            'slug' => 'b',
            'timezone' => 'v',
        ];
        $cases['basic'][1] += [
            'name' => 'a',
            'slug' => 'b',
            'timezone' => 'v',
        ];

        return $cases;
    }
}
