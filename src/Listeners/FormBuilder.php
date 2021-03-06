<?php

namespace FastDog\Core\Listeners;

use FastDog\Core\Form\BaseForm;
use FastDog\Core\Interfaces\AdminPrepareEventInterface;
use FastDog\Core\Models\BaseModel;
use FastDog\Core\Models\ModuleManager;
use FastDog\Core\Properties\BaseProperties;
use FastDog\Core\Properties\Interfases\PropertiesInterface;
use Illuminate\Http\Request;
use FastDog\Core\Models\FormFieldTypes;

/**
 * Class AdminPrepare
 * @package FastDog\Core\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class FormBuilder
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * ContentAdminPrepare constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param AdminPrepareEventInterface $event
     */
    public function handle(AdminPrepareEventInterface $event)
    {

        /**  @var $moduleManager ModuleManager */
        $moduleManager = \App::make(ModuleManager::class);

        /** @var BaseModel $item */
        $item = $event->getItem();

        /** @var array $data */
        $data = $event->getData();

        /** @var array $result */
        $result = $event->getResult();

        $result['form']['is_preset'] = 'N';// флаг конструктора формы

        /** @var BaseForm $form */
        $form = BaseForm::where([
            BaseForm::MODEL => $item->getModelId(),
            BaseForm::USER_ID => 0,//<-- all users
        ])->first();

        if (!$form) {
            foreach ($result['form']['tabs'] as &$tabs) {
                $new_fielsd = [];
                foreach ($tabs->fields as $k => &$field) {
                    $field['edit_id'] = md5($field['type'] . $k . microtime(true));

                    /*
                     * Определение типов полей для размещения в конструкторе
                     */
                    switch ($field['type']) {
                        case FormFieldTypes::TYPE_COMPONENT_SAMPLE_PROPERTIES:
                            if (class_exists($field['model'])) {
                                (new $field['model'])->properties()->each(function ($_field) use (&$new_fielsd) {

                                    $_field['edit_id'] = md5($_field['alias'] . count($new_fielsd));
                                    $_field['label'] = $_field['name'];

                                    if (is_array($_field['type'])) {
                                        $_field['type'] = (object)$_field['type'];
                                    }

                                    switch ($_field['type']->id) {
                                        case BaseProperties::TYPE_STRING:
                                        case BaseProperties::TYPE_NUMBER:
                                            $_field['type'] = FormFieldTypes::TYPE_TEXT;
                                            array_push($new_fielsd, $_field);
                                            break;
                                        case BaseProperties::TYPE_MAP:
                                            $_field['type'] = 'map';// TODO: add type map!
                                            array_push($new_fielsd, $_field);
                                            break;
                                        case BaseProperties::TYPE_FILE:
                                            $_field['type'] = FormFieldTypes::TYPE_MEDIA;
                                            array_push($new_fielsd, $_field);
                                            break;
                                        case BaseProperties::TYPE_SELECT:
                                            $_field['type'] = FormFieldTypes::TYPE_SELECT;
                                            array_push($new_fielsd, $_field);
                                            break;
                                        default:
                                            break;
                                    }
                                });
                            }
                            break;
                        default:
                            break;
                    }
                }

                if ($new_fielsd !== []) {
                    $tabs->edit_fields = $new_fielsd;
                }
            }

            /** @var BaseForm $form */
            $form = BaseForm::create([
                BaseForm::NAME => get_class($item),
                BaseForm::MODEL => $item->getModelId(),
                BaseForm::USER_ID => 0,
                BaseForm::DATA => json_encode([
                    'form' => $result['form'],
                    'preset' => [],
                ]),
            ]);
        }

        $form_data = $form->getData();

        if (isset($form_data[BaseForm::DATA]->preset) && $form_data[BaseForm::DATA]->preset !== []) {
            $result['form'] = (array)$form_data[BaseForm::DATA]->form;
            $preset = collect($form_data[BaseForm::DATA]->preset);// конфигурация полей заданных в конструкторе

            foreach ($result['form']['tabs'] as $k => &$fieldSet) {
                $presetFields = $preset->get($fieldSet->id);
                if ($presetFields) {
                    $fieldSet->fields = [
                        'left' => $presetFields->left,
                        'right' => $presetFields->right,
                        'center' => $presetFields->center,
                        'center_second' => $presetFields->center_second,
                    ];
                    $fieldSet->side = (isset($presetFields->side)) ? $presetFields->side : [];
                } else {
                    unset($result['form']['tabs'][$k]);
                }
            }

            $result['form']['is_preset'] = 'Y';
        }

        $result['form']['form_builder'] = [// идентификатор редактируемой формы
            'id' => $form->id,
        ];
        $event->setResult($result);

        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
    }
}
