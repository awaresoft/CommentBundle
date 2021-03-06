<?php

namespace Awaresoft\CommentBundle\Controller;

use Application\UserBundle\Entity\User;
use Application\CommentBundle\Entity\Abuse;
use Application\CommentBundle\Entity\Comment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class CommentController
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class CommentController extends Controller
{
    /**
     * @Route("/comment/vote/{id}/{action}", name="awaresoft_comment_comment_vote", options={"expose"=true})
     *
     * @param Request $request
     * @param Comment $comment
     * @param string $action
     *
     * @return JsonResponse
     */
    public function voteAction(Request $request, Comment $comment, $action)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $user = $this->getUser();
        $translator = $this->get('translator');
        $voteManager = $this->get('awaresoft.comment.manager.vote');

        if (!$user instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        if ($comment->getAuthor() === $user) {
            return new JsonResponse([
                'message' => $translator->trans('comment.vote.users_comment'),
                'commentId' => $comment->getId(),
                'action' => $action,
            ], 400);
        }

        if ($voteManager->findUserCommentVote($user, $comment)) {
            return new JsonResponse([
                'message' => $translator->trans('comment.vote.already_voted'),
                'commentId' => $comment->getId(),
                'action' => $action,
            ], 400);
        }

        $voteManager->createNewVote($comment, $user, $action);

        return new JsonResponse([
            'message' => $translator->trans('comment.vote.added'),
            'commentId' => $comment->getId(),
            'action' => $action,
            'score' => $comment->getScore(),
        ], 200);
    }

    /**
     * @Route("/comment/abuse/{id}", name="awaresoft_comment_comment_abuse", options={"expose"=true})
     *
     * @param Request $request
     * @param Comment $comment
     *
     * @return JsonResponse
     */
    public function abuseAction(Request $request, Comment $comment)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $translator = $this->get('translator');
        $abuseRepo = $em->getRepository('ApplicationCommentBundle:Abuse');

        if (!$user instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        if ($abuseRepo->findOneBy(['comment' => $comment, 'declarant' => $user])) {
            return new JsonResponse([
                'message' => $translator->trans('comment.abuse.already_abused'),
                'commentId' => $comment->getId(),
            ], 400);
        }

        $abuse = new Abuse();
        $abuse->setDeclarant($user);
        $comment->addAbuse($abuse);

        $em->persist($abuse);
        $em->flush();

        return new JsonResponse([
            'message' => $translator->trans('comment.abuse.added'),
            'commentId' => $comment->getId(),
        ], 200);
    }

    /**
     * @Route("/comment/remove/{id}", name="awaresoft_comment_comment_remove", options={"expose"=true})
     *
     * @param Request $request
     * @param Comment $comment
     *
     * @return JsonResponse
     */
    public function removeAction(Request $request, Comment $comment)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $user = $this->getUser();
        $translator = $this->get('translator');
        $commentManager = $this->get('awaresoft.comment.manager.comment');

        if (!$user instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        if (($comment->getAuthor() !== $user) && !$this->isGranted('ROLE_COMMENT_MODERATOR')) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        $oldComment = clone $comment;
        $commentId = $comment->getId();

        $comment->setState(Comment::STATUS_INVALID);

        try {
            $commentManager->updateComment($comment, $oldComment);
        } catch (\Exception $ex) {
            return new JsonResponse([
                'message' => $translator->trans('comment.remove.failed'),
                'commentId' => $commentId,
            ], 400);
        }

        return new JsonResponse([
            'message' => $translator->trans('comment.remove.success'),
            'commentId' => $commentId,
        ], 200);
    }

    /**
     * @Route("/comment/edit/{id}/{body}", name="awaresoft_comment_comment_edit", options={"expose"=true})
     *
     * @param Request $request
     * @param Comment $comment
     * @param $body
     *
     * @return JsonResponse
     */
    public function editAction(Request $request, Comment $comment, $body)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $user = $this->getUser();
        $translator = $this->get('translator');
        $commentManager = $this->get('awaresoft.comment.manager.comment');

        if (!$user instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        if (($comment->getAuthor() !== $user) && !$this->isGranted('ROLE_COMMENT_MODERATOR')) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        $oldComment = clone $comment;
        $commentId = $comment->getId();

        if ($this->isGranted('ROLE_COMMENT_MODERATOR')) {
            $comment->setState(Comment::STATUS_MODERATE);
            $comment->setAnswer(urldecode($body));
            $output = $comment->getAnswer();
        } else {
            $comment->setBody(urldecode($body));
            $output = $comment->getBody();
        }

        try {
            $commentManager->updateComment($comment, $oldComment);
        } catch (\Exception $ex) {
            return new JsonResponse([
                'message' => $translator->trans('comment.edit.failed'),
                'commentId' => $commentId,
            ], 400);
        }

        return new JsonResponse([
            'message' => $translator->trans('comment.edit.success'),
            'body' => $output,
            'commentId' => $commentId,
        ], 200);
    }

    /**
     * @Route("/comment/remove-many/{ids}", name="awaresoft_comment_comment_removemany", options={"expose"=true})
     *
     * @param Request $request
     * @param array $ids
     *
     * @return JsonResponse
     */
    public function removeManyAction(Request $request, $ids)
    {
        $ids = json_decode($ids);
        $outputIds = [];

        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $user = $this->getUser();
        $translator = $this->get('translator');
        $commentManager = $this->get('awaresoft.comment.manager.comment');

        if (!$user instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        foreach ($ids as $key => $commentId) {
            $comments[$key] = $commentManager->findCommentById($commentId);

            if (!$comments[$key]) {
                continue;
            }

            if (($comments[$key]->getAuthor() !== $user) && !$this->isGranted('ROLE_COMMENT_MODERATOR')) {
                throw new HttpException(Response::HTTP_UNAUTHORIZED);
            }
        }

        foreach ($comments as $comment) {
            $oldComment = $comment;
            $comment->setState(Comment::STATUS_INVALID);

            try {
                $commentManager->updateComment($comment, $oldComment);
                $outputIds[] = $comment->getId();
            } catch (\Exception $ex) {
                throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse([
            'message' => $translator->trans('comment.remove_many.success'),
            'comments' => $outputIds,
        ], 200);
    }

    /**
     * @Route("/comment/show-voters/{id}", name="awaresoft_comment_comment_showvotes", options={"expose"=true})
     *
     * @param Request $request
     * @param Comment $comment
     *
     * @return JsonResponse
     */
    public function showVotesAction(Request $request, Comment $comment)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $voteManager = $this->get('awaresoft.comment.manager.vote');
        $votes = $voteManager->getRepository()->findByComment($comment);
        $message = $this->renderView('AwaresoftCommentBundle:Comment:votes.html.twig', [
            'votes' => $votes,
        ]);

        return $this->forward('ApplicationMainBundle:Ajax:popup', [
            'id' => $request->get('id'),
            'type' => $request->get('type'),
            'requestQueryParameters' => array_merge($request->query->all(), ['message' => $message]),
        ]);
    }

    /**
     * @Route("/comment/get/{id}", name="awaresoft_comment_comment_get", options={"expose"=true})
     *
     * @param Request $request
     * @param Comment $comment
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, Comment $comment)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([
            'body' => $comment->getBody(),
        ]);
    }
}
