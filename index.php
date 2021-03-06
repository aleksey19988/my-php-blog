<?php

use Aleksey\MyPhpBlog\LatestPosts;
use Aleksey\MyPhpBlog\Twig\AssetExtension;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Aleksey\MyPhpBlog\PostMapper;

require __DIR__ . '/vendor/autoload.php';

$loader = new FilesystemLoader('templates');
$view = new Environment($loader);

$view->addExtension(new AssetExtension());

$config = include 'config/database.php';
['dsn' => $dsn, 'username' => $username, 'password' => $password] = $config;

try {
    $connection = new PDO($dsn, $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    echo 'Database error: ' . $exception->getMessage();
    die();
}

$app = AppFactory::create();
$app->setBasePath("/my-php-blog");
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) use ($view, $connection) {
    $latestPosts = new LatestPosts($connection);
    $posts = $latestPosts->get(2);
    $body = $view->render('index.twig', [
        'posts' => $posts,
    ]);
    $response->getBody()->write($body);
    return $response;
});



$app->get('/about', function (Request $request, Response $response) use ($view) {
    $body = $view->render('about.twig', [
        'name' => 'Alex',
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/blog[/{page}]', function (Request $request, Response $response, $args) use ($view, $connection) {
    $latestPosts = new PostMapper($connection);
    $page = isset($args['page']) ? (int) $args['page'] : 1;
    $limit = 2;
    $posts = $latestPosts->getList($page, $limit, 'DESC');
    $body = $view->render('blog.twig', [
        'posts' => $posts,
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/{url_key}', function (Request $request, Response $response, $args) use ($view, $connection) {
    $postMapper = new PostMapper($connection);
    $post = $postMapper->getByUrlKey((string) $args['url_key']);
    if (empty($post)) {
        $body = $view->render('not-found.twig');
    } else {
        $body = $view->render('post.twig', [
            'post' => $post,
        ]);
    }
    $response->getBody()->write($body);
    return $response;
});

$app->run();
