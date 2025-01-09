<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'auth.register')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()) {
            // Rediriger vers la page de login si l'utilisateur n'est pas connecté
            return $this->redirectToRoute('app.home');
        }

        $error = [];
        if ($request->isMethod('POST')) {
            // Get the form data
            $email = $request->request->get('user_email');
            $password = $request->request->get('user_password');
            $passwordConfirm = $request->request->get('user_password_confirm');
            $checked = $request->request->get('check');

            // Verification of the given data
            if($email == "" || $password == "" || $passwordConfirm == ""){
                $error["AllWrong"] = "Veuilliez remplir tout les champs";
            }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $error["Email"] = "Format de l'email invalide";
            }elseif(strlen($password) < 8){
                $error["Password"] = "Mot de passe trop court";
            }elseif($password !== $passwordConfirm){
                $error["PasswordConfirm"] = "Les mots de passe ne correspondent pas";
            }elseif(!$checked){
                $error["Checked"] = "Vous devez accepter les conditions générales d'utilisation";
            }
            

            $user = new User();
            $user->setEmail($email);
            // Hash the password
            $passwordHashed = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($passwordHashed);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('auth.login');
        }
        return $this->render('auth/register.html.twig', [
            "error" => $error,
        ]);
    }
}
