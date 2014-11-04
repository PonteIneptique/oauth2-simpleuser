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

use AuthBucket\OAuth2\Model\ScopeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Scope
 *
 * @ORM\Table(name="perseids_oauth2_scope")
 * @ORM\Entity(repositoryClass="Perseids\OAuth2\Entity\ScopeRepository")
 */
class Scope implements ScopeInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="scope", type="string", length=255)
     */
    protected $scope;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set scope
     *
     * @param string $scope
     *
     * @return Scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
