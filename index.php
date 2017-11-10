<?php

require 'vendor/autoload.php';

require 'class/Crypt.php';
require 'class/Pic.php';
require 'class/Club.php';
require 'class/ClubDetails.php';
require 'class/Clubs.php';
require 'class/Token.class.php';
require 'class/JsRetdata.class.php';
require 'class/Json.php';
require 'class/Sliders.php';
require 'class/CvUser.php';
require 'class/CvClub.php';
require 'class/ThinkCrypt.php';
require 'class/Medoo.php';

require 'model/CvToken.php';
require 'model/CvSliders.php';
require 'model/CvSearch.php';
require 'model/CvJSSDK.php';
require 'model/CvClubDetails.php';
require 'model/Safe.php';
require 'model/TokenCheckPoint.php';


require 'config/key.php';
require 'config/pic.php';
require 'config/wxApp.php';
require 'config/database_info.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$config = [
    'settings' => [
        'displayErrorDetails' => true
    ],
];

$app = new \Slim\App($config);

//todo: 增加路由组 用header获取 toekn 用路由组统一校验token

/*
 * get 请求
 */


//获取轮播图
$app->get('/pic/sliders/{token}', function ($request, $response) {
    $route = $request->getAttribute('route');
    $token = null;
    if ($route->getArgument('token') !== null) {
        $token = $route->getArgument('token');
    }
    $database = new Medoo(array("database_name" => DATABASE_NAME));
    $cvSliders = new CvSliders($token, $database);
    return $response->withStatus($cvSliders->getStatus());

});

//搜索
$app->get('/search/{content}/{token}', function ($request, $response) {
    $route = $request->getAttribute('route');
    $content = null;
    $token = null;
    if ($route->getArgument('token') !== null) {
        $token = $route->getArgument('token');
    }
    if ($route->getArgument('content') !== null) {
        $content = $route->getArgument('content');
    }
    $database = new Medoo(array("database_name" => DATABASE_NAME));
    $CvSearch = new CvSearch($content, $token, $database);
    return $response->withStatus($CvSearch->getStatus());
});

//获取社团详情页（信息+点赞头像+底部图）
$app->get('/clubs/{id}/{token}', function ($request, $response) {
    $route = $request->getAttribute('route');
    $token = ($route->getArgument('token') !== null) ? $route->getArgument('token') : null;
    $id = ($route->getArgument('id') !== null) ? $route->getArgument('id') : null;

    $database = new Medoo(array("database_name" => DATABASE_NAME));
    $cvClubDetails = new CvClubDetails($id,$token,$database);
    return $response->withStatus($cvClubDetails->getStatus());

});


/*
 * post 请求
 */

//创建token
$app->post('/token/', function ($request, $response, $args) {
    $code = null;
    if (isset($request->getParsedBody()["code"])) {
        $code = $request->getParsedBody()["code"];
    }
    $database = new Medoo(array("database_name" => DATABASE_NAME));
    $cvToken = new CvToken(null, $code, $database);
    return $response->withStatus($cvToken->getStatus());
});


//创建jssdk签名
$app->post('/sign/', function ($request, $response, $args) {
    $url = null;
    if (isset($request->getParsedBody()["url"])) {
        $url = $request->getParsedBody()["url"];
    }
    $database = new Medoo(array("database_name" => DATABASE_NAME));
    $cvJssdk = new CvJSSDK($url);
    return $response->withStatus($cvJssdk->getStatus());
});


//    $route = $request->getAttribute($args);
//    $code = $route->getArgument('code');
//    return $response->getBody()->write($code);
//执行code检查


/**
 * $app->group('/utils', function () use ($app) {
 * $app->get('/date', function ($request, $response) {
 * return $response->getBody()->write(date('Y-m-d H:i:s'));
 * });
 * $app->get('/time', function ($request, $response) {
 * return $response->getBody()->write(time());
 * });
 * })->add(function ($request, $response, $next) {
 * $response->getBody()->write('It is now ');
 * $response = $next($request, $response);
 * $response->getBody()->write('. Enjoy!');
 *
 * return $response;
 * });
 *
 *
 * $app->group('/users/{id:[0-9]+}', function () {
 * $this->map(['GET', 'DELETE', 'PATCH', 'PUT'], '', function ($request, $response, $args) {
 * // Find, delete, patch or replace user identified by $args['id']
 * })->setName('user');
 *
 * $this->get('/reset-password', function ($request, $response, $args) {
 * // Route for /users/{id:[0-9]+}/reset-password
 * // Reset the password for user identified by $args['id']
 * })->setName('user-password-reset');
 * });
 *
 * $app->post('/token/{code}', function (ServerRequestInterface $request, ResponseInterface $response) {
 * // 使用 PSR 7 $request 对象
 * $route = $request->getAttribute('route');
 * $code = $route->getArgument('code');
 * echo $code;
 * //执行code检查
 * });
 **/


$app->run();