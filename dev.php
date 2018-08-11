<?php
require __DIR__ . '/vendor/autoload.php';

\SDAM\Config::current()->configure([
	\SDAM\Config::DATABASE => [
       'dbname'   => 'test',
       'user'     => 'root',
       'password' => 'root',
       'host'     => 'localhost',
       'driver'   => 'pdo_mysql',
   ],
   \SDAM\Config::ENTITY_PATH => 'App\Entity',
]);

$maintainer = new \SDAM\Maintainer(new \SDAM\EntityAdapter\EntityAdapter(__DIR__ . '/example/Entity', [\App\Entity\Question::class]));
try {
	$maintainer->run();
	echo 'Maintainer run';
} catch (\Doctrine\DBAL\DBALException $e) {
	echo 'Problem with DBAL doctrine' . $e->getMessage();
} catch (\PhpDocReader\AnnotationException $e) {
	echo 'Problem with PhpDocReader' . $e->getMessage();
} catch (ReflectionException $e) {
	echo 'Problem with Reflection class' . $e->getMessage();
} catch (Throwable $e) {
	echo 'Last Problem' . $e->getMessage();
}