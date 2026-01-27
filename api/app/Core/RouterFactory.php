<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

    private const string API_PATH = 'api/v1/';

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
        $router->addRoute(self::API_PATH . 'ping', 'Api:Generator:ping');
        $router->addRoute(self::API_PATH . 'status', 'Api:Generator:status');
        $router->addRoute(self::API_PATH . 'ai', 'Api:Generator:ai');
        $router->addRoute('<presenter>/<action>[/<id>]', 'Front:Home:default');
		return $router;
	}
}
