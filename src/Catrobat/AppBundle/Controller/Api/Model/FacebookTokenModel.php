<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 23.09.18
 * Time: 12:12
 */

namespace Catrobat\AppBundle\Controller\Api\Model;

use OpenApi\Annotations as OA;

/**
 * Class FacebookTokenModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class FacebookTokenModel extends DefaultModel
{
  /**
   * @OA\Property(
   *     title="token_invalid",
   *     description="Displays if a token is invalid",
   *     example=false,
   * )
   * @var $token_invalid boolean
   *
   */
  private $token_invalid;
}