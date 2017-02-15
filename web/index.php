<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

$baseDir = __DIR__ . '/../';

$loader = require $baseDir . '/vendor/autoload.php';

$app = new Application();

$app->error(function (Exception $e) use ($app) {
    return new \Symfony\Component\HttpFoundation\Response("Something goes terribly wrong: " . $e->getMessage());
});

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$app->register(
    new DoctrineServiceProvider(),
    [
        'db.options' => [
            'driver'        => 'pdo_mysql',
            'host'          => 'localhost',
            'dbname'        => 'sample',
            'user'          => 'root',
            'password'      => 'admin',
            'charset'       => 'utf8',
            'driverOptions' => [
                1002 => 'SET NAMES utf8',
            ],
        ],
    ]
);

$app->register(new DoctrineOrmServiceProvider(), [
    'orm.proxies_dir'             => $baseDir . 'src/App/Entity/Proxy',
    'orm.auto_generate_proxies'   => $app['debug'],
    'orm.em.options'              => [
        'mappings' => [
            [
                'type'                         => 'annotation',
                'namespace'                    => 'App\\Entity\\',
                'path'                         => $baseDir. 'src/App/Entity',
                'use_simple_annotation_reader' => false,
            ],
        ],
    ]
]);

$app->get('/', function (Application $app) {
    $foo = new \App\Entity\Foo();
    $foo->setName('Hello');

    $entityManager = $app['orm.em'];

    $entityManager->persist($foo);
    $entityManager->flush();

    return new \Symfony\Component\HttpFoundation\Response('Successfully inserted!');
});

$app->run();
