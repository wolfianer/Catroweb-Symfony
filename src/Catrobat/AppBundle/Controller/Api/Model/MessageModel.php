<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 22.09.18
 * Time: 17:58
 */

namespace Catrobat\AppBundle\Controller\Api\Model;
use OpenApi\Annotations as OA;
/**
 * Class MessageModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class MessageModel extends DefaultModel
{
  /**
   * @var $message string
   *
   * @OA\Property(
   *     title="message",
   *     description="Responsemessage",
   *     example="This is a very nice message!"
   * )
   */
  private $message;
}