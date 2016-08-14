<?php

namespace Awaresoft\CommentBundle\Admin;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Awaresoft\Sonata\AdminBundle\Admin\AbstractAdmin as AwaresoftAbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class ThreadAdmin
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ThreadAdmin extends AwaresoftAbstractAdmin
{
    /**
     * @inheritdoc
     */
    protected $baseRoutePattern = 'awaresoft/comment/thread';

    /**
     * @inheritdoc
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept('list');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('permalink')
            ->add('numComments');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('id');

        $listMapper
            ->addIdentifier('permalink', 'text')
            ->add('numComments')
            ->add('type')
            ->add('isCommentable', 'boolean', array('editable' => true));
    }

    /**
     * @inheritdoc
     */
    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            $this->trans('sonata_comment_admin_view_comments', array()),
            array('uri' => $admin->generateUrl('admin_application_comment_comment_list', array('id' => $id)))
        );
    }

}
