<?php

use League\Route\Router;
use Routes\LayoutRoute;

$router = new Router();
$router->get("/", function ($request) use ($ctx) {
    $route = new LayoutRoute();
    return $route->executeRoute($ctx, $request, []);
});
