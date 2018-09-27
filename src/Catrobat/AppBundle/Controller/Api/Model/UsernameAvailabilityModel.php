<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 22.09.18
 * Time: 18:20
 */

namespace Catrobat\AppBundle\Controller\Api\Model;

use OpenApi\Annotations as OA;

/**
 * Class UsernameAvailabilityModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class UsernameAvailabilityModel extends DefaultModel
{
  /**
   * @OA\Property(
   *     title="username_available",
   *     description="Boolean if the username is available",
   *     example=false,
   * )
   * @var $username_available boolean
   *
   */
  private $username_available;
}