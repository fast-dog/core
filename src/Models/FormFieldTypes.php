<?php

namespace FastDog\Core\Models;

/**
 * Class FormFieldTypes
 *
 * Типы возможных полей в интерфейсе администрирования,
 * каждый тип должен быть реализован в виде компонента Vue интерфейса
 *
 *
 * @package FastDog\Core
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
abstract class FormFieldTypes
{
    /**
     * Реализация текстового поля: TextFormField
     *
     * @const string
     */
    const TYPE_TEXT = 'text-form-field';

    /**
     * Реализация текстового поля: TextFormField
     *
     * @const string
     */
    const TYPE_PASSWORD = 'password-form-field';

    /**
     * Реализация текстового поля с блокировкой ввода для псевдонима: TextAliasFormField
     *
     * @const string
     */
    const TYPE_TEXT_ALIAS = 'text-form-alias';

    /**
     * Реализация выпадающего списка с уровнем доступа: AccessListFormField
     *
     * @const string
     */
    const TYPE_ACCESS_LIST = 'access-list';

    /**
     * Реализация выпадающего списка: SelectFormField
     *
     * @const string
     */
    const TYPE_SELECT = 'select-form-field';

    /**
     * Реализация редактора кода: CodeEditorFormField
     *
     * @const string
     */
    const TYPE_CODE_EDITOR = 'code-editor-form-field';

    /**
     * Реализация строки поиска по заданному маршруту: SearchFormField
     *
     * @const string
     */
    const TYPE_SEARCH = 'search-form-field';

    /**
     * Реализация выбора медиа файла на сервере: MediaFormField
     *
     * @const string
     */
    const TYPE_MEDIA = 'media-form-field';

    /**
     * Реализация текстового редактора CKEDITOR: TextEditorFormField
     *
     * @const string
     */
    const TYPE_HTML_EDITOR = 'text-editor-form-field';

    /**
     * Реализация текстового редактора табов
     *
     * @const string
     */
    const TYPE_TABS_EDITOR = 'tabs-form-field';

    /**
     * Не является компонентом формы, служит для обозначения вложенных табов с полями (чаще редакторы html для описания)
     *
     * @const string
     */
    const TYPE_TABS = 'tabs';

    /**
     * Реализация поля ввода даты: DateFormField
     *
     * @const string
     */
    const TYPE_DATE = 'date-form-field';


    /**
     * Компонент для работы с медия материалами: MediaItems
     *
     * @const string
     */
    const TYPE_COMPONENT_MEDIA = 'media';

    /**
     * Компонент для работы с полями для поисковой оптимизации контента: Seo
     *
     * @const string
     */
    const TYPE_COMPONENT_SEO = 'seo';


    /**
     * Компонент для добавления произвольный свойств в контекст объекта
     * (контента, элемента каталога, элемента справочника и т.д.): SamplePropertyTable
     *
     * @const string
     */
    const TYPE_COMPONENT_SAMPLE_PROPERTIES = 'sample-properties-table';


    /**
     * Компонент для работы с значениями свойств категорий в позициях
     * Каталога (сводная таблицы выводимая ТОЛЬКО в форме редактирования элемента каталога): CatalogItemProperties
     *
     * @const string
     */
    const TYPE_COMPONENT_CATALOG_ITEM_PROPERTIES = 'catalog-item-properties';

    /**
     * Компонент для работы с свойствами категорий Каталога
     * (сводная таблицы выводимая ТОЛЬКО в форме редактирования категории)
     *
     * @const string
     */
    const TYPE_COMPONENT_CATALOG_CATEGORY_PROPERTIES = 'catalog-category-properties';

    /**
     * Компонент для работы с парамтерами позиции каталога относящимися к Торговому каталогу,
     * такими как габаритные размеры для служб доставки, цены, склады, скидки и торговые предложения.
     * (сводная таблица выводимая ТОЛЬКО в форме редактирования элемента каталога):
     *
     * @const string
     */
    const TYPE_COMPONENT_CATALOG_ITEM_STORE_PROPERTIES = 'catalog-item-store-parameters';

    /**
     * Компонент для работы с локализацией шаблонов
     *
     * @const string
     */
    const TYPE_COMPONENT_TRANSLATE_ITEMS = 'translate-items';

    /**
     * Поле выбора времени
     *
     * @const string
     */
    const TYPE_CLOCK_FIELD = 'clock-field';

    /**
     * Компонент заполнения времени
     *
     * @const string
     */
    const TYPE_CALENDAR_TIMES = 'calendar-times';

    /**
     * Автодополнение адреса
     *
     * @const string
     */
    const TYPE_ADDRESS = 'address-form-field';
}