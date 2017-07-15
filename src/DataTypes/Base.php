<?php

namespace Cheppers\GatherContent\DataTypes;

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
            $export[$src] = $this->{$handler['destination']};
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

                case 'callback':
                    $this->{$handler['destination']} = call_user_func($handler['callback'], $data[$src], $src);
                    break;

                case 'subConfig':
                    if (empty($handler['parents'])) {
                        $subConfig = new $handler['class']((array) $data[$src]);
                    } else {
                        $subConfigData = $this->getNestedValue($data[$src], $handler['parents']);
                        $subConfig = new $handler['class']((array) $subConfigData);
                    }

                    $this->{$handler['destination']} = $subConfig;
                    break;

                case 'subConfigs':
                    if (empty($handler['parents'])) {
                        $subConfigs = (array) $data[$src];
                    } else {
                        $subConfigs = (array) $this->getNestedValue($data[$src], $handler['parents']);
                    }

                    foreach ($subConfigs as $subConfigId => $subConfigData) {
                        $subConfig = new $handler['class']($subConfigData);
                        $this->{$handler['destination']}[$subConfig->id] = $subConfig;
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

    public function &getNestedValue(array &$array, array $parents, &$key_exists = null)
    {
        $ref = &$array;
        foreach ($parents as $parent) {
            if (is_array($ref) && array_key_exists($parent, $ref)) {
                $ref = &$ref[$parent];
            } else {
                $key_exists = false;
                $null = null;

                return $null;
            }
        }
        $key_exists = true;

        return $ref;
    }
}
