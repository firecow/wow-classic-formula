<?php

use League\Route\Router;
use Routes\ItemLog;
use Routes\LayoutRoute;
use Routes\Query;
use Routes\Rest;

$router = new Router();
$router->get("/", function ($request) use ($ctx) {
    $route = new LayoutRoute();
    return $route->executeRoute($ctx, $request, []);
});
$router->post("/query/", function ($request) use ($ctx) {
    $route = new Query();
    return $route->executeRoute($ctx, $request, []);
});
$router->post("/rest/", function ($request) use ($ctx) {
    $route = new Rest();
    return $route->executeRoute($ctx, $request, []);
});
$router->get("/itemlog/", function ($request) use ($ctx) {
    $route = new ItemLog();
    return $route->executeRoute($ctx, $request, []);
});
