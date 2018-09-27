<?php

namespace Catrobat\AppBundle\Controller\Api\Model;


use OpenApi\Annotations as OA;

/**
 * Class GoogleAppIDModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class GoogleAppIDModel extends DefaultModel
{
  /**
   * @OA\Property(
   *     title="gplus_appid",
   *     description="Internal Status Code",
   *     example="1234567890asdfapofiajfapoijfef.google.com",
   * )
   * @var $gplus_appid string
   *
   */
  private $gplus_appid;
}