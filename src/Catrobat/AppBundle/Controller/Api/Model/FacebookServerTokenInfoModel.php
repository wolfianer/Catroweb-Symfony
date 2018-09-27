<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 23.09.18
 * Time: 10:15
 */

namespace Catrobat\AppBundle\Controller\Api\Model;
use OpenApi\Annotations as OA;
/**
 * Class FacebookServerTokenInfoModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class FacebookServerTokenInfoModel extends DefaultModel
{
  /**
   * @OA\Property(
   *     title="token_available",
   *     description="Boolean if the token is available",
   *     example=true,
   * )
   * @var $username_available boolean
   *
   */
  private $token_available;

  /**
   * @OA\Property(
   *     title="username",
   *     description="Username of the user if available",
   *     example="Testuser",
   * )
   * @var $username_available string
   *
   */
  private $username;

  /**
   * @OA\Property(
   *     title="email",
   *     description="Email of the user if available",
   *     example="test@test.test",
   * )
   * @var $username_available string
   *
   */
  private $email;
}