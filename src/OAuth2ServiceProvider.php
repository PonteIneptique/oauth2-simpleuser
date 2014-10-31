<?php

/**
 *  Modification so this works in a more various environment and can be plugged to both simple users and perseids/clients-manager
 *
 *
 * 
 * Original headers :
 *
 * 
 * This file is part of the authbucket/oauth2-php package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Perseids\OAuth2;

use Perseids\OAuth2\Entity\ModelManagerFactory;
use Perseids\OAuth2\Entity\UserRepository;

use SimpleUser\UserManager;

/*
use AuthBucket\OAuth2\Controller\AuthorizeController;
use AuthBucket\OAuth2\Controller\ClientController;
use AuthBucket\OAuth2\Controller\OAuth2Controller;
use AuthBucket\OAuth2\Controller\ScopeController;
use AuthBucket\OAuth2\EventListener\ExceptionListener;
use AuthBucket\OAuth2\GrantType\GrantTypeHandlerFactory;
use AuthBucket\OAuth2\ResourceType\ResourceTypeHandlerFactory;
use AuthBucket\OAuth2\ResponseType\ResponseTypeHandlerFactory;
use AuthBucket\OAuth2\Security\Authentication\Provider\ResourceProvider;
use AuthBucket\OAuth2\Security\Authentication\Provider\TokenProvider;
use AuthBucket\OAuth2\Security\Firewall\ResourceListener;
use AuthBucket\OAuth2\Security\Firewall\TokenListener;
use AuthBucket\OAuth2\TokenType\TokenTypeHandlerFactory;
use Silex\Application;
use Symfony\Component\HttpKernel\KernelEvents;
*/

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;


/**
 * OAuth2 service provider as plugin for Silex SecurityServiceProvider.
 *
 * @author Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 */
class OAuth2ServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    protected $em;
    protected $userManager;

    public function register(Application $app) {

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
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/authorize', 'authbucket_oauth2.oauth2_controller:authorizeAction')
            ->bind('api_oauth2_authorize');

        $controllers->post('/token', 'authbucket_oauth2.oauth2_controller:tokenAction')
            ->bind('api_oauth2_token');

        $controllers->match('/debug', 'authbucket_oauth2.oauth2_controller:debugAction')
            ->bind('api_oauth2_debug')
            ->method('GET|POST');

        $controllers->get('/cron', 'authbucket_oauth2.oauth2_controller:cronAction')
            ->bind('api_oauth2_cron');

        foreach (array('authorize', 'client', 'scope') as $type) {
            $controllers->post('/rest/'.$type.'.{_format}', 'authbucket_oauth2.'.$type.'_controller:createAction')
                ->bind('api_'.$type.'_create')
                ->assert('_format', 'json|xml');

            $controllers->get('/rest/'.$type.'/{id}.{_format}', 'authbucket_oauth2.'.$type.'_controller:readAction')
                ->bind('api_'.$type.'_read')
                ->assert('_format', 'json|xml');

            $controllers->put('/rest/'.$type.'/{id}.{_format}', 'authbucket_oauth2.'.$type.'_controller:updateAction')
                ->bind('api_'.$type.'_update')
                ->assert('_format', 'json|xml');

            $controllers->delete('/rest/'.$type.'/{id}.{_format}', 'authbucket_oauth2.'.$type.'_controller:deleteAction')
                ->bind('api_'.$type.'_delete')
                ->assert('_format', 'json|xml');

            $controllers->get('/rest/'.$type.'.{_format}', 'authbucket_oauth2.'.$type.'_controller:listAction')
                ->bind('api_'.$type.'_list')
                ->assert('_format', 'json|xml');
        }

        return $controllers;
    }

    public function boot(Application $app) {
        //$app['dispatcher']->addListener(KernelEvents::EXCEPTION, array($app['perseids_oauth2.exception_listener'], 'onKernelException'), -8);
    }
}
?>