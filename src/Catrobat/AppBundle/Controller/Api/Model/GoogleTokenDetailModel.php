<?php


namespace Catrobat\AppBundle\Controller\Api\Model;

use OpenApi\Annotations as OA;

/**
 * Class GoogleTokenDetailModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class GoogleTokenDetailModel extends GoogleTokenModel
{
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