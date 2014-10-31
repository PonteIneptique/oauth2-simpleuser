<?php

	namespace Perseids\OAuth2;

	use Perseids\OAuth2\Entity\ModelManagerFactory;

	use Silex\Application;
	use Doctrine\Common\Annotations\AnnotationReader;
	use Doctrine\Common\Cache\FilesystemCache;
	use Doctrine\ORM\EntityManager;
	use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
	use Doctrine\ORM\Tools\Setup;


	class OAuth2EntityManager {
		protected $ModelManagerFactory;
		protected $EntityManager;

    	public function __construct(Application $app) {
			// Return an instance of Doctrine ORM entity manager.
			$app['doctrine.orm.entity_manager'] = $app->share(function ($app) {
			    $conn = $app['dbs']['default'];
			    $em = $app['dbs.event_manager']['default'];
			   
			    $isDevMode = false;
			    $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__.'/../Entity'), $isDevMode, null, null, false);

			    return EntityManager::create($conn, $config, $em);
			});

			// Return entity classes for model manager.
			$app['authbucket_oauth2.model'] = array(
			    'access_token' => 'Perseids\\OAuth2\\Entity\\AccessToken',
			    'authorize' => 'Perseids\\OAuth2\\Entity\\Authorize',
			    'client' => 'Perseids\\ClientsManager\\Entity\\Client',
			    'code' => 'Perseids\\OAuth2\\Entity\\Code',
			    'refresh_token' => 'Perseids\\OAuth2\\Entity\\RefreshToken',
			    'scope' => 'Perseids\\OAuth2\\Entity\\Scope',
			    'user' => 'Perseids\\OAuth2\\Entity\\User',
			);

			// Add model managers from ORM.
			$app['authbucket_oauth2.model_manager.factory'] = $app->share(function ($app) {
			    return new ModelManagerFactory(
			        $app['doctrine.orm.entity_manager'],
			        $app['authbucket_oauth2.model']
			    );
			});

			$this->EntityManager = $app['doctrine.orm.entity_manager'];
			$this->ModelManagerFactory = $app['authbucket_oauth2.model_manager.factory'];
		}

		public function getModelManagerFactory() {
			return $this->ModelManagerFactory;
		}

		public function getEntityManager() {
			return $this->EntityManager;
		}
	}
?>