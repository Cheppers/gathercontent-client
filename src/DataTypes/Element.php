<?php

namespace Cheppers\GatherContent\DataTypes;

abstract class Element extends Base
{
    /**
     * @var array
     */
    public static $type2Class = [
        'text' => ElementText::class,
        'files' => ElementFiles::class,
        'section' => ElementSection::class,
        'choice_checkbox' => ElementOptions::class,
        'choice_radio' => ElementOptions::class,
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
    protected $unusedProperties = ['id'];

    /**
     * @return mixed
     */
    abstract public function getValue();

    /**
     * @param mixed $value
     *
     * @return $this
     */
    abstract public function setValue($value);

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
                'type' => 'type',
                'label' => 'label',
                'required' => 'required',
                'microcopy' => 'microCopy',
            ]
        );

        return $this;
    }
}
