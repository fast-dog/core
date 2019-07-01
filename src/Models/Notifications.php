<?php

namespace FastDog\Core\Models;

use Carbon\Carbon as Carbon;
use FastDog\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;


/**
 * Уведомления в разделе администрирования
 *
 * Реализация системы уведомлений о происходящих в автоматическом режиме событиях
 *
 * @package FastDog\Core
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Notifications extends BaseModel
{
    /**
     * Идентификатор пользователя
     *
     * @const int
     */
    const USER_ID = 'user_id';
    /**
     * Прочитано или нет
     * @const int
     */
    const READ = 'read';

    /**
     * Тип сообщения
     * @const int
     */
    const TYPE = 'type';
    /**
     * Событие не определено
     * @const int
     */
    const TYPE_UNDEFINED = 0;
    /**
     * Изменен маршрут пункта меню
     * @const int
     */
    const TYPE_CHANGE_ROUTE = 1;
    /**
     * Пункт меню отключен
     * @const int
     */
    const TYPE_MENU_DISABLED = 2;
    /**
     * Создан поисковый индекс
     * @const int
     */
    const TYPE_CREATE_SEARCH_INDEX = 3;
    /**
     * Обновлен поисковый индекс
     * @const int
     */
    const TYPE_UPDATE_SEARCH_INDEX = 4;
    /**
     * Обновлена каноническая ссылка
     * @const int
     */
    const TYPE_UPDATE_CANONICAL_LINK = 5;
    /**
     * Товар поступил на склад
     * @const int
     */
    const TYPE_CATALOG_ITEM_IN_STORE = 6;

    /**
     * @var string $table 'system_notifications'
     */
    public $table = 'system_notifications';

    /**
     * Массив полей автозаполнения
     *
     * @var array $fillable [self::USER_ID, self::READ, self::DATA, self::TYPE]
     */
    public $fillable = [self::USER_ID, self::READ, self::DATA, self::TYPE];

    /**
     * Последние сообщения
     *
     * Возвращает пять последних вообщений
     *
     * @return array
     */
    public static function getNew()
    {
        $result = ['total' => 0, 'items' => []];
        /**
         * @var  $user User
         */
        $user = \Auth::getUser();
        if (config('app.install') === true && $user) {

            /** @var $items Collection */
            $items = self::where(function (Builder $query) use ($user) {
                $query->where(self::USER_ID, $user->id);
            })->orderBy('created_at', 'desc')->paginate(5);

            $result['total'] = $items->total();

            $items->each(function (self $item) use (&$result) {
                array_push($result['items'], $item->getData());
            });
        }

        return $result;
    }

    /**
     * Возвращает данные модели
     *
     * <pre>
     *      [
     *          'id' => $this->id,
     *          'created_at_diff' => $this->created_at->diffForHumans(),
     *          'created_at' => $this->created_at->format('d.m.y H:i'),
     *          'message' => $this->getMessage(),
     *          'icon' => url('/upload/images/.tmb/system.png'),
     *      ];
     * </pre>
     * @return array
     */
    public function getData(): array
    {
        Carbon::setLocale('ru');
        $result = [
            'id' => $this->id,
            'created_at_diff' => $this->created_at->diffForHumans(),
            'created_at' => $this->created_at->format('d.m.y H:i'),
            'message' => $this->getMessage(),
            'icon' => url('/upload/images/.tmb/system.png'),
        ];

        return $result;
    }

    /**
     * Возвращает текст сообщения
     *
     * @return string
     */
    public function getMessage()
    {
        $message = [];

        if (is_string($this->{self::DATA})) {
            $this->{self::DATA} = json_decode($this->{self::DATA});
        }
        switch ($this->{self::TYPE}) {
            case self::TYPE_CHANGE_ROUTE:
                array_push($message, $this->{self::DATA}->message);
                break;
            default:
                array_push($message, $this->{self::DATA}->message);
                break;
        }

        return implode('<br />', $message);
    }

    /**
     * Добавляет новое сообщение
     *
     * Обязательным параметром является тип сообщения
     *
     * <pre>
     *  Notifications::add([
     *      Notifications::TYPE => Notifications::TYPE_MENU_DISABLED,
     *      'message' => 'Текс сообщения',
     * ]);
     * </pre>
     *
     * @param array $params массив параметров
     * @return Notifications
     */
    public static function add($params)
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();
        if ($user) {
            $params['message'] = str_replace('{ADMIN}', config('app.admin_path'), $params['message']);

            return Notifications::create([
                Notifications::TYPE => $params['type'],
                Notifications::USER_ID => $user->id,
                Notifications::DATA => json_encode([
                    'message' => $params['message'],
                ]),
            ]);
        }

    }

}