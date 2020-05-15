<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\Tab;

/**
 * @group GatherContentClient
 */
class TabTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = Tab::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] = [
            'label' => 'tab-uuid'
        ];
        $cases['basic'][1] = [
            'label' => 'tab-uuid'
        ];

        return $cases;
    }

    public function testJsonSerialize()
    {
        $itemArray = static::getUniqueResponseTemplateTab([
            'text', 'files', 'choice_radio', 'choice_checkbox'
        ]);

        $item1 = new $this->className($itemArray);

        $item1->label .= '-MODIFIED';
        $itemArray['label'] .= '-MODIFIED';

        $json1 = json_encode($item1);
        $actual1 = json_decode($json1, true);

        static::assertEquals(
            \GuzzleHttp\json_encode($item1, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual1, JSON_PRETTY_PRINT)
        );

        /** @var \Cheppers\GatherContent\DataTypes\Item $item2 */
        $item2 = new $this->className($actual1);
        $json2 = json_encode($item2);
        $actual2 = json_decode($json2, true);
        static::assertEquals($actual1, $actual2);
    }
}
