<?php

namespace App\Controller;

use App\Entity\Chantier;
use App\Entity\Pointage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

$edit = false;

class ChantierController extends AbstractController
{
    /**
     * @Route("/chantier", name="chantier")
     */
    public function chantier( EntityManagerInterface $manager): Response
    {
        $repochantier = $this->getDoctrine()->getRepository(Chantier::class);

        $chantiers = $repochantier->findAll();
        $repoPointage = $this->getDoctrine()->getRepository(Pointage::class);
        $pointages = $repoPointage->findAll();
        /*$nbrPointages = 0 ;
        foreach($chantiers as $chantier){
            $sql = "SELECT COUNT(DISTINCT(utilisateur)) FROM pointage WHERE chantier = ?";
            $connection = $manager->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1,$chantier); 
            $stmt->execute();
            $nbrPointages = $stmt;
        }*/

        return $this->render('chantier/chantier.html.twig',[
            'chantiers'=>$chantiers,
        ]);
    }
    /**
     * @Route("/chantier/create", name="chantier-create")
     */
    public function addChantier(Request $request, EntityManagerInterface $manager): Response
    {   $update = true;
        
        $chantier = new Chantier();
        $form = $this->createFormBuilder($chantier)
                     ->add('nom')
                     ->add('adresse')
                     ->add('dateDebut')
                     ->add('save', SubmitType::class, [
                         'label' => 'save'
                     ])
                     ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($chantier);
            $manager->flush();

            return $this->redirectToRoute('chantier');
        }


        return  $this->render('chantier/addChantier.html.twig',[
            'formAddChantier'=> $form->createView()
        ]) ;
    }

    /**
     * * @Route("/chantier/{id}", name="chantier-edit")
     */
    public function editChantier(Chantier $chantier, Request $request, EntityManagerInterface $manager): Response
    {   $update = true;
        
        
        $form = $this->createFormBuilder($chantier)
                     ->add('nom')
                     ->add('adresse')
                     ->add('dateDebut')
                     ->add('save', SubmitType::class, [
                         'label' => 'update'
                     ])
                     ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($chantier);
            $manager->flush();

            return $this->redirectToRoute('chantier');
        }


        return  $this->render('chantier/updateChantier.html.twig',[
            'formAddChantier'=> $form->createView()
        ]) ;
    }

    /**
     * 
     * @Route("/delete/{id}", name="chantier-delete")
     */
    public function deleteChantier(Chantier $chantier, Request $request, EntityManagerInterface $manager){
        $manager->remove($chantier);
        $manager->flush();
        $id = $request->query->get("id");
        return $this->redirectToRoute('chantier');
    }
}
