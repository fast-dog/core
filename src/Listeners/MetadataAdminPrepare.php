<?php
namespace FastDog\Core\Listeners;

use FastDog\Core\Interfaces\AdminPrepareEventInterface;
use FastDog\Core\Models\DomainManager;
use Illuminate\Http\Request;

/**
 * Class MetadataAdminPrepare
 *
 * @package FastDog\Core\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class MetadataAdminPrepare
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
//        $item = $event->getItem();
        $data = $event->getData();

        if ($data['data'] === null || $data['data'] == []) {
            $data['data'] = new \StdClass();
        }

        if (!isset($data['data']->meta_robots)) {
            $data['data']->meta_robots = [
                'INDEX', 'FOLLOW',
            ];
        }

        if (!isset($data['data']->hreflang)) {
            if (!isset($data['data']->hreflang)) {
                $data['data']->hreflang = [];
            }

            $domains = DomainManager::getAccessDomainList();
            foreach ($domains as $domain) {
                if ($domain['id'] !== '000') {
                    array_push($data['data']->hreflang, [
                        'code' => $domain['id'],
                        'lang' => $domain['lang'],
                        'value' => '',
                    ]);
                }
            }
        }

        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
    }
}