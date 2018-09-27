<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 24.09.18
 * Time: 08:06
 */

namespace Catrobat\AppBundle\Controller\Api\Model;

use OpenApi\Annotations as OA;

/**
 * Class GoogleProfileModel
 * @package Catrobat\AppBundle\Controller\Api\Model
 *
 * @OA\Schema(
 * )
 */
class GoogleProfileModel extends DefaultModel
{
  /**
   * @OA\Property(
   *     title="ID",
   *     description="Displays the ID",
   *     example=1,
   * )
   * @var $id integer
   *
   */
  private $id;

  /**
   * @OA\Property(
   *     title="displayName",
   *     description="Displays the users name",
   *     example="Max",
   * )
   * @var $display_name string
   *
   */
  private $display_name;

  /**
   * @OA\Property(
   *     title="imageUrl",
   *     description="Displays the users image url",
   *     example="http://google.com/whatever/is/written/here",
   * )
   * @var $imageUrl string
   *
   */
  private $imageUrl;

  /**
   * @OA\Property(
   *     title="profileUrl",
   *     description="Displays the users profile url",
   *     example="http://google.com/whatever/is/written/here",
   * )
   * @var $profileUrl string
   *
   */
  private $profileUrl;
}