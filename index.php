<?php

require 'vendor/autoload.php';

require 'class/Crypt.php';
require 'class/CvBottomPic.php';
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


/*
 * get 请求
 */


//获取轮播图
$app->get('/pic/sliders/', function ($request, $response) {
    if (!$request->hasHeader('cookie') || !isset($_COOKIE['token'])) {
        return $response->withStatus(412)->write('Precondition Failed');
    } else {
        $token = $_COOKIE['token'];
        $database = new Medoo(array("database_name" => DATABASE_NAME));
        $cvSliders = new CvSliders($token, $database);
        return $response->withStatus($cvSliders->getStatus());
    }
});

//搜索
$app->get('/search/{content}/', function ($request, $response) {
    if (!$request->hasHeader('cookie') || !isset($_COOKIE['token'])) {
        return $response->withStatus(412)->write('Precondition Failed');
    } else {
        $token = $_COOKIE['token'];
        $route = $request->getAttribute('route');
        $content = ($route->getArgument('content') !== null) ? $route->getArgument('content') : null;
    }
    $database = new Medoo(array("database_name" => DATABASE_NAME));
    $CvSearch = new CvSearch($content, $token, $database);
    return $response->withStatus($CvSearch->getStatus());
});


//社团相关路由组
$app->group('/clubs', function () {

    //获取社团详情页（信息+点赞头像+底部图）
    $this->get('/{id}/', function ($request, $response) {
        if (!$request->hasHeader('cookie') || !isset($_COOKIE['token'])) {
            return $response->withStatus(412)->write('Precondition Failed');
        } else {
            $token = $_COOKIE['token'];
            $route = $request->getAttribute('route');
            $id = ($route->getArgument('id') !== null) ? $route->getArgument('id') : null;

            $database = new Medoo(array("database_name" => DATABASE_NAME));
            $cvClubDetails = new CvClubDetails($id, $token, $database);

            return $response->withStatus($cvClubDetails->getStatus());
        }
    });
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
    $cvToken = new CvToken($code, $database);
    return $response->withAddedHeader('set-cookies', "token=$cvToken->token")->withStatus($cvToken->getStatus());
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

$app->run();