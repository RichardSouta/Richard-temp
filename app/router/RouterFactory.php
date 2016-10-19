<?php

namespace App;

use App\Presenters\ClubPresenter;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{

    /**
     * @return Nette\Application\IRouter
     */
    public function createRouter()
    {
        $router = new RouteList;
        $router[] = new Route('club/<id>', 'Club:display');
        $router[] = new Route('[<presenter>/]images/collectible/<id>', 'Collectible:show');
        $router[] = new Route('category/new', 'Category:new');
        $router[] = new Route('category/<category>', 'Category:default');
        $router[] = new Route('collectible/edit/<id>', 'Collectible:edit');
        $router[] = new Route('collectible/trade/<id>', 'Collectible:trade');
        $router[] = new Route('collectible/final/<id>', 'Collectible:final');
        $router[] = new Route('collectible/confirm/<id>', 'Collectible:confirm');
        $router[] = new Route('collectible/new', 'Collectible:new');
        $router[] = new Route('collectible[/<default>]/<id>', array(
            'presenter' => 'Collectible',
            'action' => 'default',
            'id' => array(
                Route::FILTER_IN => function ($url) {
                    return self::urlToId($url);
                },
                Route::FILTER_OUT => function ($id) {
                    return self::idToUrl($id);
                },
            ),
        ));
        $router[] = new Route('user/edit/<id>', array(
            'presenter' => 'User',
            'action' => 'edit',
            'id' => array(
                Route::FILTER_IN => function ($url) {
                    return self::urlToId2($url);
                },
                Route::FILTER_OUT => function ($id) {
                    return self::idToUrl2($id);
                },
            ),
        ));

        $router[] = new Route('user[/<default>]/<id>', array(
            'presenter' => 'User',
            'action' => 'default',
            'id' => array(
                Route::FILTER_IN => function ($url) {
                    return self::urlToId2($url);
                },
                Route::FILTER_OUT => function ($id) {
                    return self::idToUrl2($id);
                },
            ),
        ));

        $router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
        return $router;
    }

    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function idToUrl($id)
    {
        $urlTile = str_replace(" ", "-", $this->em->getRepository('App\Model\Entity\Collectible')->find($id)->getName());
        $url = $id . "-" . $urlTile;
        setlocale(LC_CTYPE, 'en_US');
        return urlencode($url);
    }

    public function urlToId($url)
    {
        $pole = explode("-", $url);
        return $pole[0];
    }

    public function idToUrl2($id)
    {
        $url = str_replace(" ", "-", $this->em->getRepository('App\Model\Entity\User')->find($id)->getUsername());
        setlocale(LC_CTYPE, 'en_US');
        return urlencode($url);
    }

    public function urlToId2($url)
    {
        $id = $this->em->getRepository('App\Model\Entity\User')->findOneByUsername($url)->getId();
        return $id;
    }

}
