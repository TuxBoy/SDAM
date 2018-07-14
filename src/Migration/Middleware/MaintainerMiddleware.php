<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TuxBoy\Maintainer;

/**
 * MaintainerMiddleware
 *
 * @package App\Middleware
 */
class MaintainerMiddleware
{

    /**
     * @var string[]
     */
    private $entities = [
        //...
    ];

    /**
     * MaintainerMiddleware constructor
     * @param array $entities
     * @param array $config
     */
    public function __construct(array $entities, array $config = [])
    {
        $defaultConfig = array_merge($config, [
            \TuxBoy\Config::DATABASE => [
                'dbname'   => 'autoMigrate',
                'user'     => 'root',
                'password' => 'root',
                'host'     => 'localhost',
                'driver'   => 'pdo_mysql',
            ],
            \TuxBoy\Config::ENTITY_PATH => 'App\Model\\'
        ]);
        \TuxBoy\Config::current()->configure($defaultConfig);
        $this->entities = $entities;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     * @throws \Doctrine\DBAL\DBALException
     * @throws \ReflectionException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $maintainer = new Maintainer($this->entities);
        $maintainer->run();
        return $next($request, $response);
    }

}