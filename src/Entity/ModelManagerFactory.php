<?php

/**
 * This file is part of the authbucket/oauth2-php package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Perseids\OAuth2\Entity;

use AuthBucket\OAuth2\Exception\ServerErrorException;
use AuthBucket\OAuth2\Model\ModelManagerFactoryInterface;
use AuthBucket\OAuth2\Model\ModelManagerInterface;
use Doctrine\ORM\EntityManager;

/**
 * OAuth2 model manager factory implemention.
 *
 * @author Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 */
class ModelManagerFactory implements ModelManagerFactoryInterface
{
    protected $managers;

    public function __construct(EntityManager $em, array $models = array())
    {
        $managers = array();
        foreach ($models as $type => $model) {
            $manager = $em->getRepository($model);
            if (!$manager instanceof ModelManagerInterface) {
                throw new ServerErrorException($message = array(
                    "error_description" => "The Manager for " . $model . " is not a ModelManagerInterface"
                ));
            }
            $managers[$type] = $manager;
        }

        $this->managers = $managers;
    }

    public function getModelManager($type)
    {
        if (!isset($this->managers[$type])) {
            throw new ServerErrorException(array("error_description" => "The manager for " . $type . " is not available"));
        }

        return $this->managers[$type];
    }
}
