<?php
require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Trichechus\Manatus\ManatizerService;
use Trichechus\Manatus\ManateeRequest;
use Trichechus\Manatus\ManateeApplication;

$app = new ManateeApplication();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => realpath(__DIR__ . '/../views')
));



$app['manatizer'] = $app->share(function (Application $app) {
    return new ManatizerService(realpath(__DIR__ . '/../manatees'), __DIR__);
});

$app->get('/', function(Application $app) {

    $manatizerService = $app['manatizer'];
    $response = $app->render(
        'index.html.twig',
        [
            'realManatees' => $manatizerService->getManatees('jpg'),
            'svgManatees' => $manatizerService->getManatees('svg')
        ]
    );
    $response->setPublic();
    $response->setExpires(new DateTime('+1 hours'));
    return $response;
});

$app->get('/specimens', function(Application $app) {

    $manatizerService = $app['manatizer'];
    $response = $app->render(
        'specimens.html.twig',
        [
            'realManatees' => $manatizerService->getManatees('jpg'),
            'svgManatees' => $manatizerService->getManatees('svg')
        ]
    );
    $response->setPublic();
    $response->setExpires(new DateTime('+1 hours'));
    return $response;

});

$app->get('/{width}/{height}.{format}', function (Application $app, Request $request, $width, $height, $format) {


    if (! in_array($format, ['jpg', 'svg'])) {
        throw new \InvalidArgumentException('Only jpg or svg formats are available, sorry', 500);
    }

    if ($width < 16 || $height < 16) {
        throw new \InvalidArgumentException('Can not serve a manatee so small, sorry', 500);
    }

    if ($width > 2000 || $height > 2000) {
        throw new \InvalidArgumentException('Steller\'s sea cows are extinct, sorry', 500);
    }

    $manatizerService = $app['manatizer'];
    $manateeRequest = new ManateeRequest($width, $height, $format);

    $response = new Response(
        $manatizerService->createManatee($manateeRequest),
        200,
        [
            'Content-Type' => getMime($format),
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

$app->get('/{specificManatee}/{width}/{height}.{format}', function (Application $app, Request $request, $width, $height, $specificManatee, $format) {

    if (! in_array($format, ['jpg', 'svg'])) {
        throw new \InvalidArgumentException('Only jpg or svg formats are available, sorry', 500);
    }

    if ($width < 16 || $height < 16) {
        throw new \InvalidArgumentException('Can not serve a manatee so small, sorry', 500);
    }

    if ($width > 2000 || $height > 2000) {
        throw new \InvalidArgumentException('Steller\'s sea cows are extinct, sorry', 500);
    }

    $manatizerService = $app['manatizer'];
    $manateeRequest = new ManateeRequest($width, $height, $format);
    $manateeRequest->setSpecificManatee($specificManatee);
    $response = new Response(
        $manatizerService->createManatee($manateeRequest),
        200,
        [
            'Content-Type' => getMime($format),
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


function getMime($format) {
    $mimes = [
        'jpg' => 'image/jpg',
        'svg' => 'image/svg+xml'
    ];
    return $mimes[$format];
}