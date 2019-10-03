<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\User;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use App\Service\UserService;
use App\Service\VoteService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/club")
 * @IsGranted("ROLE_MERITE_ADMIN")
 */
class ClubController extends AbstractController
{
    /**
     * @var ClubRepository
     */
    private $clubRepository;
    /**
     * @var VoteService
     */
    private $voteService;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ClubRepository $clubRepository,
        VoteService $voteService,
        UserService $userService
    ) {
        $this->clubRepository = $clubRepository;
        $this->voteService = $voteService;
        $this->entityManager = $entityManager;
        $this->userService = $userService;
    }

    /**
     * @Route("/", name="club_index", methods={"GET"})
     * @IsGranted("ROLE_MERITE_ADMIN")
     */
    public function index(): Response
    {
        return $this->render(
            'club/index.html.twig',
            [
                'clubs' => $this->clubRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/new", name="club_new", methods={"GET","POST"})
     *
     */
    public function new(Request $request): Response
    {
        $club = new Club();
        $form = $this->createForm(ClubType::class, $club);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($club);
            $this->userService->createUser($club);
            $this->entityManager->flush();

            return $this->redirectToRoute('club_index');
        }

        return $this->render(
            'club/new.html.twig',
            [
                'club' => $club,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="club_show", methods={"GET"})
     *
     */
    public function show(Club $club): Response
    {
        $votes = $this->voteService->getVotesByClub($club);

        return $this->render(
            'club/show.html.twig',
            [
                'club' => $club,
                'votes' => $votes,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="club_edit", methods={"GET","POST"})
     *
     */
    public function edit(Request $request, Club $club): Response
    {
        $form = $this->createForm(ClubType::class, $club);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('club_index');
        }

        return $this->render(
            'club/edit.html.twig',
            [
                'club' => $club,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="club_delete", methods={"DELETE"})
     *
     */
    public function delete(Request $request, Club $club): Response
    {
        if ($this->isCsrfTokenValid('delete'.$club->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($club);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('club_index');
    }
}
