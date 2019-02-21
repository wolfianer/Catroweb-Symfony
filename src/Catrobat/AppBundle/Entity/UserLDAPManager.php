<?php

namespace Catrobat\AppBundle\Entity;

use Catrobat\AppBundle\Ldap\UserHydrator;
use Catrobat\AppBundle\Services\TokenGenerator;
use FR3D\LdapBundle\Driver\LdapDriverException;
use FR3D\LdapBundle\Ldap\LdapManager;
use FR3D\LdapBundle\Driver\LdapDriverInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Monolog\Logger;
use FR3D\LdapBundle\Model\LdapUserInterface;

/**
 * Class UserLDAPManager
 * @package Catrobat\AppBundle\Entity
 */
class UserLDAPManager extends LdapManager
{

  /**
   * @var
   */
  protected $role_mappings;

  /**
   * @var
   */
  protected $group_filter;

  /**
   * @var TokenGenerator
   */
  protected $tokenGenerator;

  /**
   * @var UserManager
   */
  protected $userManager;

  /**
   * @var Logger
   */
  protected $logger;

  /**
   * UserLDAPManager constructor.
   *
   * @param LdapDriverInterface $driver
   * @param UserHydrator        $userHydrator
   * @param UserManager         $userManager
   * @param array               $params
   * @param                     $role_mappings
   * @param                     $group_filter
   * @param                     $tokenGenerator
   * @param Logger              $logger
   */
  public function __construct(LdapDriverInterface $driver,
                              UserHydrator $userHydrator,
                              UserManager $userManager,
                              array $params, $role_mappings, $group_filter,
                              TokenGenerator $tokenGenerator, Logger $logger)
  {

    $this->userManager = $userManager;
    $this->role_mappings = $role_mappings;
    $this->group_filter = $group_filter;
    $this->logger = $logger;
    $this->tokenGenerator = $tokenGenerator;

    parent::__construct($driver, $userHydrator, $params);
  }

  /**
   * @param array $criteria
   *
   * @return bool|\FOS\UserBundle\Model\UserInterface|object|UserInterface|null
   * @throws \Exception
   */
  public function findUserBy(array $criteria)
  {
    /**
     * @var $user User
     */
    try
    {
      $filter = $this->buildFilter($criteria);
      $entries = $this->driver->search($this->params['baseDn'], $filter, $this->params['attributes']);
      if ($entries['count'] > 1)
      {
        throw new \Exception('This search can only return a single user');
      }

      if ($entries['count'] == 0)
      {
        return false;
      }

      // same Email-Address already in system?
      $sameEmailUser = $this->userManager->findOneBy([
        "email" => $entries[0]['mail'],
      ]);
      if ($sameEmailUser != null)
      {
        if ($sameEmailUser instanceof LdapUserInterface)
        {
          $sameEmailUser->setDn($entries[0]['dn']);
        }
        $this->userManager->updateUser($sameEmailUser);

        return $sameEmailUser;
      }

      $user = $this->hydrator->hydrate($entries[0]);
      $user->setUploadToken($this->tokenGenerator->generateToken());

      return $user;
    } catch (LdapDriverException $e)
    {
      $this->logger->addError("LDAP-Server not reachable?: " . $e->getMessage());

      return false;
    }
  }


  /**
   * @param UserInterface|User  $user
   * @param                     $password
   *
   * @return bool
   */
  public function bind(UserInterface $user, $password)
  {
    try
    {
      $filter = sprintf($this->group_filter, $user->getDn());
      $entries = $this->driver->search($this->params['baseDn'], $filter, [
        "cn",
      ]);
      $binding = $this->driver->bind($user, $password);
    } catch (LdapDriverException $e)
    {
      $this->logger->addError("LDAP-Server not reachable?: " . $e->getMessage());

      return false;
    }

    if ($binding)
    {
      /**
       * @var $user \Catrobat\AppBundle\Entity\User*
       */
      $user->setRealRoles([]);
      $user->setRoles([]);
      $roles = [];
      foreach ($entries as $entry)
      {
        $ldap_group_name = $entry["cn"][0];
        if ($role_to_add = array_search($ldap_group_name, $this->role_mappings))
        {
          array_push($roles, $role_to_add);
        }
      }
      $user->setRoles($roles);
    }

    return $binding;
  }
}