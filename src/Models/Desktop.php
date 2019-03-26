<?php

namespace FastDog\Core\Models;


use FastDog\Core\Scopes\UserId as UserId;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Расширение базовой модели для хранения отображаемых на главной странице блоков
 *
 * Текущая раелизация поддерживает два типа блоков, таблицу и график.
 *
 * @package FastDog\Core
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Desktop extends BaseModel
{
    /**
     * Тип блока
     *
     * Поддерживается два типа блоков: self::TYPE_GRAPH, self::TYPE_PANEL
     *
     * @const string
     */
    const TYPE = 'type';

    /**
     * Идентификатор пользователя
     *
     * @const int
     */
    const USER_ID = 'user_id';

    /**
     * Тип: график
     *
     * @const string
     */
    const TYPE_GRAPH = 'Graph';

    /**
     * Тип: таблица
     *
     * @const string
     */
    const TYPE_PANEL = 'panel';
    /**
     * Имя таблицы в базе данных
     *
     * @var string $table 'admin_desktop'
     */
    public $table = 'core_dmin_desktop';

    /**
     * Массив полей автозаполнения
     *
     * @var array $fillable [self::NAME, self::TYPE, self::DATA, self::SORT, self::USER_ID, self::SITE_ID]
     */
    public $fillable = [self::NAME, self::TYPE, self::DATA, self::SORT, self::USER_ID, self::SITE_ID];

    /**
     * "Загрузочный" метод модели
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new UserId());
    }

    /**
     * Проверка активности блока
     *
     * Если блок не существует, то будет создан
     *
     * @param string $state
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public static function check($state, $data): void
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();
        /**
         * @var $item self
         */
        $item = self::where(self::NAME, $data['name'])->withTrashed()->first();
        if ($item) {
            if ($state == 'Y') {
                $item->restore();
            } else {
                $item->delete();
            }
        } else {
            self::create([
                self::NAME => $data['name'],
                self::TYPE => $data['type'],
                self::DATA => json_encode($data['data']),
                self::USER_ID => $user->id,
                self::SITE_ID => DomainManager::getSiteId(),
            ]);
        }
    }

    /**
     * Возвращает данные блока
     *
     * В результирующем массиве содержаться доступные поля и результат работы
     * метода возвращающего набор данных для отображения графика или таблицы
     *
     * <pre>
     *  [
     *      'id' => $this->id,
     *      'alias' => md5($this->id . $this->{self::NAME}),
     *      self::NAME => $this->{self::NAME},
     *      self::DATA => $this->{self::DATA},
     *      self::TYPE => $this->{self::TYPE},
     *      'items'=> [
     *                  [1488740566000, "13"],
     *                  [1488286596000, "2"],
     *                  [1488049838000, "147"]
     *               ]
     *  ];
     * </pre>
     *
     *
     * @return array
     */
    public function getData(): array
    {
        if (is_string($this->{self::DATA})) {
            $this->{self::DATA} = json_decode($this->{self::DATA});
        }

        $result = [
            'id' => $this->id,
            'alias' => md5($this->id . $this->{self::NAME}),
            self::NAME => $this->{self::NAME},
            self::DATA => $this->{self::DATA},
            self::TYPE => $this->{self::TYPE},
        ];
        if (isset($this->{self::DATA}->{'data'})) {
            $this->{self::DATA}->{'data'} = explode('::', $this->{self::DATA}->{'data'});
            if (count((array)$this->{self::DATA}->{'data'}) === 2) {
                $instanceCls = array_first($this->{self::DATA}->{'data'});
                $method = array_last($this->{self::DATA}->{'data'});
                $instance = new $instanceCls();
                $result['items'] = $instance->$method();
            }
        }

        return $result;
    }

}