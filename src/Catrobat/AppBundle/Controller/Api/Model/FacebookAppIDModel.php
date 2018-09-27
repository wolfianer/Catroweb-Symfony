<?php

namespace Catrobat\AppBundle\Controller\Api\Model;

use OpenApi\Annotations as OA;

/**
 * Class FacebookAppIDModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class FacebookAppIDModel extends DefaultModel
{
  /**
   * @OA\Property(
   *     title="fb_appid",
   *     description="Internal Status Code",
   *     example=1234567890,
   * )
   * @var $fb_appid integer
   *
   */
  private $fb_appid;
}