<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 22.09.18
 * Time: 17:50
 */

namespace Catrobat\AppBundle\Controller\Api\Model;
use OpenApi\Annotations as OA;
/**
 * Class LoginModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class LoginModel extends DefaultModel
{
  /**
   * @var $token string
   *
   * @OA\Property(
   *     title="token",
   *     description="Uploadtoken",
   *     example="3caf8e4134951a474594cccd9f84e474"
   * )
   */
  private $token;

  /**
   * @var $email string
   *
   * @OA\Property(
   *     title="email",
   *     description="Email of the User",
   *     example="test@test.test"
   * )
   */
  private $email;

  /**
   * @var $nolb boolean
   *
   * @OA\Property(
   *     title="nolb",
   *     description="Is User a Nolb User",
   *     example="false"
   * )
   */
  private $nolb;
}