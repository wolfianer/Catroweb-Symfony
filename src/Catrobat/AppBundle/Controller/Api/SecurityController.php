<?php

namespace Catrobat\AppBundle\Controller\Api;

//use Assetic\Exception;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\UserLDAPManager;
use Catrobat\AppBundle\Services\OAuthService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Services\TokenGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\StatusCode;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\AppBundle\Requests\LoginUserRequest;
use Catrobat\AppBundle\Requests\CreateUserRequest;
use Catrobat\AppBundle\Requests\CreateOAuthUserRequest;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Catrobat\AppBundle\Security\UserAuthenticator;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use OpenApi\Annotations as OA;

/**
  @OA\Tag(
   *     name="Security",
   *     description="Everything about your security endpoints",
   * )
 **/
class SecurityController extends Controller
{
  /**
   * @Route("/api/checkToken/check.json", name="catrobat_api_check_token", defaults={"_format": "json"},
   *                                      methods={"POST"})
   */

  /**
   * @OA\Post(
   *     path="/api/checkToken/check.json",
   *     tags={"Security"},
   *     summary="Check Token",
   *     description="Checks if a sent token is okay.",
   *     operationId="checkTokenAction",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="username",
   *                   description="Name of the user.",
   *                   type="string",
   *               ),
   *               @OA\Property(
   *                   property="token",
   *                   description="Upload Token of the user",
   *                   type="string",
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=200,
   *         description="successful operation",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Authorization error",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function checkTokenAction()
  {
    return JsonResponse::create([
      'statusCode'        => StatusCode::OK,
      'answer'            => $this->trans('success.token'),
      'preHeaderMessages' => "  \n",
    ]);
  }

  /**
   * @Route("/api/register/Register.json", name="catrobat_api_register", options={"expose"=true}, defaults={"_format":
   *                                       "json"}, methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   *
   * @OA\Post(
   *     path="/api/register/Register.json",
   *     tags={"Security"},
   *     summary="Register new User",
   *     description="Registers a new PocketCode user.",
   *     operationId="registerNativeUser",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="registrationUsername",
   *                   description="Name of the user.",
   *                   type="string"
   *               ),
   *               @OA\Property(
   *                   property="registrationPassword",
   *                   description="Password of the user.",
   *                   type="string"
   *               ),
   *               @OA\Property(
   *                   property="registrationEmail",
   *                   description="Email address of the user.",
   *                   type="string"
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=201,
   *         description="Registration successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Authorization error (user mistake)",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function registerNativeUser(Request $request)
  {
    /**
     * @var $user_manager          UserManager
     * @var $user                  User
     * @var $token_generator       TokenGenerator
     * @var $validator             Validator\ValidatorInterface
     * @var $violations            ConstraintViolation
     */
    $user_manager = $this->get("usermanager");
    $token_generator = $this->get("tokengenerator");
    $validator = $this->get("validator");

    $retArray = [];

    $create_request = new CreateUserRequest($request);
    $violations = $validator->validate($create_request);
    foreach ($violations as $violation)
    {
      $retArray['statusCode'] = StatusCode::REGISTRATION_ERROR;
      switch ($violation->getMessageTemplate())
      {
        case 'errors.username.blank':
          $retArray['statusCode'] = StatusCode::USER_USERNAME_MISSING;
          break;
        case 'errors.username.invalid':
          $retArray['statusCode'] = StatusCode::USER_USERNAME_INVALID;
          break;
        case 'errors.password.blank':
          $retArray['statusCode'] = StatusCode::USER_PASSWORD_MISSING;
          break;
        case 'errors.password.short':
          $retArray['statusCode'] = StatusCode::USER_PASSWORD_TOO_SHORT;
          break;
        case 'errors.email.blank':
          $retArray['statusCode'] = StatusCode::USER_EMAIL_MISSING;
          break;
        case 'errors.email.invalid':
          $retArray['statusCode'] = StatusCode::USER_EMAIL_INVALID;
          break;
      }
      $retArray['answer'] = $this->trans($violation->getMessageTemplate(), $violation->getParameters());
      break;
    }

