<?php

namespace FastDog\Core\Controllers;


use FastDog\Config\Entity\DomainManager;
use FastDog\Content\Entity\ContentConfig;
use FastDog\Core\AdminMenu;
use FastDog\Core\Desktop;
use FastDog\Core\Http\Controllers\Controller;
use FastDog\Users\Entity\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
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
     * Авторизация
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getLogin()
    {
        return view('core::admin.login');
    }

    public function postLogin(Request $request)
    {
        $request->merge([
            User::TYPE => User::USER_TYPE_ADMIN,
            User::STATUS => User::STATUS_ACTIVE,
            User::SITE_ID => DomainManager::getSiteId(),
        ]);

        return $this->login($request);
    }

    /**
     * Получение меню навигации по разделам администрирования
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenu(Request $request)
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
    public function getDashboardStatistic(Request $request)
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
    public function getDesktop(Request $request)
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
    public function postDesktopSort(Request $request)
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
     */
    public function postDesktopDelete(Request $request)
    {
        $widget = Desktop::where('id', $request->input('id'))->first();
        Desktop::check('N', [
            'name' => $widget->name,
            'type' => $widget->type,
        ]);

        return $this->json(['success' => true]);
    }

}
