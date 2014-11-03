<?php

/**
 * This file is part of the authbucket/oauth2-php package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Perseids\OAuth2;

use AuthBucket\OAuth2\Exception\InvalidScopeException;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class APIController {

    public function userDetails(Request $request, Application $app) {
        $token = $app["security"]->getToken();

        $manager = $app["user.manager"];
        $user = $manager->loadUserByUsername($token->getUsername());

        return $app->json(array(
            "uid" => $user->getId(),
            "username" => $user->getUsername(),
            "email" => $user->getEmail(),
            "name" => $user->getName()
        ));
    }
}
