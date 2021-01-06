<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Exception;

final class DefaultController extends AbstractController
{
  /**
   * @Route("/", name="index")
   */
  public function index(): Response
  {
    return $this->redirectToRoute('profile');
  }

  /**
   * @Route("/test", name="test_index")
   */
  public function testIndex(
    Request $request,
    SessionInterface $session
  ): Response {
    if ($this->getParameter('kernel.environment') != 'dev') {
      throw new NotFoundHttpException();
    }
    $file = $request->query->get('file', 'index.html');

    $file = \str_replace('.html', '', $file);
    return $this->render(\sprintf('%s.twig', $file), []);
  }

  /**
   * @Route("/access_denied", name="access_denied")
   */
  public function denyAccess(): Response
  {
    return $this->render('security/access_denied.twig', []);
  }

  /**
   * @Route("/opcache", name="opcache_stats")
   */
  public function getOpcacheStats(): JsonResponse
  {
    if ($this->getParameter('kernel.environment') != 'dev') {
      throw new NotFoundHttpException();
    }
    return new JsonResponse(\opcache_get_status());
  }
}
