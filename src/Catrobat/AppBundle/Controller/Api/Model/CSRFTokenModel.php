<?php

namespace Catrobat\AppBundle\Controller\Api\Model;
use OpenApi\Annotations as OA;

/**
 * Class CSRFTokenModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */

class CSRFTokenModel extends DefaultModel
{
  /**
   * @OA\Property(
   *     title="csrf_token",
   *     description="CSRF Token",
   *     example="ojemquc4c93m02cr329",
   * )
   * @var $csrf_token string
   *
   */
  private $csrf_token;
}