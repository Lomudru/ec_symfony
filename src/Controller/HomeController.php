<?php

namespace App\Controller;

use App\Entity\BookRead;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookReadRepository;
use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private BookReadRepository $readBookRepository;
    private BookRepository $BookRepository;
    // Inject the repository via the constructor
    public function __construct(BookReadRepository $bookReadRepository, BookRepository $BookRepository)
    {
        $this->bookReadRepository = $bookReadRepository;
        $this->BookRepository = $BookRepository;
    }

    #[Route('/', name: 'app.home')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('auth.login');
        }
        // Get the user data
        $user = $this->getUser();
        $userId = $user->getId();
        $userEmail = $user->getEmail();

        $books = $this->BookRepository->findAll();

        $booksRead  = $this->bookReadRepository->findByUserId($userId, false);

        

        // Render the 'hello.html.twig' template
        return $this->render('pages/home.html.twig', [
            'booksRead' => $booksRead,
            'books' => $books,
            'email'      => $userEmail, 
        ]);
    }

    public function saveForm(Request $request, EntityManagerInterface $em): JsonResponse
    {
        if ($request->isXmlHttpRequest()) { // Vérifie que la requête est AJAX
            // Récupère les données du formulaire
            $data = $request->request->all();

            $check = isset($data["check"]);

            $bookRead = new BookRead();
            $bookRead->setUserId($this->getUser()->getId());
            $bookRead->setBookId($data["book"]);
            $bookRead->setRating($data["rating"]);
            $bookRead->setDescription($data["description"]);
            $bookRead->setRead($check);
            $bookRead->setCreatedAt(new \DateTime());
            $bookRead->setUpdatedAt(new \DateTime());

            $em->persist($bookRead);
            $em->flush();

            return new JsonResponse(['status' => 'success', 'message' => $data]);
        }

        return new JsonResponse(['status' => 'error', 'message' => 'Requête non AJAX']);
    }

}
