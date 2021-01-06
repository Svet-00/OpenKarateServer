<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

final class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
  public function handle(
    Request $request,
    AccessDeniedException $accessDeniedException
  ): RedirectResponse {
    return new RedirectResponse('/access_denied');
  }
}
