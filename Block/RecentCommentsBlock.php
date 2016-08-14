<?php

namespace Awaresoft\CommentBundle\Block;

use Awaresoft\Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RecentCommentsBlock
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class RecentCommentsBlock extends BaseBlockService
{
    /**
     * Default template
     */
    const DEFAULT_TEMPLATE = 'AwaresoftCommentBundle:Block:block_recent_comments.html.twig';

    /**
     * @inheritdoc
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'number' => 5,
            'mode' => 'public',
            'title' => 'Recent Comments',
            'template' => self::DEFAULT_TEMPLATE,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', [
            'keys' => [
                ['number', 'integer', [
                    'required' => true,
                    'label' => 'form.label_number',
                ]],
                ['title', 'text', [
                    'required' => false,
                    'label' => 'form.label_title',
                ]],
            ],
            'translation_domain' => 'SonataNewsBundle',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $block = $blockContext->getBlock();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $commentManager = $this->container->get('awaresoft.comment.manager.comment');

        if (!$user instanceof UserInterface) {
            $user = null;
        }

        $latestComments = $commentManager->findComments(
            $user
            ,
            [],
            $block->getSetting('number'),
            0,
            ['c.createdAt' => 'DESC'],
            null,
            false
        );

        return $this->renderResponse($blockContext->getTemplate(), [
            'title' => $block->getSetting('title'),
            'comments' => $latestComments,
            'block_context' => $blockContext,
            'block' => $block,
        ], $response);
    }

    /**
     * @inheritdoc
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (!is_null($code) ? $code : $this->getName()), false, 'AwaresoftCommentBundle', [
            'class' => 'fa fa-comments-o',
        ]);
    }
}
