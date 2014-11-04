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

class OAuth2Controller {

    public function authorizeAction(Request $request, Application $app) {
        // We only handle non-authorized scope here.
        // 
        try {
            return $app['authbucket_oauth2.oauth2_controller']->authorizeAction($request);
        } catch (InvalidScopeException $exception) {
            $message = unserialize($exception->getMessage());
            if ($message['error_description'] !== 'The requested scope is invalid.') {
                throw $exception;
            }
        }

        // Fetch parameters, which already checked.
        $clientId = $request->query->get('client_id');
        $username = $app['security']->getToken()->getUser()->getUsername();
        $scope = preg_split('/\s+/', $request->query->get('scope', ''));

        // Create form.
        $form = $app['form.factory']->createBuilder('form')->getForm();
        $form->handleRequest($request);

        // Save authorized scope if submitted by POST.
        if ($form->isValid()) {
            $modelManagerFactory = $app['authbucket_oauth2.model_manager.factory'];
            $authorizeManager = $modelManagerFactory->getModelManager('authorize');

            // Update existing authorization if possible, else create new.
            $authorize = $authorizeManager->readModelOneBy(array(
                'clientId' => $clientId,
                'username' => $username,
            ));
            if ($authorize === null) {
                $class = $authorizeManager->getClassName();
                $authorize = new $class();
                $authorize->setClientId($clientId)
                    ->setUsername($username)
                    ->setScope((array) $scope);
                $authorize = $authorizeManager->createModel($authorize);
            } else {
                $authorize->setClientId($clientId)
                    ->setUsername($username)
                    ->setScope(array_merge((array) $authorize->getScope(), $scope));
                $authorizeManager->updateModel($authorize);
            }

            // Back to this path, with original GET parameters.
            return $app->redirect($request->getRequestUri());
        }

        // Display the form.
        $authorizationRequest = $request->query->all();

        /*
         * Display values
         *     We get nicer value to show through our modelManageer
         */
        $scopes = array();
        $user = $app["user.manager"]->getCurrentUser()->getName();
        $client_name = $clientId;
        $client_description = "";

        if(count($scope) > 0) {
            $modelManagerFactory = $app['authbucket_oauth2.model_manager.factory'];
            $scopeManager = $modelManagerFactory->getModelManager("scope");

            foreach($scope as $scopeTitle) {
                $temp = $scopeManager->readModelOneBy(array(
                    "scope" => $scopeTitle
                ));
                $scopes[] = $temp->getDescription();

            }


            $clientManager = $modelManagerFactory->getModelManager("client");
            $client = $clientManager->readModelOneBy(array(
                "clientId" => $clientId
            ));
            $client_name = $client->getName();
            $client_description = $client->getDescription();
        }



        return $app['twig']->render('@perseidsoauth/authorize.twig', array(
            'client_name' => $client_name,
            'client_description' => $client_description,
            'username' => $user,
            'scopes' => $scopes,
            'form' => $form->createView()
        ));
    }
}
