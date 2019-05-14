<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Form\PanierType;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="Accuiel")
     */
    public function index( ProduitRepository $produitRepository , Request $request, PanierRepository $panierRepository,Security $security): Response
    {

        $panier = new Panier;
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

        $totale[0]=0;
        $prixTotal=0;
        for ($i = 0; $i <= $panierCount-1; $i++) {
            $prix = intval($totale[$i]["totale"]) ;
            $prixTotal = $prix + $prixTotal;
        }
        return $this->render('base.html.twig', [
            'produits' => $produitRepository->findAll(),
            'paniers' => $panierRepository->findBy(['user'=>$user]),
            'panierCount' => $panierCount,
            'prixTotal'=> $prixTotal
        ]);
    }

    /**
     * @Route("/apropos", name="Apropos")
     */
    public function apropos(): Response
    {

       
        return $this->render('apropos.html.twig');

    }
}
