<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

/**
 * Class RouterFactory
 * @package App
 */
class RouterFactory
{

    /**
     * @return Nette\Application\IRouter
     */
    public static function createRouter()
    {

        $router = new RouteList();

        $router[] = $aRouter = new RouteList('Admin');
        $aRouter[] = new Route('admin/<presenter>/<action>[/<id>]', 'Homepage:default');

        $router[] = $fRouter = new RouteList('Front');

        /* Registrácia */
        $fRouter[] = new Route('registracia', 'Registration:default');

        /* Prihlásenie */
        $fRouter[] = new Route('prihlasenie', 'Login:default');

        /* Uživateľ */
        $fRouter[] = new Route('uzivatel/sprava_udajov', 'User:edit');
        $fRouter[] = new Route('uzivatel/<id>', 'User:profile');

        /* Správy */
        $fRouter[] = new Route('spravy/vytvorit_spravu', 'Messages:new');
        $fRouter[] = new Route('spravy/prijate/<id>', 'Messages:receive');
        $fRouter[] = new Route('spravy/odoslane/<id>', 'Messages:send');
        $fRouter[] = new Route('spravy', 'Messages:default');

        /* Novinky */
        $fRouter[] = new Route('novinky/<id>', 'News:default');

        /* Team */
        $fRouter[] = new Route('team/profil/<id>', 'Team:profile');
        $fRouter[] = new Route('team/vstupit/<id>', 'Team:join');

        $fRouter[] = new Route('liga/<id>', 'League:default');
        $fRouter[] = new Route('liga/zoznam_teamov/<id>', 'League:contestants');
        $fRouter[] = new Route('liga/tabulka/<id>', 'League:ladder');
        $fRouter[] = new Route('liga/pravidla/<id>', 'League:rules');
        $fRouter[] = new Route('liga/prihlasit/<id>', 'League:join');


        /* Defaultná routa nechať na konci !!!*/
        $fRouter[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');

        return $router;
    }

}
