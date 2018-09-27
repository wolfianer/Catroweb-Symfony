<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 23.09.18
 * Time: 12:14
 */

namespace Catrobat\AppBundle\Controller\Api\Model;

use OpenApi\Annotations as OA;

/**
 * Class FacebookTokenErrorDetailsModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class FacebookTokenErrorDetailsModel extends FacebookTokenErrorModel
{
  /**
   * @OA\Property(
   *     title="details",
   *     description="Details of why a reason is invalid.",
   *     example="BBecause I am lactose intolerant :.(",
   * )
   * @var $details string
   *
   */
  private $details;
}