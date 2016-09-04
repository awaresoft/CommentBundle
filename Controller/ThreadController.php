<?php

namespace Awaresoft\CommentBundle\Controller;

use Application\CommentBundle\Manager\CommentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ThreadController
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ThreadController extends Controller
{
    /**
     * @Route("/thread/get", name="awaresoft_comment_thread_get", options={"expose"=true})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $trans = $this->get('translator');

        if (!$request->get('threadId') || !$request->get('type')) {
            return new JsonResponse(array(
                'message' => $trans->trans('missing_parameters'),
            ), 400);
        }

        $threadManager = $this->get('awaresoft.comment.manager.thread');
        $commentManager = $this->get('awaresoft.comment.manager.comment');
        $isModerator = $this->isGranted('ROLE_COMMENT_MODERATOR');

        $thread = $threadManager->findThreadById($request->get('threadId'));

        if (!$thread) {
            return new JsonResponse(array(
                'message' => $trans->trans('missing_parameters'),
            ), 400);
        }

        $comments = $commentManager->findCommentsByThreadWithLimit($this->getUser(), $thread, CommentManager::DEFAULT_LIMIT, (int)$request->get('size'), ['c.createdAt' => 'DESC'], null, true, $isModerator);
        $count = $commentManager->countCommentsByThread($thread, true, $isModerator);

        $resultParams = [];
        $viewParams = [
            'comments' => $comments,
        ];

        foreach ($request->query->all() as $key => $param) {
            $resultParams[$key] = $param;
            $viewParams[$key] = $param;
        }

        $resultParams['view'] = $this->renderView('AwaresoftCommentBundle:Helper:comments_list.html.twig', $viewParams);
        $resultParams['count'] = $count;

        return new JsonResponse($resultParams, 200);
    }

    /**
     * @Route("/thread/getbyowner", name="awaresoft_comment_thread_getbyowner", options={"expose"=true})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getByOwnerAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $trans = $this->get('translator');

        if (!$request->get('ownerId') || !$request->get('type')) {
            return new JsonResponse(array(
                'message' => $trans->trans('missing_parameters'),
            ), 400);
        }

        $userManager = $this->get('fos_user.user_manager');
        $commentManager = $this->get('awaresoft.comment.manager.comment');

        $owner = $userManager->findUserBy(array('id' => $request->get('ownerId')));

        if (!$owner) {
            return new JsonResponse(array(
                'message' => $trans->trans('missing_parameters'),
            ), 400);
        }

        $comments = $commentManager->findCommentsByOwnerWithLimit($this->getUser(), $owner, CommentManager::DEFAULT_LIMIT, (int)$request->get('size'));
        $count = $commentManager->countCommentsByOwner($owner);

        $resultParams = [];
        $viewParams = [
            'comments' => $comments,
        ];

        foreach ($request->query->all() as $key => $param) {
            $resultParams[$key] = $param;
            $viewParams[$key] = $param;
        }

        $resultParams['view'] = $this->renderView('AwaresoftCommentBundle:Helper:comments_list.html.twig', $viewParams);
        $resultParams['count'] = $count;

        return new JsonResponse($resultParams, 200);
    }

    /**
     * @Route("/thread/getbyauthor", name="awaresoft_comment_thread_getbyauthor", options={"expose"=true})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getByAuthorAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $trans = $this->get('translator');

        if (!$request->get('authorId') || !$request->get('type')) {
            return new JsonResponse(array(
                'message' => $trans->trans('missing_parameters'),
            ), 400);
        }

        $userManager = $this->get('fos_user.user_manager');
        $commentManager = $this->get('awaresoft.comment.manager.comment');

        $author = $userManager->findUserBy(array('id' => $request->get('authorId')));

        if (!$author) {
            return new JsonResponse(array(
                'message' => $trans->trans('missing_parameters'),
            ), 400);
        }

        $comments = $commentManager->findCommentsByAuthorWithLimit($this->getUser(), $author, CommentManager::DEFAULT_LIMIT, (int)$request->get('size'));
        $count = $commentManager->countCommentsByOwner($author);

        $resultParams = [];
        $viewParams = [
            'comments' => $comments,
        ];

        foreach ($request->query->all() as $key => $param) {
            $resultParams[$key] = $param;
            $viewParams[$key] = $param;
        }

        $resultParams['view'] = $this->renderView('AwaresoftCommentBundle:Helper:comments_list.html.twig', $viewParams);
        $resultParams['count'] = $count;

        return new JsonResponse($resultParams, 200);
    }
}
