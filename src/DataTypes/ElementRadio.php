<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementRadio extends ElementChoice
{
    /**
     * @var bool
     */
    public $otherOption = false;

    /**
     * {@inheritdoc}
     */
    public $type = 'choice_radio';
}
