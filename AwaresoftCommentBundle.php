<?php

namespace Awaresoft\CommentBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * AwaresoftCommentBundle class
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class AwaresoftCommentBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'SonataCommentBundle';
    }
}