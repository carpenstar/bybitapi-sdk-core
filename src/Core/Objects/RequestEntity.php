<?php
namespace Carpenstar\ByBitAPI\Core\Objects;

use Carpenstar\ByBitAPI\Core\Interfaces\IRequestInterface;
use Carpenstar\ByBitAPI\Spot\Trade\GetOrder\GOQueryBag;

abstract class RequestEntity implements IRequestInterface
{
    protected array $requiredFields = [];

    protected array $requiredBetweenFields = [];

    /**
     * @return array
     */
    public function fetchArray(): array
    {
        $entity = $this;
        $entityMethods = get_class_methods($this);
        $params = [];

        array_walk($entityMethods, function ($method) use (&$entity, &$params) {
            if (substr($method, 0, 3) == 'get') {
                $entityProperty = lcfirst(substr($method, 3));
                if (isset($entity->$entityProperty)) {
                    $params[$entityProperty] = (string)$entity->$method();

                    $propIndex = array_search($entityProperty, $entity->requiredFields, true);
                    if($propIndex > -1 && !empty($params[$entityProperty])) {
                        unset($entity->requiredFields[$propIndex]);
                    }


                    if (!empty($entity->requiredBetweenFields)) {
                        foreach ($entity->requiredBetweenFields as $index => $condition) {
                            if (in_array($entityProperty, $condition)) {
                                unset($entity->requiredBetweenFields[$index]);
                                break;
                            }
                        }
                    }
                }
            }
        });

        if (!empty($this->requiredFields)) {
            throw new \Exception("Необходимо указать следующие параметры запроса: " . implode(',', $this->requiredFields));
        }

        if (!empty($this->requiredBetweenFields)) {
            $paramsString = '';
            foreach ($this->requiredBetweenFields as $fieldArray) {
                $paramsString .= implode(' or ', $fieldArray);
            }
            $params = $paramsString;

            throw new \Exception("Необходимо указать один из двух параметров {$paramsString}");
        }
        return $params;
    }

    protected function setRequiredField(string $fieldName): self
    {
        $this->requiredFields[] = $fieldName;
        return $this;
    }

    protected function setRequiredBetweenField(string $fieldOne, string $fieldTwo): self
    {
        $this->requiredBetweenFields[] = [$fieldOne, $fieldTwo];
        return $this;
    }
}