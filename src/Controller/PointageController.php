<?php

namespace App\Controller;

use Symfony\Component\PropertyInfo\Type;

use App\Entity\Pointage;
use App\Entity\Chantier;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PointageController extends AbstractController
{
    /**
     * @Route("/pointage", name="pointage")
     */
    public function pointage(): Response
    {
        $repo = $this->getDoctrine()->getRepository(Pointage::class);
        $repoUser = $this->getDoctrine()->getRepository(User::class);
        $repoChantier = $this->getDoctrine()->getRepository(Chantier::class);

        $pointages = $repo->findAll();

        foreach ($pointages as $pointage) {
            $utilisateur = $repoUser->find($pointage->getUtilisateur());
            //if(!$utilisateur ||  is_string($utilisateur)) continue;
            $pointage->setUtilisateur($utilisateur);

            $chantier = $repoChantier->find($pointage->getChantier());
            //if(!$chantier ||  is_string($chantier)) continue;
            $pointage->setChantier($chantier);
        }
        


        return $this->render('pointage/pointage.html.twig',[
            'pointages'=>$pointages,
        ]);
    }

 
    /**
     * @Route("/pointage/create", name="pointage-create")
     */
    public function addPointage(Request $request, EntityManagerInterface $manager): Response
    {
        $repo = $this->getDoctrine()->getRepository(Pointage::class);

        $pointage = new Pointage();
        $form = $this->createFormBuilder($pointage)
                    ->add('utilisateur')
                    ->add('chantier')             
                     ->add('dateP')
                     ->add('duree')
                     ->add('save', SubmitType::class, [
                         'label' => 'save',
                        
                     ])
                     ->getForm();

        $form->handleRequest($request);
        
        

        $err = "";
        if($form->isSubmitted() && $form->isValid()){

            /*validator*/
            $isValid = true;
            

            $repoUser = $this->getDoctrine()->getRepository(User::class);
            $utilisateur = $repoUser->find($form["utilisateur"]->getData());
            $repoChantier = $this->getDoctrine()->getRepository(Chantier::class);
            $chantier = $repoChantier->find($form["chantier"]->getData());
            if(!$utilisateur || !$chantier){
                $isValid = false;
                $err = "L'utilisateur ou le chantier n'existe pas.";
            } elseif(time() < $form["dateP"]->getData()->getTimestamp()){
                $isValid = false;
                $err = "bruh hh.";
            } else {
                $sql = "SELECT * FROM pointage WHERE utilisateur = ?";
                $connection = $manager->getConnection();
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(1, 's:1:"'.$form["utilisateur"]->getData().'";'); 
                $stmt->execute();
                
                $pts = $stmt->fetchAll();
                
                $time = intval($form["duree"]->getData());
                foreach($pts as $pt){
                    
                    if($pt["chantier"] == 's:1:"'.$form["chantier"]->getData().'";' && abs($form["dateP"]->getData()->getTimestamp() - strtotime($pt["date_p"])) < 86400){
                        $isValid = false;
                        $err = "Vous ne pouvez pas pointer dans le meme chantier au meme jour";
                        break;
                    }
                    
                    if(abs($form["dateP"]->getData()->getTimestamp() - strtotime($pt["date_p"])) < 604800){
                        $duree = intval($pt["duree"]);
                        if(is_numeric($duree)) $time += $duree;
                        if($time >= 35){
                            $isValid = false;
                            $err = "Vous ne pouvez pas pointer plus que 35h dans la même semaine.";
                            break;
                        }
                    }
                }
            }
            /*end validator*/

            if($isValid && $err==""){
                $manager->persist($pointage);
                $manager->flush();

                return $this->redirectToRoute('pointage');
               
            }
        }
            
        return  $this->render('pointage/addPointage.html.twig',[
            'formAddPointage'=> $form->createView(),
            'log'=> $err
            
        ]) ; 
            
            
            
        


        
    }
}
