<?php

namespace Awaresoft\CommentBundle\Admin;

use Awaresoft\Sonata\AdminBundle\Admin\AbstractAdmin as AwaresoftAbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class AbuseAdmin
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class AbuseAdmin extends AwaresoftAbstractAdmin
{
    /**
     * @inheritdoc
     */
    protected $baseRoutePattern = 'awaresoft/comment/abuse';

    /**
     * @inheritdoc
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('declarant', 'doctrine_orm_model_autocomplete', [], null, [
                'property' => 'username',
            ])
            ->add('comment', 'doctrine_orm_model_autocomplete', [], null, [
                'property' => 'id',
            ])
            ->add('solved')
            ->add('createdAt');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('declarant')
            ->add('comment')
            ->add('solved', null, ['editable' => true])
            ->add('createdAt', 'datetime')
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with($this->trans('admin.admin.form.group.main'), ['class' => 'col-md-6'])->end();

        $formMapper
            ->with($this->trans('admin.admin.form.group.main'))
            ->add('solved')
            ->end();
    }
}
