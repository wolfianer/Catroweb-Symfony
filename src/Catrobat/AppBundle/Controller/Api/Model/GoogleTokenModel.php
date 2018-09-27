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
class GoogleTokenModel extends DefaultModel
{
  /**
   * @OA\Property(
   *     title="token_available",
   *     description="Displays if a token is valid.",
   *     example=false,
   * )
   * @var $token_available boolean
   *
   */
  private $token_available;
}