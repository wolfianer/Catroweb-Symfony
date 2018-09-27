<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 22.09.18
 * Time: 18:06
 */

namespace Catrobat\AppBundle\Controller\Api\Model;
use OpenApi\Annotations as OA;
/**
 * Class IsOAuthModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class IsOAuthModel extends DefaultModel
{
  /**
   * @var $is_oauth_user boolean
   *
   * @OA\Property(
   *     title="is_oauth_user",
   *     description="Indicates if the user is a oauth user",
   *     example=false
   * )
   */
  private $is_oauth_user;
}