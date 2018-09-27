<?php

namespace Catrobat\AppBundle\Controller\Api\Model;

use OpenApi\Annotations as OA;
/**
 * Class FacebookServerTokenInfoModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class GoogleLoginModel extends DefaultModel
{
  /**
   * @OA\Property(
   *     title="token",
   *     description="Upload token of given user.",
   *     example="3caf8e4134951a474594cccd9f84e474",
   * )
   * @var $token string
   *
   */
  private $token;

  /**
   * @OA\Property(
   *     title="username",
   *     description="Username of the user",
   *     example="Testuser",
   * )
   * @var $username_available string
   *
   */
  private $username;
}