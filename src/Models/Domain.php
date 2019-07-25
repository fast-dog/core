<?php

namespace FastDog\Core\Models;


use FastDog\Core\Events\DomainsItemAdminPrepare;
use FastDog\Core\Properties\Interfases\PropertiesInterface;
use FastDog\Core\Properties\Traits\PropertiesTrait;
use FastDog\Core\Table\Filters\BaseFilter;
use FastDog\Core\Table\Filters\Operator\BaseOperator;
use FastDog\Core\Table\Interfaces\TableModelInterface;
use FastDog\Core\Traits\StateTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Определение домена
 *
 * Управление доступными доменами в режиме мультисайт
 *
 * @package FastDog\Modules\Config\Entity
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Domain extends Model implements TableModelInterface, PropertiesInterface
{
    use PropertiesTrait;

    /**
     * HTTP адрес основного домена или поддомена
     *
     * @const string
     */
    const URL = 'url';

    /**
     * Код домена
     *
     * @const string
     */
    const CODE = 'code';

    /**
     * Название сайта
     *
     * @const string
     */
    const NAME = 'name';

    /**
     * Псевдоним
     *
     * @const string
     */
    const ALIAS = 'alias';

    /**
     * Ключ принедлежности к определеному домену в режиме мультисайт:
     * 000 - общий доступ
     * 001 - главный сайт
     * ХХХ - сайт ХХХ
     *
     * @const  string
     */
    const SITE_ID = 'site_id';

    /**
     * Дополнительные данные в формате json
     * @const string
     */
    const DATA = 'data';

    /**
     * Текущее состояние модели
     *
     * Возможные значения self::STATE_PUBLISHED, self::STATE_NOT_PUBLISHED, self::STATE_IN_TRASH
     *
     * @const int
     */
    const STATE = 'state';

    /**
     * Код языка домена
     *
     * @const string
     */
    const LANG = 'lang';

    /**
     * Состояние - опубликовано
     * @const int
     */
    const STATE_PUBLISHED = 1;

    /**
     * Состояние - не опубликовано
     * @const int
     */
    const STATE_NOT_PUBLISHED = 0;

    /**
     * Состояние - в корзине
     * @const int
     */
    const STATE_IN_TRASH = 2;

    /**
     * Имя таблицы в базе данных
     *
     * @var string $table 'core_domain'
     */
    public $table = 'core_domain';

    /**
     * Массив полей автозаполнения
     *
     * @var array $fillable [self::NAME, self::URL, self::SITE_ID, self::DATA, self::CODE]
     */
    public $fillable = [self::NAME, self::URL, self::SITE_ID, self::DATA, self::CODE, self::LANG];


    use StateTrait;

    /**
     * Детальная информация по объекту
     *
     * @return array
     */
    public function getData(): array
    {
        $data = [
            'id' => $this->id,
            self::NAME => $this->{self::NAME},
            self::URL => $this->{self::URL},
            self::CODE => $this->{self::CODE},
            self::SITE_ID => $this->{self::SITE_ID},
            self::STATE => $this->{self::STATE},
            self::LANG => $this->{self::LANG},
            self::DATA => json_decode($this->{self::DATA}),
        ];

        return $data;
    }


    /**
     * Возвращает возможные состояния объекта
     *
     * @return array
     */
    public static function getStatusList(): array
    {
        return [
            ['id' => self::STATE_PUBLISHED, 'name' => trans('config::interface.state.published')],
            ['id' => self::STATE_NOT_PUBLISHED, 'name' => trans('config::interface.state.not_published')],
            ['id' => self::STATE_IN_TRASH, 'name' => trans('config::interface.state.in_trash')],
        ];
    }

    /**
     * Возвращает имя события вызываемого при обработке данных при передаче на клиент в разделе администрирования
     * @return string
     */
    public function getEventAdminPrepareName(): string
    {
        return DomainsItemAdminPrepare::class;
    }

    /**
     * Возвращает описание доступных полей для вывода в колонки...
     *
     * ... метод используется для первоначального конфигурирования таблицы,
     * дальнейшие типы, порядок колонок и т.д. будут храниться в обхекте BaseTable
     *
     * @return array
     */
    public function getTableCols(): array
    {
        return [
            [
                'name' => trans('config::forms.domain.general.name'),
                'key' => Domain::NAME,
                'domain' => true,
                'callback' => false,
                'link' => 'domain_item',
            ],
            [
                'name' => trans('config::forms.domain.general.url'),
                'key' => Domain::URL,
                'link' => null,
                'domain' => false,
            ],
            [
                'name' => trans('config::forms.domain.general.code') ,
                'key' => Domain::CODE,
                'domain' => false,
                'link' => null,
                'width' => 100,
                'class' => 'text-center',
            ],
            [
                'name' => '#',
                'key' => 'id',
                'link' => null,
                'width' => 80,
                'class' => 'text-center',
            ],
        ];
    }

    /**
     * Определение фильтров таблицы в виде массива
     *
     * @return array
     */
    public function getAdminFilters(): array
    {
        $default = [
            [
                [
                    BaseFilter::NAME => Domain::NAME,
                    BaseFilter::PLACEHOLDER => trans('config::forms.domain.general.name'),
                    BaseFilter::TYPE => BaseFilter::TYPE_TEXT,
                    BaseFilter::DISPLAY => true,
                    BaseFilter::OPERATOR => (new BaseOperator('LIKE', 'LIKE'))->getOperator(),
                ],
            ],
        ];

        return $default;
    }

    /**
     * Коллекция дополнительных параметров модели по умолчанию
     *
     * @return Collection
     */
    public function getDefaultProperties(): Collection
    {
        $result = [];

        return collect($result);
    }

}
