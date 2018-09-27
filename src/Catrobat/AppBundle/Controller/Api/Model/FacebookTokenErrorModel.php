<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 23.09.18
 * Time: 12:06
 */

namespace Catrobat\AppBundle\Controller\Api\Model;
use OpenApi\Annotations as OA;

/**
 * Class FacebookTokenErrorModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class FacebookTokenErrorModel extends FacebookTokenModel
{
  /**
   * @OA\Property(
   *     title="reason",
   *     description="Reason why a token is invalid",
   *     example="Because i can not eat cookies </3",
   * )
   * @var $reason string
   *
   */
  private $reason;
}