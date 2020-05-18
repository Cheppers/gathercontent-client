<?php

namespace Cheppers\GatherContent\DataTypes;

class Element extends ElementBase
{
    /**
     * {@inheritdoc}
     */
    protected $unusedProperties = ['id'];

    /**
     * @var array
     */
    public static $type2Class = [
        'text' => ElementText::class,
        'attachment' => ElementFiles::class,
        'guidelines' => ElementSection::class,
        'choice_checkbox' => ElementCheckbox::class,
        'choice_radio' => ElementRadio::class,
    ];

    /**
     * @see https://docs.gathercontent.com/reference#tab-structure
     *
     * @var string
     */
    public $type = '';

    /**
     * @var bool
     */
    public $required = false;

    /**
     * @var string
     */
    public $label = '';

    /**
     * @var string
     */
    public $microCopy = '';

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'name' => 'id',
                'field_type' => 'type',
                'label' => 'label',
                'required' => 'required',
                'microcopy' => 'microCopy',
            ]
        );

        return $this;
    }
}
