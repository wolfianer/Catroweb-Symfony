<?php

namespace App\Catrobat\Controller\Admin;

use App\Entity\ProgramInappropriateReport;
use App\Entity\UserComment;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportController extends CRUDController
{
  public function unreportProgramAction(): RedirectResponse
  {
    /** @var ProgramInappropriateReport|null $object */
    $object = $this->admin->getSubject();
    if (null === $object)
    {
      throw new NotFoundHttpException();
    }
    $program = $object->getProgram();
    $program->setVisible(true);
    $program->setApproved(true);
    $object->setState(3);
    $this->admin->update($object);
    $this->addFlash('sonata_flash_success', 'Program '.$object->getId().' is no longer reported');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function createUrlCommentsAction(): RedirectResponse
  {
    $id = $this->admin->getSubject()->getId();

    return new RedirectResponse('/admin/report/list?filter%5Buser%5D%5Bvalue%5D='.$id);
  }

  public function createUrlProgramsAction(): RedirectResponse
  {
    $id = $this->admin->getSubject()->getId();

    return new RedirectResponse('/admin/app/programinappropriatereport/list?filter%5BreportedUser%5D%5Bvalue%5D='.$id);
  }

  public function acceptProgramReportAction(): RedirectResponse
  {
    /** @var ProgramInappropriateReport|null $object */
    $object = $this->admin->getSubject();
    if (null === $object)
    {
      throw new NotFoundHttpException();
    }
    $program = $object->getProgram();
    $program->setVisible(false);
    $program->setApproved(false);
    $object->setState(2);
    $this->admin->update($object);
    $this->addFlash('sonata_flash_error', 'Program '.$object->getId().' report got accepted');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function unreportCommentAction(): RedirectResponse
  {
    /* @var $object UserComment */
    $object = $this->admin->getSubject();
    if (null === $object)
    {
      throw new NotFoundHttpException();
    }
    $object->setIsReported(false);
    $this->admin->update($object);
    $this->addFlash('sonata_flash_success', 'Comment '.$object->getId().' is no longer reported');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function deleteCommentAction(): RedirectResponse
  {
    /* @var $object UserComment */
    $object = $this->admin->getSubject();
    if (null === $object)
    {
      throw new NotFoundHttpException();
    }
    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository(UserComment::class)->find($object->getId());
    if (null === $comment)
    {
      throw $this->createNotFoundException('No comment found for this id '.$object->getId());
    }
    $em->remove($comment);
    $em->flush();
    $this->addFlash('sonata_flash_success', 'Comment '.$object->getId().' deleted');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
