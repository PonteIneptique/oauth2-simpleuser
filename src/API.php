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

use Perseids\OAuth2\APIController;

/**
 * OAuth2 service provider as plugin for Silex SecurityServiceProvider.
 *
 * @author Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 */
class API implements ServiceProviderInterface, ControllerProviderInterface
{
	public function register(Application $app) {
        // User controller service.
        $app['perseids.api.controller'] = $app->share(function ($app) {
            $controller = new APIController();
            return $controller;
        });
	}
    public function connect(Application $app)
    {
	    $controllers = $app['controllers_factory'];
	    $controllers->match('/user', 'perseids.api.controller:userDetails')
            ->method('GET|POST')
	        ->bind('perseids.api.userdetails');
        return $controllers;
    }
	public function boot(Application $app) {
	}
}