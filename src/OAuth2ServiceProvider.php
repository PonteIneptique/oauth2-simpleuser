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


use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;

use Perseids\OAuth2\Entity\ModelManagerFactory;
use Perseids\OAuth2\Entity\UserRepository;
use Perseids\OAuth2\OAuth2Controller;

/**
 * OAuth2 service provider as plugin for Silex SecurityServiceProvider.
 *
 * @author Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 */
class OAuth2ServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{

    public function register(Application $app) {
        $app['doctrine.orm.entity_manager'] = $app->share(function ($app) {
            $conn = $app['dbs']['default'];
            $em = $app['dbs.event_manager']['default'];

            $isDevMode = false;
            $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__.'/../Entity'), $isDevMode, null, null, false);

            return EntityManager::create($conn, $config, $em);
        });


        // User controller service.
        $app['perseids.oauth2.controller'] = $app->share(function ($app) {
            $controller = new OAuth2Controller();

            return $controller;
        });
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/authorize', 'authbucket_oauth2.oauth2_controller:authorizeAction')
            ->bind('api_oauth2_authorize');

        $controllers->match('/token', 'authbucket_oauth2.oauth2_controller:tokenAction')
            ->method('GET|POST')
            ->bind('api_oauth2_token');
/*
        $controllers->match('/debug', 'authbucket_oauth2.oauth2_controller:debugAction')
            ->bind('api_oauth2_debug')
            ->method('GET|POST');

        $controllers->get('/cron', 'authbucket_oauth2.oauth2_controller:cronAction')
            ->bind('api_oauth2_cron');
*/
        /*
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
        */

        return $controllers;
    }

    public function boot(Application $app) {
        if(!$app['user.manager']) {
            throw new \LogicException('There is no user.manager available from within the OAuth2ServiceProvider.');
        }
        if (!$app['user.tokenGenerator']) {
            // using RuntimeException crashes PHP?!
            throw new \LogicException('You must enable the ServiceController service provider to be able to use these routes.');
        }

    }
}
?>