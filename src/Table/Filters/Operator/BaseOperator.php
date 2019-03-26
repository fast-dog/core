<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 17.03.2018
 * Time: 18:09
 */

namespace FastDog\Core\Table\Filters\Operator;

/**
 * Операторы условий фильтра
 *
 * @package FastDog\Core\Table\Filters\Operator
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BaseOperator
{
    /**
     * @var string $id
     */
    protected $id = '';

    /**
     * @var string $name
     */
    protected $name = '';

    /**
     * @var string $description
     */
    protected $description = '';

    /**
     * Варианты оператора
     *
     * @var array $variant
     */
    protected $variant = [];
    /**
     * Варианты оператора по умолчанию
     *
     * @var array $default_variant
     */
    protected $default_variant = [['id' => '=', 'name' => '='], ['id' => '!=', 'name' => '!=']];

    /**
     * BaseOperator constructor.
     * @param string $operator
     * @param string $name
     * @param null $description
     */
    public function __construct($operator = '=', $name = '=', $description = null)
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
     * @return array
     */
    public function getVariant(): array
    {
        return $this->variant;
    }

    /**
     * @param array $variant
     */
    public function setVariant(array $variant)
    {
        $this->variant = $variant;
    }

    /**
     * @param BaseOperator $variant
     */
    public function pushVariant(BaseOperator $variant)
    {
        array_push($this->variant, $variant);
    }

    /**
     * Возвращает текущий условный\логический оператор
     *
     * @param array $variant варианты операторов ['id' => '=', 'name' => '='], ['id' => '!=', 'name' => '!=']
     * @return array
     */
    public function getOperator($variant = []): array
    {
        if ($variant == []) {
            $variant = $this->default_variant;
        }
        foreach ($variant as $item) {
            $this->pushVariant(new BaseOperator($item['id'], $item['name']));
        }

        return [
            'value' => $this->getId(),
            'variant' => $this->getVariant(),
        ];
    }
}