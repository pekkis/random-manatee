<?php
require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Trichechus\Manatus\ManatizerService;
use Trichechus\Manatus\ManateeRequest;
use Trichechus\Manatus\ManateeApplication;

$app = new ManateeApplication();
$app['debug'] = false;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => realpath(__DIR__ . '/../views')
));



$app['manatizer'] = $app->share(function (Application $app) {
    return new ManatizerService(realpath(__DIR__ . '/../manatees'), __DIR__);
});

$app->get('/', function(Application $app) {
    $response = $app->render('index.html.twig');
    return $response;
});


$app->get('/{width}/{height}.jpg', function (Application $app, Request $request, $width, $height) {

    if ($width < 50 || $height < 50) {
        throw new \InvalidArgumentException('Can not serve a manatee so small, sorry', 500);
    }

    if ($width > 2000 || $height > 2000) {
        throw new \InvalidArgumentException('Steller\'s sea cows are extinct, sorry', 500);
    }

    $manatizerService = $app['manatizer'];
    $manateeRequest = new ManateeRequest($width, $height, 'jpeg');
    $response = new Response(
        $manatizerService->createManatee($manateeRequest),
        200,
        [
            'Content-Type' => 'image/jpg',
        ]
    );

    $response->setPublic();
    $response->setExpires(new DateTime('+30 days'));
    return $response;
})->convert('width', function ($width) {
    return (int) $width;
})->convert('height', function ($height) {
    return (int) $height;
});

$app->get('/{specificManatee}/{width}/{height}.jpg', function (Application $app, Request $request, $width, $height, $specificManatee) {

    if ($width < 50 || $height < 50) {
        throw new \InvalidArgumentException('Can not serve a manatee so small, sorry', 500);
    }

    if ($width > 2000 || $height > 2000) {
        throw new \InvalidArgumentException('Steller\'s sea cows are extinct, sorry', 500);
    }

    $manatizerService = $app['manatizer'];
    $manateeRequest = new ManateeRequest($width, $height, 'jpeg');
    $manateeRequest->setSpecificManatee($specificManatee);
    $response = new Response(
        $manatizerService->createManatee($manateeRequest),
        200,
        [
            'Content-Type' => 'image/jpg',
        ]
    );

    $response->setPublic();
    $response->setExpires(new DateTime('+30 days'));
    return $response;
})->convert('width', function ($width) {
    return (int) $width;
})->convert('height', function ($height) {
    return (int) $height;
})->convert('specificManatee', function ($sm) {
    return (int) $sm;
});

$app->error(function (Exception $e, $code) use ($app) {

    if ($app['debug']) {
        return false;
    }

    switch ($code) {
        case 404:
            $message = 'Page not found';
            break;
        default:
            $message = $e->getMessage();
    }

    $response = $app->render('error.html.twig', ['message' => $message, 'code' => $code]);
    return $response;
});


$app->run();
