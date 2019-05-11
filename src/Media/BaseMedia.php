<?php
namespace App\Core\Media;


use App\Modules\Media\Entity\GalleryItem;
use Illuminate\Database\Eloquent\Model;

/**
 * Базовая реализация работы с файлами
 *
 * @package App\Core\Media
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BaseMedia extends Model
{
    /**
     * Идентификатор объекта
     * @const string
     */
    const ITEM_ID = 'item_id';

    /**
     * Идентификатор медия файла
     * @const string
     */
    const MEDIA_ID = 'media_id';

    /**
     * Идентификатор модели
     * @const string
     */
    const MODEL_ID = 'model_id';

    /**
     * @const string
     */
    const TYPE_FILE = 'file';

    /**
     * @const string
     */
    const TYPE_STRING = 'string';

    /**
     * @const string
     */
    const SORT = 'sort';

    /**
     * @const string
     */
    const DATA = 'data';

    /**
     * Хэш файла,
     * необходим для контроля целостности файловой системы и удаления записей по удалению файла
     * @const string
     */
    const HASH = 'hash';

    /**
     * @var bool $timestamps
     */
    public $timestamps = false;

    /**
     * @var string $table
     */
    public $table = 'system_media_store';
    /**
     * @var array
     */
    public $fillable = [self::ITEM_ID, self::MEDIA_ID, self::MODEL_ID, self::SORT, self::DATA, self::HASH];

    /**
     * @return array
     */
    public function getData(): array
    {
        $result = [
            'id' => $this->id,
            'type' => self::TYPE_FILE,
            self::ITEM_ID => $this->{self::ITEM_ID},
            self::MEDIA_ID => $this->{self::MEDIA_ID},
            self::MODEL_ID => $this->{self::MODEL_ID},
            self::SORT => $this->{self::SORT},
            self::HASH => $this->{self::HASH},
            self::DATA => json_decode($this->{self::DATA}),
            'value' => ($this->file) ? $this->file->path : '',
        ];
        if (isset($result[self::DATA]->description)) {
            $result['description'] = $result[self::DATA]->description;
        }

        return $result;
    }

    public function file()
    {
        return $this->hasOne(GalleryItem::class, 'id', self::MEDIA_ID);
    }
}