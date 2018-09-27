<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 22.09.18
 * Time: 18:15
 */

namespace Catrobat\AppBundle\Controller\Api\Model;
use OpenApi\Annotations as OA;
/**
 * Class EMailAvailabilityModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class EMailAvailabilityModel extends DefaultModel
{
  /**
   * @OA\Property(
   *     title="email_available",
   *     description="Displays if the email is already in use.",
   *     example=false,
   * )
   * @var $email_available boolean
   *
   */
  private $email_available;
}