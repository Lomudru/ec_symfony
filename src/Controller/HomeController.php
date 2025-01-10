<?php

namespace App\Controller;

use App\Entity\BookRead;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookReadRepository;
use App\Repository\CategoryRepository;
use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private BookReadRepository $bookReadRepository;
    private BookRepository $BookRepository;
    private CategoryRepository $CategoryRepository;
    // Inject the repository via the constructor
    public function __construct(BookReadRepository $bookReadRepository, BookRepository $BookRepository, CategoryRepository $CategoryRepository)
    {
        $this->bookReadRepository = $bookReadRepository;
        $this->BookRepository = $BookRepository;
        $this->CategoryRepository = $CategoryRepository;
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
        $booksReading = [];
        foreach($booksRead as $key => $book){
            $booksReading[$key] = $this->BookRepository->findById($book->getBookId()) ;
        }

        $booksReaded  = $this->bookReadRepository->findByUserId($userId, true);
        $booksReadedData = [];
        foreach($booksReaded as $key => $book){
            $booksReadedData[$key] = $this->BookRepository->findById($book->getBookId())[0];
        }
        $booksReadedCate = [];
        foreach($booksReadedData as $key => $book){
            $booksReadedCate[$key] = $this->CategoryRepository->findById($book->getCategoryId())[0];
        }
        $bookReadedReturn = ["read" => $booksReaded, "book" => $booksReadedData, "cate" => $booksReadedCate];

        // Get the data for the graph
        $allCate = $this->CategoryRepository->CountCategoryByUser($userId);
        $category = [];
        $count = [];
        foreach($allCate as $cate){
            array_push($category, $cate["name"]);
            array_push($count, $cate["book_count"]);
        }
        $categoryCount = ["category" => $category, "count" => $count];

        // Get the data for the searchBar*
        $allBooksRating = $this->BookRepository->getAllBookWithRating();

        return $this->render('pages/home.html.twig', [
            'booksRead' => $booksRead,
            'booksReading' => $booksReading,
            'booksReaded' => $bookReadedReturn,
            'books' => $books,
            'email' => $userEmail, 
            'categoryCount' => $categoryCount,
            'allBooks' => $allBooksRating,
        ]);
    }

    public function saveForm(Request $request, EntityManagerInterface $em): JsonResponse
    {
        if ($request->isXmlHttpRequest()) { 
            $data = $request->request->all();

            $check = isset($data["check"]);

            if($data["idBookReading"] == ""){
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
            }else{
                $bookRead = $this->bookReadRepository->find($data["idBookReading"]);
                $bookRead->setBookId($data["book"]);
                $bookRead->setRating($data["rating"]);
                $bookRead->setDescription($data["description"]);
                $bookRead->setRead($check);
                $bookRead->setUpdatedAt(new \DateTime());
                $em->flush();        
            }
            $dataBook = [];


            if($data["idBookReading"] == ""){
                

                $bookData = $this->BookRepository->findById($bookRead->getBookId());
                $dataBook = [];
                foreach ($bookData as $book) {
                    $dataBook[] = [
                        'id' => $book->getId(),
                        'cateId' => $book->getCategoryId(),
                        'name' => $book->getName(),
                        'desc' => $book->getDescription(),
                    ];
                }
                $cateData = $this->CategoryRepository->findById($dataBook[0]["cateId"]);
                $dataCate = [];
                foreach ($cateData as $cate) {
                    $dataCate[] = [
                        'id' => $cate->getId(),
                        'name' => $cate->getName(),
                        'desc' => $cate->getDescription(),
                    ];
                }
                $bookRead = ["id" => $bookRead->getId(), "updatedAt" => date_format($bookRead->getUpdatedAt(), "d/m/Y H:i")];
                $dataBook = ["reading" => $bookRead, "book" =>  $dataBook[0], "cate" => $dataCate[0]];
            }
            
            return new JsonResponse(['status' => 'success', 'message' => $data, 'dataBook' => $dataBook]);
        }

        return new JsonResponse(['status' => 'error', 'message' => 'Requête non AJAX']);
    }

    public function getBookReadingData(BookReadRepository $bookReadRepository, Request $request): JsonResponse{
    
        if ($request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true);
            
            $books = $bookReadRepository->findById($data["bookId"]);
            

            $dataResponse = [];
            foreach ($books as $book) {
                $dataResponse[] = [
                    'id' => $book->getId(),
                    'bookId' => $book->getBookId(),
                    'desc' => $book->getDescription(),
                    'note' => $book->getRating(),
                    'checked' => $book->isRead(),
                ];
            }
    
            return new JsonResponse($dataResponse);
        
        }
        return new JsonResponse(['status' => 'error', 'message' => 'Requête non AJAX']);
    }

    public function GetGraphData(BookReadRepository $bookReadRepository, Request $request): JsonResponse{
    
        if ($request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true);
            
            $books = $bookReadRepository->findById($data["bookId"]);
            

            $dataResponse = [];
            foreach ($books as $book) {
                $dataResponse[] = [
                    'id' => $book->getId(),
                    'bookId' => $book->getBookId(),
                    'desc' => $book->getDescription(),
                    'note' => $book->getRating(),
                    'checked' => $book->isRead(),
                ];
            }
    
            return new JsonResponse($dataResponse);
        
        }
        return new JsonResponse(['status' => 'error', 'message' => 'Requête non AJAX']);
    }

}
