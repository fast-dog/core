<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 17.03.2018
 * Time: 17:39
 */

namespace FastDog\Core\Table\Filters\Logic;

use FastDog\Core\Table\Filters\BaseFilter;

/**
 * Логика группировки условий фильтрации, по умолчанию AND
 *
 * @package FastDog\Core\Table\Filters\Logic
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BaseLogic
{
    /**
     * @var string $id
     */
    protected $id = 'AND';
    /**
     * @var string $name
     */
    protected $name = 'AND';
    /**
     * @var string $description
     */
    protected $description = '';

    protected $variant = [];

    protected $default_variant = [['id' => 'AND', 'name' => 'AND'], ['id' => 'OR', 'name' => 'OR']];

    public function __construct($operator = 'AND', $name = 'AND', $description = null)
    {
        $this->setId($operator);
        $this->setName($name);
        if ($description) {
            $this->setDescription($description);
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @param BaseLogic $variant
     */
    public function pushVariant(BaseLogic $variant)
    {
        array_push($this->variant, $variant);
    }

    /**
     * @param array $variant
     * @return array
     */
    public function getLogic($variant = []): array
    {
        if ($variant == []) {
            $variant = $this->default_variant;
        }
        foreach ($variant as $item) {
            $this->pushVariant(new BaseLogic($item['id'], $item['name']));
        }

        return [
            'id' => md5(microtime(true) + rand(0, 300)),
            'type' => BaseFilter::TYPE_OPERATOR,
            'value' => $this->getId(),
            'display' => true,
            'variant' => $this->default_variant,
        ];
    }
}