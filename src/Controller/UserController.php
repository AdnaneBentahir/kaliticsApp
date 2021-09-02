<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
   /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="register")
     * @Route("/{id}", name="edit-user")
     */
    public function manageUser(User $User = null,Request $request, EntityManagerInterface $manager): Response
    {   if(!$User){
        $User = new User();
    }
        //$User = new User();
        $form = $this->createFormBuilder($User)
                     ->add('nom')
                     ->add('prenom')
                     ->add('matricule')
                     ->add('password')
                     ->add('save', SubmitType::class, [
                         'label' => 'save'
                     ])
                     ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($User);
            $manager->flush();

            return $this->redirectToRoute('login');
        }

        return  $this->render('register.html.twig',[
            'formRegister'=> $form->createView()
        ]);
    }
    
    // /**TODO
    //  * 
    //  * @Route("/delete/{id}", name="user-delete")
    //  */
    // public function deleteUser(User $user, Request $request, EntityManagerInterface $manager){
    //     $manager->remove($user);
    //     $manager->flush();
    //     $id = $request->query->get("id");
    //     return $this->redirectToRoute('user');
    // }

}