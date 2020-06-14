<?php

namespace App\Admin;

use App\Entity\Program;
use App\Entity\User;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ReportedCommentsAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_report';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'report';

  protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
  {
    $query = parent::configureQuery($query);

    if (!$query instanceof ProxyQuery)
    {
      return $query;
    }

    /** @var QueryBuilder $qb */
    $qb = $query->getQueryBuilder();


    $rootAlias = $qb->getRootAliases()[0];
    $parameters = $this->getFilterParameters();
    if ('getReportedCommentsCount' === $parameters['_sort_by'])
    {
      $sub = $qb->getEntityManager()->createQueryBuilder();

     /* $sub->select('c')
        ->addSelect( 'count(c.user)')
        ->from('App\Entity\UserComment', 'c')
        //->where($qb->expr()->eq('c.isReported', '1'))
        ->groupBy('c.user')
      ;*/

      $sub->select(array('c', 'count(c.user)'))
        ->from('App\Entity\UserComment', 'c')
      // Instead of setting the parameter in the main query below, it could be quoted here:
      // ->where('status = ' . $connection->quote(UserSurveyStatus::ACCESSED))
      //->where('status = :status')
      ->groupBy('c.user');


      //->leftJoin($sub->getDQL(), 'counter', Join::WITH, $rootAlias.'.user = counter.user');
      //[Syntax Error] line 0, col 49: Error: Expected Doctrine\ORM\Query\Lexer::T_ALIASED_NAME, got 'SELECT'
      $qb->leftJoin(sprintf('(%s)', $sub->getQuery()->getDQL()), 'counter', Join::WITH, $sub->getRootAliases()[0].'.user = counter.user')
      ->setParameter('isReported', true);

      //->where($qb->expr()->eq('counter.isReported', '1'))
        //->groupBy('counter.user');
      /*$qb->andWhere(
      $qb->expr()->eq($rootAlias. '.isReported', $qb->expr()->literal(true))
      )*/
  /*   $qb
       ->join('App\Entity\UserComment', 'i')
       ->where(
         $qb->expr()->in(
           'i.user',
           $sub->getDQL()
       ));*/



      // var_dump($qb->getQuery()->getSQL());
       var_dump($qb->getQuery()->getDQL());
       var_dump($qb->getQuery()->getResult());
      //var_dump($sub->getQuery()->getSQL());
      //var_dump($sub->getQuery()->getDQL());
      //var_dump($sub->getQuery()->getResult());
      die;

    } else
    {
      $qb->andWhere(
        $qb->expr()->eq($rootAlias. '.isReported', $qb->expr()->literal(true))
      );
    }





    return $query;
  }

  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
  {
  }

  protected function configureFormFields(FormMapper $formMapper): void
  {
    $formMapper
      ->add('user', EntityType::class, ['class' => User::class])
    ;
  }
  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper): void
  {
    $listMapper
      ->add('username', null, [
        'label' => 'Comment author',
      ])
      ->add(
        'getReportedCommentsCount',
        null,
        [
          'label' => '#Reported Comments from author',
          'sortable' => true,
          'sort_field_mapping' => ['fieldName' => 'user'],
          'sort_parent_association_mappings' => [],
        ])
      ->add('user.limited', 'boolean', [
        'editable' => true,
      ])
      ->add('program.name', null, [
        'label' => 'Program commented on'
      ])
      ->add('uploadDate', null, [
        'label' => 'Upload date of comment'
     ])
      ->add('text')
      ->add('_action', 'actions', ['actions' => [
        'delete' => ['template' => 'Admin/CRUD/list__action_delete_comment.html.twig'],
        'unreportComment' => ['template' => 'Admin/CRUD/list__action_unreportComment.html.twig'],
      ]])
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->add('deleteComment');
    $collection->add('unreportComment');
    $collection->remove('create')->remove('delete')->remove('export');
  }
}


