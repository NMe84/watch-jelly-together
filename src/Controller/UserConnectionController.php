<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserConnection;
use App\Form\Type\UserConnectionType;
use App\Messenger\Message\SyncMasterDataMessage;
use App\Repository\UserConnectionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/connections', name: 'user_connection_')]
class UserConnectionController extends BaseController
{
    #[Route(name: 'list')]
    public function list(UserConnectionRepository $repository): Response
    {
        return $this->render('user_connection/list.html.twig', [
            'connections' => $repository->findAll(),
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request): Response
    {
        return $this->edit(new UserConnection(), $request);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(UserConnection $userConnection, Request $request): Response
    {
        $form = $this
            ->createForm(UserConnectionType::class, $userConnection)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($userConnection);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('user_connection.alert.saved'));

            return $this->redirectToRoute('user_connection_list');
        }

        return $this->render('user_connection/edit.html.twig', [
            'user_connection' => $userConnection,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: [Request::METHOD_POST])]
    public function delete(UserConnection $userConnection): Response
    {
        $this->em->remove($userConnection);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('user_connection.alert.deleted'));

        return $this->redirectToRoute('user_connection_list');
    }

    #[Route('/sync-masterdata', name: 'sync_masterdata')]
    public function syncMasterdata(): Response
    {
        $this->messageBus->dispatch(new SyncMasterDataMessage());

        $this->addFlash('success', $this->translator->trans('user_connection.alert.sync_queued'));

        return $this->redirectToRoute('user_connection_list');
    }
}