    $httpResponse = Response::HTTP_BAD_REQUEST;
    if (count($violations) === 0)
    {
      if ($user_manager->findUserByEmail($create_request->mail) !== null)
      {
        $retArray['statusCode'] = StatusCode::USER_ADD_EMAIL_EXISTS;
        $retArray['answer'] = $this->trans("errors.email.exists");
      }
      else
      {
        if ($user_manager->findUserByUsername($create_request->username) !== null)
        {
          $retArray['statusCode'] = StatusCode::USER_ADD_USERNAME_EXISTS;
          $retArray['answer'] = $this->trans("errors.username.exists");
        }
        else
        {
          $user = $user_manager->createUser();
          $user->setUsername($create_request->username);
          $user->setEmail($create_request->mail);
          $user->setPlainPassword($create_request->password);
          $user->setEnabled(true);
          $user->setUploadToken($token_generator->generateToken());
          $user_manager->updateUser($user);

          $retArray['statusCode'] = Response::HTTP_CREATED;
          $retArray['answer'] = $this->trans("success.registration");
          $retArray['token'] = $user->getUploadToken();
          $httpResponse = Response::HTTP_CREATED;
        }
      }
    }

    return JsonResponse::create($retArray, $httpResponse);
  }

  /**
   * @Route("/api/login/Login.json", name="catrobat_api_login", options={"expose"=true}, defaults={"_format": "json"},
   *                                 methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   *
   * @OA\Post(
   *     path="/api/login/Login.json",
   *     tags={"Security"},
   *     summary="To log in a user",
   *     description="Log in a user",
   *     operationId="loginNativeUser",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="loginUsername",
   *                   description="Name of the user.",
   *                   type="string"
   *               ),
   *               @OA\Property(
   *                   property="loginPassword",
   *                   description="Password of the user.",
   *                   type="string"
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=200,
   *         description="Login successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/LoginModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Authorization error (user mistake)",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function loginNativeUser(Request $request)
  {
    /**
     * @var $user_manager         UserManager
     * @var $user                 User
     * @var $token_generator      TokenGenerator
     * @var $validator            Validator\ValidatorInterface
     * @var $violations           ConstraintViolation
     */

    $user_manager = $this->get("usermanager");
    $token_generator = $this->get("tokengenerator");
    $validator = $this->get("validator");
    $retArray = [];

    $login_request = new LoginUserRequest($request);
    $violations = $validator->validate($login_request);
    foreach ($violations as $violation)
    {
      $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
      switch ($violation->getMessageTemplate())
      {
        case 'errors.password.blank':
          $retArray['statusCode'] = StatusCode::USER_PASSWORD_MISSING;
          break;
        case 'errors.password.short':
          $retArray['statusCode'] = StatusCode::USER_PASSWORD_TOO_SHORT;
          break;
        case 'errors.username.blank':
          $retArray['statusCode'] = StatusCode::USER_USERNAME_MISSING;
          break;
      }
      $retArray['answer'] = $this->trans($violation->getMessageTemplate(), $violation->getParameters());
      break;
    }

    if (count($violations) > 0)
    {
      $httpResponse = Response::HTTP_BAD_REQUEST;

      return JsonResponse::create($retArray, $httpResponse);
    }

    if (count($violations) === 0)
    {
      $username = $request->request->get('loginUsername');
      $password = $request->request->get('loginPassword');

      $user = $user_manager->findUserByUsername($username);

      if (!$user)
      {
        return $this->signInLdapUser($request, $retArray);
      }
      else
      {
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $correct_pass = $user_manager->isPasswordValid($user, $password, $encoder);
        if ($correct_pass)
        {
          $retArray['statusCode'] = StatusCode::OK;
          $user->setUploadToken($token_generator->generateToken());
          $retArray['token'] = $user->getUploadToken();
          $retArray['email'] = $user->getEmail();
          $retArray['nolbUser'] = $user->getNolbUser();
          $user_manager->updateUser($user);
        }
        else
        {
          return $this->signInLdapUser($request, $retArray);
        }
      }
    }

    return JsonResponse::create($retArray);
  }

  private function signInLdapUser($request, &$retArray)
  {
    /**
     * @var $authenticator UserAuthenticator
     */
    $authenticator = $this->get('user_authenticator');
    $token = null;
    $username = $request->request->get('loginUsername');

    try
    {
      $token = $authenticator->authenticate($username, $request->request->get('loginPassword'));
      $retArray['statusCode'] = StatusCode::OK;
      $retArray['token'] = $token->getUser()->getUploadToken();
      $httpResponse = Response::HTTP_OK;

    } catch (UsernameNotFoundException $exception)
    {
      $user = null;
      $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
      $retArray['answer'] = $this->trans('errors.login');
      $httpResponse = Response::HTTP_BAD_REQUEST;
    } catch (AuthenticationException $exception)
    {
      $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
      $retArray['answer'] = $this->trans('errors.login');
      $httpResponse = Response::HTTP_BAD_REQUEST;
    }

    return JsonResponse::create($retArray, $httpResponse);
  }

  /**
   * @Route("/api/IsOAuthUser/IsOAuthUser.json", name="catrobat_is_oauth_user", options={"expose"=true},
   *                                             defaults={"_format": "json"}, methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   *
   * @OA\Post(
   *     path="/api/IsOAuthUser/IsOAuthUser.json",
   *     tags={"Security"},
   *     summary="Check if user is an OAuthUser",
   *     description="Check if user is an OAuthUser",
   *     operationId="isOAuthUser",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="username_email",
   *                   description="Name or email of the user",
   *                   type="string"
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=200,
   *         description="Response successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/IsOAuthModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function isOAuthUser(Request $request)
  {
    return $this->getOAuthService()->isOAuthUser($request);
  }

  /**
   * @Route("/api/EMailAvailable/EMailAvailable.json", name="catrobat_oauth_login_email_available",
   *                                                   options={"expose"=true}, defaults={"_format": "json"},
   *                                                   methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   *
   * @OA\Post(
   *     path="/api/EMailAvailable/EMailAvailable.json",
   *     tags={"Security"},
   *     summary="Check if an email is already in use.",
   *     description="Check if an email is already in use.",
   *     operationId="checkEMailAvailable",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="email",
   *                   description="Email of the user",
   *                   type="string"
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/EMailAvailabilityModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function checkEMailAvailable(Request $request)
  {
    return $this->getOAuthService()->checkEMailAvailable($request);
  }

  /**
   * @Route("/api/UsernameAvailable/UsernameAvailable.json", name="catrobat_oauth_login_username_available",
   *                                                         options={"expose"=true}, defaults={"_format": "json"},
   *                                                         methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   *
   * @OA\Post(
   *     path="/api/UsernameAvailable/UsernameAvailable.json",
   *     tags={"Security"},
   *     summary="Check if a username is available.",
   *     description="Check if a username is available.",
   *     operationId="checkUserNameAvailable",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="username",
   *                   description="Username to be checked!",
   *                   type="string"
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/UsernameAvailabilityModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function checkUserNameAvailable(Request $request)
  {
    return $this->getOAuthService()->checkUserNameAvailable($request);
  }

  /**
   * @Route("/api/FacebookServerTokenAvailable/FacebookServerTokenAvailable.json", name="catrobat_oauth_login_facebook_servertoken_available",
   *                                                                               options={"expose"=true},
   *                                                                               defaults={"_format": "json"},
   *                                                                               methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   * @OA\Post(
   *     path="/api/FacebookServerTokenAvailable/FacebookServerTokenAvailable.json",
   *     tags={"Security"},
   *     summary="Check if we have a FacebookUID available.",
   *     description="Check if we have a FacebookUID available.",
   *     operationId="checkFacebookServerTokenAvailable",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="facebookUid",
   *                   description="FacebookUID to be checked!",
   *                   type="string"
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/FacebookServerTokenInfoModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function checkFacebookServerTokenAvailable(Request $request)
  {
    return $this->getOAuthService()->checkFacebookServerTokenAvailable($request);
  }

  /**
   * @Route("/api/exchangeFacebookToken/exchangeFacebookToken.json", name="catrobat_oauth_login_facebook_token",
   *                                                                 options={"expose"=true}, defaults={"_format":
   *                                                                 "json"}, methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   * @OA\Post(
   *     path="/api/exchangeFacebookToken/exchangeFacebookToken.json",
   *     tags={"Security"},
   *     summary="Check if token exchnage with FB works",
   *     description="Check if token exchnage with FB works",
   *     operationId="exchangeFacebookTokenAction",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="client_token",
   *                   description="Token of the user.",
   *                   type="string"
   *               ),
   *               @OA\Property(
   *                   property="state",
   *                   description="Request state.",
   *                   type="string"
   *               ),
   *               @OA\Property(
   *                   property="username",
   *                   description="Name of the user to be logged in!",
   *                   type="string"
   *               ),
   *               @OA\Property(
   *                   property="id",
   *                   description="facebookUID",
   *                   type="integer"
   *               ),
   *               @OA\Property(
   *                   property="email",
   *                   description="Email of the user",
   *                   type="string"
   *               ),
   *               @OA\Property(
   *                   property="locale",
   *                   description="locale of the user",
   *                   type="string"
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=201,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *   @OA\Response(
   *         response=400,
   *         description="Bad Request!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *   @OA\Response(
   *         response=401,
   *         description="Authorization Error!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *   @OA\Response(
   *         response=500,
   *         description="Internal Server Error",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function exchangeFacebookTokenAction(Request $request)
  {
    return $this->getOAuthService()->exchangeFacebookTokenAction($request);
  }

  /**
   * @Route("/api/loginWithFacebook/loginWithFacebook.json", name="catrobat_oauth_login_facebook",
   *                                                         options={"expose"=true}, defaults={"_format": "json"},
   *                                                         methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   * @OA\Post(
   *     path="/api/loginWithFacebook/loginWithFacebook.json",
   *     tags={"Security"},
   *     summary="Logs in a Facebook user and gives back an upload token.",
   *     description="Check if the user exists and if it is a FB user and returns the UploadToken for the user",
   *     operationId="checkFacebookServerTokenAvailable",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="username",
   *                   description="Name of the user to be logged in!",
   *                   type="string"
   *               ),
   *               @OA\Property(
   *                   property="id",
   *                   description="facebookUID",
   *                   type="integer"
   *               ),
   *               @OA\Property(
   *                   property="email",
   *                   description="Email of the user",
   *                   type="string"
   *               ),
   *               @OA\Property(
   *                   property="locale",
   *                   description="locale of the user",
   *                   type="string"
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/LoginFacebookModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="User connection successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/LoginFacebookModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function loginWithFacebookAction(Request $request)
  {
    return $this->getOAuthService()->loginWithFacebookAction($request);
  }

  /**
   * @Route("/api/getFacebookUserInfo/getFacebookUserInfo.json", name="catrobat_facebook_userinfo",
   *                                                             options={"expose"=true}, defaults={"_format": "json"},
   *                                                             methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   * @OA\Post(
   *     path="/api/getFacebookUserInfo/getFacebookUserInfo.json",
   *     tags={"Security"},
   *     summary="Gives back the profile information for a FB user.",
   *     description="Gives back the profile information for a FB user.",
   *     operationId="getFacebookUserProfileInfo",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="id",
   *                   description="facebookUid",
   *                   type="string",
   *               ),
   *               @OA\Property(
   *                   property="token",
   *                   description="Already known facebook access token",
   *                   type="string"
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/FacebookProfileModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Internal Server Error",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/InternalErrorModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function getFacebookUserProfileInfo(Request $request)
  {
    return $this->getOAuthService()->getFacebookUserProfileInfo($request);
  }

  /**
   * @Route("/api/checkFacebookServerTokenValidity/checkFacebookServerTokenValidity.json", name="catrobat_oauth_facebook_server_token_validity",
   *                                                                                       options={"expose"=true},
   *                                                                                       defaults={"_format":
   *                                                                                       "json"}, methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   * @OA\Post(
   *     path="/api/checkFacebookServerTokenValidity/checkFacebookServerTokenValidity.json",
   *     tags={"Security"},
   *     summary="Validate if a token is good or not.",
   *     description="Gives you information if a token is valid or not. In some cases also gives you details why it is not valid.",
   *     operationId="getFacebookUserProfileInfo",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="id",
   *                   description="facebookUid",
   *                   type="string",
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/FacebookTokenModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Token Authorization Error!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/FacebookTokenErrorModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=418,
   *         description="Token Error, find details in error!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/FacebookTokenErrorDetailsModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Internal Server Error",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/FacebookTokenErrorModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function isFacebookServerAccessTokenValid(Request $request)
  {
    return $this->getOAuthService()->isFacebookServerAccessTokenValid($request);
  }

  /**
   * @Route("/api/GoogleServerTokenAvailable/GoogleServerTokenAvailable.json", name="catrobat_oauth_login_google_servertoken_available",
   *                                                                           options={"expose"=true},
   *                                                                           defaults={"_format": "json"},
   *                                                                           methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   * @OA\Post(
   *     path="/api/GoogleServerTokenAvailable/GoogleServerTokenAvailable.json",
   *     tags={"Security"},
   *     summary="Checks if given ID is GPlus ID.",
   *     description="Checks if the given ID correlates to a user. If so gives more info about the user.",
   *     operationId="checkGoogleServerTokenAvailable",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="id",
   *                   description="gplusUid",
   *                   type="string",
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/GoogleTokenDetailModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Token not assoicated with any user.",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/GoogleTokenModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function checkGoogleServerTokenAvailable(Request $request)
  {
    return $this->getOAuthService()->checkGoogleServerTokenAvailable($request);
  }

  /**
   * @Route("/api/exchangeGoogleCode/exchangeGoogleCode.json", name="catrobat_oauth_login_google_code",
   *                                                           options={"expose"=true}, defaults={"_format": "json"},
   *                                                           methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   * @OA\Post(
   *     path="/api/exchangeGoogleCodeAction/exchangeGoogleCodeAction.json",
   *     tags={"Security"},
   *     summary="Check if token exchange with Google works",
   *     description="Check if token exchange with Google works",
   *     operationId="exchangeGoogleCodeAction",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="id_token",
   *                   description="Token of the user.",
   *                   type="string"
   *               ),
   *               @OA\Property(
   *                   property="'username'",
   *                   description="The name of the user",
   *                   type="string"
   *               )
   *           )
   *       )
   *   ),
   *    @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Creation successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *   @OA\Response(
   *         response=400,
   *         description="Bad Request!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *   @OA\Response(
   *         response=401,
   *         description="Authorization Error!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *   @OA\Response(
   *         response=500,
   *         description="Internal Server Error",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function exchangeGoogleCodeAction(Request $request)
  {
    return $this->getOAuthService()->exchangeGoogleCodeAction($request);
  }

  /**
   * @Route("/api/loginWithGoogle/loginWithGoogle.json", name="catrobat_oauth_login_google", options={"expose"=true},
   *                                                     defaults={"_format": "json"}, methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   * @OA\Post(
   *     path="/api/loginWithGoogle/loginWithGoogle.json",
   *     tags={"Security"},
   *     summary="Log in a user via G+",
   *     description="Log in a user via G+. If the user was not registered via G+ the account will be connected to it.",
   *     operationId="loginWithGoogleAction",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="id",
   *                   description="gplusUid",
   *                   type="string",
   *               ),
   *               @OA\Property(
   *                   property="username",
   *                   description="Name of the user",
   *                   type="string",
   *               ),
   *               @OA\Property(
   *                   property="email",
   *                   description="Email of the user",
   *                   type="string",
   *               ),
   *               @OA\Property(
   *                   property="locale",
   *                   description="Locale of the user",
   *                   type="string",
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/GoogleLoginModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Request successful and account connected",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/GoogleLoginModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Token not assoicated with any user.",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function loginWithGoogleAction(Request $request)
  {
    return $this->getOAuthService()->loginWithGoogleAction($request);
  }

  /**
   * @Route("/api/getGoogleUserInfo/getGoogleUserInfo.json", name="catrobat_google_userinfo", options={"expose"=true},
   *                                                         defaults={"_format": "json"}, methods={"POST"})
   *
   * @param $request Request
   *
   * @return JsonResponse
   *
   * @OA\Post(
   *     path="/api/getGoogleUserInfo/getGoogleUserInfo.json",
   *     tags={"Security"},
   *     summary="Get G+ Profile",
   *     description="Get the profile Information of a G+ User",
   *     operationId="loginWithGoogleAction",
   *   @OA\RequestBody(
   *       required=true,
   *       @OA\MediaType(
   *           mediaType="multipart/form-data",
   *           @OA\Schema(
   *               type="object",
   *               @OA\Property(
   *                   property="id",
   *                   description="gplusUid",
   *                   type="string",
   *               ),
   *           )
   *       )
   *   ),
   *     @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/GoogleProfileModel")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Invalid ID.",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/MessageModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function getGoogleUserProfileInfo(Request $request)
  {
    return $this->getOAuthService()->getGoogleUserProfileInfo($request);
  }

  /**
   * @Route("/api/loginWithTokenAndRedirect/loginWithTokenAndRedirect", name="catrobat_oauth_login_redirect",
   *                                                                    options={"expose"=true}, methods={"POST"})
   */
  public function loginWithTokenAndRedirectAction(Request $request)
  {
    return $this->getOAuthService()->loginWithTokenAndRedirectAction($request);
  }

  /**
   * @Route("/api/getFacebookAppId/getFacebookAppId.json", name="catrobat_oauth_login_get_facebook_appid",
   *                                                       options={"expose"=true}, defaults={"_format": "json"},
   *                                                       methods={"GET"})
   *
   * @return JsonResponse
   *
   * @OA\Get(
   *     path="/api/getFacebookAppId/getFacebookAppId.json",
   *     tags={"Security"},
   *     summary="Get PocketCode Web Facebook APP ID",
   *     description="Get PocketCode Web Facebook APP ID",
   *     operationId="getFacebookAppId",
   *     @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/FacebookAppIDModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function getFacebookAppId()
  {
    $retArray = [];
    $retArray['statusCode'] = Response::HTTP_OK;
    $retArray['fb_appid'] = $this->container->getParameter('facebook_app_id');

    return JsonResponse::create($retArray, Response::HTTP_OK);
  }

  /**
   * @Route("/api/getGoogleAppId/getGoogleAppId.json", name="catrobat_oauth_login_get_google_appid",
   *                                                   options={"expose"=true}, defaults={"_format": "json"},
   *                                                   methods={"GET"})
   *
   * @return JsonResponse
   *
   * @OA\Get(
   *     path="/api/getGoogleAppId/getGoogleAppId.json",
   *     tags={"Security"},
   *     summary="Get PocketCode Web Google APP ID",
   *     description="Get PocketCode Web Google APP ID",
   *     operationId="getGoogleAppId",
   *     @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/GoogleAppIDModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function getGoogleAppId()
  {
    $retArray = [];
    $retArray['gplus_appid'] = $this->container->getParameter('google_app_id');

    return JsonResponse::create($retArray);
  }

  /**
   * @Route("/api/generateCsrfToken/generateCsrfToken.json", name="catrobat_oauth_register_get_csrftoken",
   *                                                         options={"expose"=true}, defaults={"_format": "json"},
   *                                                         methods={"GET"})
   *
   * @return JsonResponse
   *
   * @OA\Get(
   *     path="/api/generateCsrfToken/generateCsrfToken.json",
   *     tags={"Security"},
   *     summary="Get CSRF Token",
   *     description="Get CSRF Token",
   *     operationId="generateCsrfToken",
   *     @OA\Response(
   *         response=200,
   *         description="Request successful!",
   *         @OA\JsonContent(
   *            type="array",
   *            @OA\Items(ref="#/components/schemas/CSRFTokenModel")
   *         )
   *     ),
   *     deprecated=false
   * )
   */
  public function generateCsrfToken()
  {
    $retArray = [];
    $retArray['statusCode'] = Response::HTTP_OK;
    $retArray['csrf_token'] = $this->container->get('security.csrf.token_manager')->getToken('authenticate')->getValue();

    return JsonResponse::create($retArray, Response::HTTP_OK);
  }

  /**
   * @Route("/api/deleteOAuthUserAccounts/deleteOAuthUserAccounts.json", name="catrobat_oauth_delete_testusers",
   *                                                                     options={"expose"=true}, defaults={"_format":
   *                                                                     "json"}, methods={"GET"})
   */
  public function deleteOAuthTestUserAccounts()
  {
    return $this->getOAuthService()->deleteOAuthTestUserAccounts();
  }

  /**
   * @return object
   */
  private function getOAuthService()
  {

    return $this->get("oauth_service");
  }

  private function trans($message, $parameters = [])
  {
    return $this->get('translator')->trans($message, $parameters, 'catroweb');
  }
}
