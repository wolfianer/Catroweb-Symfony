<?php

namespace Catrobat\AppBundle\Controller\Api\Model;

use OpenApi\Annotations as OA;

/**
 * Class DefaultModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class DefaultModel
{
  /**
   * @OA\Property(
   *     title="statusCode",
   *     description="Internal Status Code",
   *     example=200,
   * )
   * @var $statusCode integer
   *
   */
  private $statusCode;

}