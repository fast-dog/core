<?php

namespace FastDog\Core\Form;

use FastDog\Core\Models\BaseModel;

/**
 * Формы раздела администрирования
 *
 * @package FastDog\Core\Form
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BaseForm extends BaseModel
{
    /**
     * Модель к которой принадлежит параметр
     * @cont string
     */
    const MODEL = 'model';

    /**
     * Идентификатор пользователя
     * cont string
     */
    const USER_ID = 'user_id';

    /**
     * @var string $table
     */
    public $table = 'system_forms';

    /**
     * @var array $fillable
     */
    public $fillable = [self::NAME, self::MODEL, self::USER_ID, self::DATA];
}
