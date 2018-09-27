<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 23.09.18
 * Time: 11:30
 */

namespace Catrobat\AppBundle\Controller\Api\Model;

use OpenApi\Annotations as OA;
/**
 * Class InternalErrorModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class InternalErrorModel extends DefaultModel
{
  /**
   * @OA\Property(
   *     title="Error Code",
   *     description="Displays if the email is already in use.",
   *     example=500,
   * )
   * @var $error_code integer
   *
   */
  private $error_code;

  /**
   * @OA\Property(
   *     title="error_description",
   *     description="Error Description",
   *     example=false,
   * )
   * @var $error_description string
   *
   */
  private $error_description;
}