<?php
require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Trichechus\Manatus\ManatizerService;

$app = new Silex\Application();
$app['debug'] = true;

$app['manatizer'] = $app->share(function (Application $app) {
    return new ManatizerService(realpath(__DIR__ . '/../manatees'));
});

$app->get('/{width}/{height}', function (Application $app, Request $request, $width, $height) {

    $manatizerService = $app['manatizer'];
    return new Response(
        $manatizerService->createManatee($width, $height),
        200,
        [
            'Content-Type' => 'image/jpg',
        ]
    );
});

$app->run();
