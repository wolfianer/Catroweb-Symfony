<?php

namespace App\Catrobat\Controller\Api;

use _HumbugBox3ab8cff0fda0\___PHPSTORM_HELPERS\this;
use App\Catrobat\Events\ReportInsertEvent;
use App\Catrobat\StatusCode;
use App\Entity\Program;
use App\Entity\ProgramInappropriateReport;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use App\Utils\TimeUtils;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="report_controller")
 */
class ReportController extends AbstractController
{
  private UserManager $user_manager;
  private ProgramManager $program_manager;
  private TranslatorInterface $translator;
  private EventDispatcherInterface $event_dispatcher;

  private float $report_sensitivity = 2; // should be in a config file, the lower this value is, the faster a report will have consequences

  public function __construct(UserManager $user_manager, ProgramManager $program_manager,
                              TranslatorInterface $translator, EventDispatcherInterface $event_dispatcher)
  {
    $this->user_manager = $user_manager;
    $this->program_manager = $program_manager;
    $this->translator = $translator;
    $this->event_dispatcher = $event_dispatcher;
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Doctrine\ORM\NoResultException
   * @throws \Exception
   * @deprecated
   *
   * @Route("/api/reportProject/reportProject.json", name="catrobat_api_report_program",
   * defaults={"_format": "json"}, methods={"POST", "GET"})
   */
  public function reportProgramAction(Request $request): JsonResponse
  {
    /* @var $program Program */
    /* @var $user User */

    $entity_manager = $this->getDoctrine()->getManager();

    $response = [];
    if (!$request->get('program') || !$request->get('category'))
    {
      $response['statusCode'] = StatusCode::MISSING_POST_DATA;
      $response['answer'] = $this->translator->trans('errors.post-data', [], 'catroweb');
      $response['preHeaderMessages'] = '';

      return JsonResponse::create($response);
    }

    $program = $this->program_manager->find($request->get('program'));
    if (null == $program)
    {
      $response['statusCode'] = StatusCode::INVALID_PROGRAM;
      $response['answer'] = $this->translator->trans('errors.program.invalid', [], 'catroweb');
      $response['preHeaderMessages'] = '';

      return JsonResponse::create($response);
    }

    $report = new ProgramInappropriateReport();
    $approved_project = $program->getApproved();
    $featured_project = $this->program_manager->getFeaturedRepository()->isFeatured($program);
    if ($approved_project || $featured_project)
    {
      $response = [];
      $response['answer'] = $this->translator->trans('success.report', [], 'catroweb');
      $response['statusCode'] = Response::HTTP_OK;

      return JsonResponse::create($response);
    }

    $token = $request->headers->get('authorization');

    if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
    {
      $user = $this->get('security.token_storage')->getToken()->getUser();
      $report->setReportingUser($user);
    }
    elseif (null !== $token)
    {
      $token = preg_split('#\s+#', $token)[1]; // strip "bearer"
      $jwt_payload = $this->user_manager->decodeToken($token);
      if (!array_key_exists('username', $jwt_payload))
      {
        return JsonResponse::create([], Response::HTTP_UNAUTHORIZED);
      }
      $report->setReportingUser($jwt_payload['username']);
    }
    else
    {
      return JsonResponse::create([], Response::HTTP_UNAUTHORIZED);
    }

    /** @var ProgramInappropriateReport $previous_report */
    $previous_report = $program->getReportFromReportingUser($user);

    if($previous_report)
    {
      if($previous_report->getState() == 1)
      {
        $previous_report->setCategory($request->get('category'));
        $previous_report->setTime(TimeUtils::getDateTime());
        $entity_manager->flush();
      }
      $response = [];
      $response['answer'] = $this->translator->trans('success.report', [], 'catroweb');
      $response['statusCode'] = Response::HTTP_OK;

      return JsonResponse::create($response);
    }

    $report->setCategory($request->get('category'));
    $report->setProgram($program);

    $entity_manager->persist($report);
    $entity_manager->flush();

    $this->event_dispatcher->dispatch(
      new ReportInsertEvent($request->get('category'),  $report)
    );

    if($program->getProgramReportScore() > $this->report_sensitivity)
    {
      $program->setVisible(false);
    }

    $entity_manager->flush();
    $response = [];
    $response['answer'] = $this->translator->trans('success.report', [], 'catroweb');
    $response['statusCode'] = Response::HTTP_OK;

    return JsonResponse::create($response);
  }
}
