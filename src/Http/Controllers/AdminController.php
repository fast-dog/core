<?php

namespace FastDog\Core\Http\Controllers;


use FastDog\Core\Http\Controllers\Controller;
use FastDog\Core\Models\AdminMenu;
use FastDog\Core\Models\Desktop;
use FastDog\Core\Models\DomainManager;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class AdminController
 * @package FastDog\Core\Controllers
 * @version 0.1.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 *
 */
class AdminController extends Controller
{

    use AuthenticatesUsers;

    /**
     * @var string $redirectTo
     */
    protected $redirectTo = '/admin';

    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        $this->middleware('admin')->except(['getLogin', 'postLogin']);
    }

    /**
     * Главная страница администрирования
     *
     * @return \Illuminate\View\View
     */
    public function getIndex()
    {
        return view('core::admin.dashboard');
    }

    /**
     * Страница авторизации
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getLogin()
    {
        return view('core::admin.login');
    }

    /**
     * Авторизация
     *
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postLogin(Request $request)
    {
        $request->merge([
            'type' => 'admin',
            'status' => 'active',
            'site_id' => DomainManager::getSiteId(),
        ]);

        return $this->login($request);
    }

    /**
     * Получение меню навигации по разделам администрирования
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenu(Request $request): JsonResponse
    {
        $result = ['success' => false];
        $result['items'] = AdminMenu::get();

//        $config = ContentConfig::getAllConfig();
//        if (isset($config[ContentConfig::CONFIG_CKEDITOR])) {
//            $result['ckeditor_config'] = $config[ContentConfig::CONFIG_CKEDITOR];
//        }

        /**
         * @var $user User
         */
        $user = \Auth::getUser();
        if ($user) {
            $user = User::find($user->id);
            $result['user'] = [
                'id' => $user->id,
                'name' => $user->getName(),
                'role' => $user->getRoleName(),
                'photo' => $user->getPhoto(),
            ];
        }

        return $this->json($result);
    }

    /**
     * Метод возвращает статистику отображаемую на главной странице
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardStatistic(Request $request): JsonResponse
    {
        $result = ['success' => true, 'items' => [[]]];

        return $this->json($result);
    }

    /**
     * Получение виджетов рабочего стола
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDesktop(Request $request): JsonResponse
    {
        $result = ['success' => true, 'items' => []];

        $items = Desktop::where(function ($query) {
            $query->where(Desktop::SITE_ID, DomainManager::getSiteId());
        })->orderBy('sort')->get();
        /**
         * @var $item Desktop
         */
        foreach ($items as $item) {
            array_push($result['items'], $item->getData());
        }

        return $this->json($result);
    }

    /**
     * Сортировка виджетов на рабочем столе
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postDesktopSort(Request $request): JsonResponse
    {
        $result = ['success' => true, 'items' => []];

        $updatePosition = $request->input('set', []);
        foreach ($updatePosition as $id => $position) {
            Desktop::where('id', $id)->update([
                Desktop::SORT => $position,
            ]);
        }

        return $this->json($result);
    }

    /**
     * Удаление виджета с рабочего стола
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function postDesktopDelete(Request $request): JsonResponse
    {
        $widget = Desktop::where('id', $request->input('id'))->first();
        Desktop::check('N', [
            'name' => $widget->name,
            'type' => $widget->type,
        ]);

        return $this->json(['success' => true]);
    }

}
