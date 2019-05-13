<?php

namespace FastDog\Core\Table;


use FastDog\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Таблицы раздела администрирования
 *
 * @package FastDog\Core\Table
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BaseTable extends Model
{
    use SoftDeletes;

    /**
     * @const string
     */
    const NAME = 'name';

    /**
     * @const string
     */
    const DATA = 'data';

    /**
     * @var string $table
     */
    public $table = 'system_table';

    /**
     * @var array $dates
     */
    public $dates = [BaseModel::DELETED_AT];

    /**
     * @var array $fillable
     */
    public $fillable = [self::NAME, self::DATA];

    /**
     * @return array
     */
    public function getData()
    {
        return [
            self::NAME => $this->{self::NAME},
            self::DATA => (is_string($this->{self::DATA})) ? json_decode($this->{self::DATA}) : $this->{self::DATA},
        ];
    }

    /**
     * @return Collection
     */
    public function getCols(): Collection
    {
        $data = $this->getData();

        return collect($data[self::DATA]->cols);
    }
}