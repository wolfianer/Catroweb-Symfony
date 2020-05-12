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
      $qb->andWhere(
        $qb->expr()->eq($rootAlias. '.isReported', $qb->expr()->literal(true)))
        ->groupBy($rootAlias)
        ->orderBy('COUNT('.$rootAlias.'.user )', $parameters['_sort_order'])
      ;
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


