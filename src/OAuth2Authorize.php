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

use Perseids\OAuth2\OAuth2Controller;

use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
/**
 * OAuth2 service provider as plugin for Silex SecurityServiceProvider.
 *
 * @author Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 */
class OAuth2Authorize implements ServiceProviderInterface, ControllerProviderInterface
{
	public function register(Application $app) {
		$app['security.encoder.digest'] = $app->share(function ($app) {
			return new PlaintextPasswordEncoder();
		});
	}
    public function connect(Application $app)
    {
	    $controllers = $app['controllers_factory'];
	    $controllers->match('/authorize', 'perseids.oauth2.controller:authorizeAction')
            ->method('GET|POST')
	        ->bind('perseidsoauth.authorize');
        return $controllers;
    }
	public function boot(Application $app) {
        // Add twig template path.
        if (isset($app['twig.loader.filesystem'])) {
            $app['twig.loader.filesystem']->addPath(__DIR__ . '/views/', 'perseidsoauth');
        }
	}
}