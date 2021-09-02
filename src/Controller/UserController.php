<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;


class UserController extends AbstractController
{
  

     /**
     * @Route("/user", name="user")
     */
    public function user(): Response
    {
        $repoUser = $this->getDoctrine()->getRepository(User::class);

        $users = $repoUser->findAll();
        
        return $this->render('user/user.html.twig',[
            'users'=>$users,
        ]);
    }
    /**
     * @Route("/user/create", name="user-create")
     */
    public function addUser(Request $request, EntityManagerInterface $manager): Response
    {  
        $User = new User();
        $form = $this->createFormBuilder($User)
                     ->add('nom')
                     ->add('prenom')
                     ->add('matricule')
                     ->add('save', SubmitType::class, [
                         'label' => 'save'
                     ])
                     ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($User);
            $manager->flush();

            return $this->redirectToRoute('user');
        }

        return  $this->render('user/addUser.html.twig',[
            'formRegister'=> $form->createView()
        ]);
    }


    /**
     * @Route("/user/{id}", name="user-edit")
     */
    public function updateUser(User $User,Request $request, EntityManagerInterface $manager): Response
    {   
        $form = $this->createFormBuilder($User)
                     ->add('nom')
                     ->add('prenom')
                     ->add('matricule')
                     ->add('save', SubmitType::class, [
                         'label' => 'save'
                     ])
                     ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($User);
            $manager->flush();

            return $this->redirectToRoute('user');
        }

        return  $this->render('user/updateUser.html.twig',[
            'formRegister'=> $form->createView()
        ]);
    }
    
    
     /** 
      * @Route("/user/delete/{id}", name="user-delete")
      */
     public function deleteUser(User $user, Request $request, EntityManagerInterface $manager){
         $manager->remove($user);
        $manager->flush();
         $id = $request->query->get("id");
         return $this->redirectToRoute('user');
     }

}