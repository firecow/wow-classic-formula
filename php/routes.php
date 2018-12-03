<?php

use League\Route\Router;
use Routes\LayoutRoute;
use Routes\Query;

$router = new Router();
$router->get("/", function ($request) use ($ctx) {
    $route = new LayoutRoute();
    return $route->executeRoute($ctx, $request, []);
});
$router->post("/query/", function ($request) use ($ctx) {
    $route = new Query();
    return $route->executeRoute($ctx, $request, []);
});
