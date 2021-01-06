<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/{url}", name="get_gyms", requirements={"url"=".*\/$"})
 */
final class RedirectController extends AbstractController
{
  public function removeTrailingSlash(Request $request): Response
  {
    $pathInfo = $request->getPathInfo();
    $requestUri = $request->getRequestUri();

    $url = \str_replace($pathInfo, \rtrim($pathInfo, ' /'), $requestUri);

    return $this->redirect($url, 301);
  }
}
