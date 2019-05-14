<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Commande;
use App\Form\PanierType;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @Route("/panier")
 */
class PanierController extends AbstractController
{
    /**
     * @Route("/", name="panier_index", methods={"GET","POST"})
     */
    public function index(Request $request , ProduitRepository $produitRepository ,
    Security $security , PanierRepository $panierRepository,ObjectManager $manager): Response
    {

        $panier = new Panier;
        $user = $security->getUser();
        $em = $this->getDoctrine()->getManager();
        $panierCount = count($panierRepository->findBy(['user'=>$user]));
        $id = $request->get('id');
        $id=intval($id);
        $connection = $em->getConnection();
        $statement = $connection->prepare("SELECT totale FROM panier where user_id=2");
        $statement->bindValue('id', $id);
        $statement->execute();
        $totale = $statement->fetchAll();

        $prixTotal=0;
        for ($i = 0; $i <= $panierCount-1; $i++) {
            $prix = intval($totale[$i]["totale"]) ;
            $prixTotal = $prix + $prixTotal;
        }


        if (isset($_POST['ajout'])) {
            $user = $this->getUser();
            $userId = $user->getId();
             
            
            $commande = new Commande;
            $panier = $panierRepository->findBy(['user'=>$user]);
            $panierCount = count($panierRepository->findBy(['user'=>$user]));
            for ($i = 0; $i <= $panierCount-1; $i++) {
            $commande->addPanier($panier[$i]);
            }
            $commande->setMontant($prixTotal);
            $commande->setDate(new \DateTime('now'));
            $commande->setClient($user);
            $manager->persist($commande);
            $manager->flush();
            $connection = $em->getConnection();
            $statement = $connection->prepare("Delete FROM panier where user_id=2");
            $statement->bindValue('id', $id);
            $statement->execute();
           


            return $this->redirectToRoute('produit_index_user');

        }

   
    
        return $this->render('panier/index.html.twig', [
            'produits' => $produitRepository->findAll(),
            'paniers' => $panierRepository->findBy(['user'=>$user]),
            'panierCount' => $panierCount,
            'prixTotal'=> $prixTotal
        ]);
    }

    

    /**
     * @Route("/{id}", name="panier_show", methods={"GET"})
     */
    public function show(Panier $panier,Request $request , ProduitRepository $produitRepository ,
    Security $security , PanierRepository $panierRepository): Response
    {   $panier = new Panier;
        $user = $security->getUser();
        $em = $this->getDoctrine()->getManager();
        $panierCount = count($panierRepository->findBy(['user'=>$user]));
        $id = $request->get('id');
        $id=intval($id);
        $connection = $em->getConnection();
        $statement = $connection->prepare("SELECT totale FROM panier where user_id=1");
        $statement->bindValue('id', $id);
        $statement->execute();
        $totale = $statement->fetchAll();

        $prixTotal=0;
        for ($i = 0; $i <= $panierCount-1; $i++) {
            $prix = intval($totale[$i]["totale"]) ;
            $prixTotal = $prix + $prixTotal;
        }
   
    


        return $this->render('panier/show.html.twig', [
            'panier' => $panier,
            'produits' => $produitRepository->findAll(),
            'paniers' => $panierRepository->findBy(['user'=>$user]),
            'panierCount' => $panierCount,
            'prixTotal'=> $prixTotal
        ]);
    }

    

    /**
     * @Route("/{id}", name="panier_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Panier $panier): Response
    {
        if ($this->isCsrfTokenValid('delete'.$panier->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($panier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('panier_index');
    }
}
