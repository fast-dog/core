<?php

namespace FastDog\Core\Listeners;

use FastDog\Core\Form\BaseForm;
use FastDog\Core\Interfaces\AdminPrepareEventInterface;
use FastDog\Core\Models\BaseModel;
use FastDog\Core\Models\ModuleManager;
use Illuminate\Http\Request;

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

        /** @var BaseForm $form */
        $form = BaseForm::where([
            BaseForm::MODEL => $item->getModelId(),
            BaseForm::USER_ID => 0,//<-- all users
        ])->firs();

        if (!$form) {
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
            $result['form'] = $form_data[BaseForm::DATA]->preset;
            $event->setResult($result);
        }

        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
    }
}
