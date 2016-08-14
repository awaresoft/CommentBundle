<?php

namespace Awaresoft\CommentBundle\Admin;

use Awaresoft\Sonata\AdminBundle\Admin\AbstractAdmin as AwaresoftAbstractAdmin;
use Application\CommentBundle\Manager\ThreadManager;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;

/**
 * Class CommentAdmin
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class CommentAdmin extends AwaresoftAbstractAdmin
{
    /**
     * @inheritdoc
     */
    protected $baseRoutePattern = 'awaresoft/comment/comment';

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('author', 'doctrine_orm_model_autocomplete', [], null, [
                'property' => 'username',
            ])
            ->add('thread.owner', 'doctrine_orm_model_autocomplete', [], null, [
                'property' => 'username',
            ])
            ->add('authorName')
            ->add('body', null, [
                'label' => $this->trans('label.comment'),
            ])
            ->add('answer')
            ->add('thread', 'doctrine_orm_model_autocomplete', [], null, [
                'property' => 'id',
                'minimum_input_length' => 1,
            ])
            ->add('score')
            ->add('abusesCount')
            ->add('state');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('body', 'text', [
                'label' => $this->trans('label.comment'),
            ])
            ->add('author')
            ->add('createdAt', 'datetime')
            ->add('score', 'float')
            ->add('abusesCount')
            ->add('state', 'string', ['template' => 'SonataCommentBundle:CommentAdmin:list_status.html.twig']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $disabledAuthorName = false;

        if ($this->getSubject()->getAuthor()) {
            $disabledAuthorName = true;
        }

        $formMapper
            ->with($this->trans('admin.admin.form.group.main'), ['class' => 'col-md-6'])->end()
            ->with($this->trans('admin.admin.form.group.advanced'), ['class' => 'col-md-6'])->end();

        $formMapper
            ->with($this->trans('admin.admin.form.group.main'))
            ->add('author', 'sonata_type_model_autocomplete', [
                'property' => 'username',
                'required' => true,
            ]);

        if ($this->getSubject() && $this->getSubject()->getId()) {
            $formMapper
                ->add('authorName', null, [
                    'disabled' => $disabledAuthorName,
                ]);
        }

        $formMapper
            ->add('body', 'textarea', [
                'label' => $this->trans('label.comment'),
            ])
            ->add('answer', 'textarea', [
                'required' => false,
            ])
            ->add('createdAt', 'sonata_type_datetime_picker');

        if ($this->getSubject() && !$this->getSubject()->getId()) {
            $formMapper
                ->add('thread', 'sonata_type_model_autocomplete', [
                    'property' => 'id',
                    'minimum_input_length' => 1,
                ]);
        }

        $formMapper
            ->end();

        $formMapper
            ->with($this->trans('admin.admin.form.group.advanced'), ['class' => 'col-md-6'])
            ->add('state', 'sonata_comment_status', [
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('score', null, [
                'disabled' => true,
            ])
            ->add('abusesCount', null, [
                'disabled' => true,
            ])
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, ['edit'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        if (!$id) {
            return;
        }

        $commentManager = $this->getConfigurationPool()->getContainer()->get('awaresoft.comment.manager.comment');
        $comment = $commentManager->findCommentById($id);

        if (!$comment) {
            return;
        }

        $menu->addChild(
            $this->trans('comment.admin.tabmenu.show_all_threads_comments'),
            ['uri' => $this->getRouteGenerator()->generate('admin_application_comment_comment_list', ['filter[thread][value]' => $comment->getThread()->getId()])]
        );

        if ($comment->getThread()->getPermalink() != ThreadManager::THREAD_DEFAULT_PERMALINK) {
            $menu->addChild(
                $this->trans('comment.admin.tabmenu.show_thread'),
                ['uri' => $this->getRequest()->getBaseUrl() . $comment->getThread()->getPermalink()]
            );
        }
    }
}
