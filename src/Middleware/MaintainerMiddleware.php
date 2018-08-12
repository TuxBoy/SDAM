<?php
namespace SDAM\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SDAM\Config;
use SDAM\EntityAdapter\EntityAdapterInterface;
use SDAM\Maintainer;

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
	 *
	 * @param EntityAdapterInterface $entityAdapter
	 * @param array $config
	 */
    public function __construct(EntityAdapterInterface $entityAdapter, array $config = [])
	{
        $defaultConfig = array_merge([
            Config::DATABASE => [
                'dbname'   => 'autoMigrate',
                'user'     => 'root',
                'password' => 'root',
                'host'     => 'localhost',
                'driver'   => 'pdo_mysql',
            ],
            Config::ENTITY_PATH => 'App\Entity'
        ], $config);
        Config::current()->configure($defaultConfig);
        $this->entities = $entityAdapter->toArray();
    }

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param callable $next
	 * @return ResponseInterface
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \ReflectionException
	 * @throws \PhpDocReader\AnnotationException
	 * @throws \Throwable
	 */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $maintainer = new Maintainer($this->entities);
        $maintainer->run();
        return $next($request, $response);
    }

}