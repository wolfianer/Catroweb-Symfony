<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 23.09.18
 * Time: 11:34
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
class FacebookProfileModel
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
   *     title="first_name",
   *     description="Displays the users first name",
   *     example="Max",
   * )
   * @var $error_description string
   *
   */
  private $first_name;

  /**
   * @OA\Property(
   *     title="last_name",
   *     description="Displays the users last name",
   *     example="Mustermann",
   * )
   * @var $error_description string
   *
   */
  private $last_name;

  /**
   * @OA\Property(
   *     title="username",
   *     description="Displays the username of the FB user",
   *     example="APITester",
   * )
   * @var $username string
   *
   */
  private $username;

  /**
   * @OA\Property(
   *     title="link",
   *     description="Displays the link to the profile of the FB user",
   *     example="http://facebook.com/whatever/is/written/here",
   * )
   * @var $link string
   *
   */
  private $link;

  /**
   * @OA\Property(
   *     title="locale",
   *     description="Displays locale of the FB user",
   *     example="de",
   * )
   * @var $locale string
   *
   */
  private $locale;

  /**
   * @OA\Property(
   *     title="Email",
   *     description="Displays email of the FB user",
   *     example="test@test.test",
   * )
   * @var $email string
   *
   */
  private $email;
}