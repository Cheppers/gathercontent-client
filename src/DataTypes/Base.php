<?php

namespace Cheppers\GatherContent\DataTypes;

use Cheppers\GatherContent\Utils\NestedArray;
use JsonSerializable;

class Base implements JsonSerializable
{
    /**
     * @var string
     */
    public $id = '';

    /**
     * @var array
     */
    protected $propertyMapping = [];

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $dataDefaultValues = [];

    protected $unusedProperties = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this
            ->initPropertyMapping()
            ->expandPropertyMappingShortCuts()
            ->populateProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $export = [];

        foreach ($this->propertyMapping as $src => $handler) {
            if (in_array($src, $this->unusedProperties)) {
                continue;
            }

            $value = $this->{$handler['destination']};

            if ($handler['type'] === 'setJsonDecode') {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            }

            if (!empty($handler['parents'])) {
                while ($handler['parents']) {
                    $value = [
                        array_pop($handler['parents']) => $value,
                    ];
                }
            }

            $export[$src] = $value;
        }

        return $export;
    }

    /**
     * @return $this
     */
    protected function initPropertyMapping()
    {
        $this->propertyMapping += [
            'id' => 'id',
        ];

        return $this;
    }

    /**
     * @return $this
     */
    protected function expandPropertyMappingShortCuts()
    {
        foreach ($this->propertyMapping as $src => $handler) {
            if (is_string($handler)) {
                $handler = [
                    'type' => 'set',
                    'destination' => $handler,
                ];
            }

            if ($handler['type'] === 'subConfig' || $handler['type'] === 'subConfigs') {
                $handler += ['parents' => []];
            }

            $this->propertyMapping[$src] = $handler + ['destination' => $src];
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function populateProperties()
    {
        $data = $this->setDataDefaultValues($this->data);
        foreach ($this->propertyMapping as $src => $handler) {
            if (!array_key_exists($src, $data)) {
                continue;
            }

            switch ($handler['type']) {
                case 'set':
                    $this->{$handler['destination']} = $data[$src];
                    break;
                case 'setJsonDecode':
                    $this->{$handler['destination']} = json_decode($data[$src], true);
                    break;

                case 'closure':
                    $this->{$handler['destination']} = $handler['closure']($data[$src], $src);
                    break;

                case 'subConfig':
                    /** @var \Cheppers\GatherContent\DataTypes\Base $subConfig */
                    if (empty($handler['parents'])) {
                        $subConfig = new $handler['class']((array) $data[$src]);
                    } else {
                        $subConfigData = NestedArray::getValue($data[$src], $handler['parents']);
                        $subConfig = new $handler['class']((array) $subConfigData);
                    }

                    $this->{$handler['destination']} = $subConfig;
                    break;

                case 'subConfigs':
                    if (empty($handler['parents'])) {
                        $subConfigs = (array) $data[$src];
                    } else {
                        $subConfigs = (array) NestedArray::getValue($data[$src], $handler['parents']);
                    }

                    foreach ($subConfigs as $subConfigId => $subConfigData) {
                        $subConfig = new $handler['class']($subConfigData);
                        $id = $subConfig->id ?: $subConfigId;
                        $this->{$handler['destination']}[$id] = $subConfig;
                    }
                    break;
            }
        }

        return $this;
    }

    protected function setDataDefaultValues(array $data): array
    {
        return array_replace_recursive($this->dataDefaultValues, $data);
    }
}
