<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementFiles extends Element
{
    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return $this;
    }
}
