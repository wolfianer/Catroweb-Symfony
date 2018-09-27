<?php


namespace Catrobat\AppBundle\Controller\Api\Model;
use OpenApi\Annotations as OA;
/**
 * Class LoginFacebookModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class LoginFacebookModel extends DefaultModel
{
  /**
   * @OA\Property(
   *     title="token",
   *     description="UploadToken",
   *     example="3caf8e4134951a474594cccd9f84e474",
   * )
   * @var $username_available boolean
   *
   */
  private $token;

  /**
   * @OA\Property(
   *     title="username",
   *     description="Name of the user.",
   *     example="APITestuser",
   * )
   * @var $username_available boolean
   *
   */
  private $username;
}