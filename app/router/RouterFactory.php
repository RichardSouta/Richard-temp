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
        $router[] = new Route('club/new', 'Club:new');
        $router[] = new Route('club/<id>', 'Club:display');
        $router[] = new Route('chat/<id>','Chat:default');
        $router[] = new Route('[<presenter>/]images/collectible/<id>', 'Collectible:show');
        $router[] = new Route('[<presenter>/]images/user/<id>', 'User:showPicture');
        $router[] = new Route('category/new', 'Category:new');
        $router[] = new Route('category/<category>', [
            'presenter' => 'Category',
            'action' => 'default',
            'category' => [
                Route::FILTER_IN => function ($url) {
                    return self::urlToIdCategory($url);
                },
                Route::FILTER_OUT => function ($id) {
                    return self::idToUrlCategory($id);
                }
            ]
        ]);
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
        $collectible = $this->em->getRepository('App\Model\Entity\Collectible')->find($id);
        if (!empty($collectible)) {
            $urlTile = str_replace(" ", "-", $collectible->getName());
            $url = $id . "-" . $urlTile;
            setlocale(LC_CTYPE, 'en_US');
            return urlencode($url);
        }
        return null;
    }

    public function urlToId($url)
    {
        $pole = explode("-", $url);
        if (!empty($pole[0]))
        {
            return $pole[0];
        }
        return null;

    }

    public function idToUrl2($id)
    {
        $user = $this->em->getRepository('App\Model\Entity\User')->find($id);
        if (!empty($user)) {
            $url = str_replace(" ", "-", $user->getUsername());
            setlocale(LC_CTYPE, 'en_US');
            return urlencode($url);
        }
        return null;
    }

    public function urlToId2($url)
    {
        $url = str_replace("-", " ", $url);
        $user = $this->em->getRepository('App\Model\Entity\User')->findOneByUsername($url);
        if (!empty($user)) {
            $id = $user->getId();
            return $id;
        }
        return null;
    }

    public function urlToIdCategory($url)
    {
        $url = str_replace("-", " ", $url);
        $category = $this->em->getRepository('App\Model\Entity\Category')->findOneByName($url);
        if (!empty($category)) {
            $id = $category->getId();
            return $id;
        }
        return null;
    }

    public function idToUrlCategory($id){
        $category = $this->em->getRepository('App\Model\Entity\Category')->find($id);
        if (!empty($category)) {
            $url = str_replace(" ", "-", $category->getName());
            setlocale(LC_CTYPE, 'en_US');
            return urlencode($url);
        }
        return null;
    }
}
