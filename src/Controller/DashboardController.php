<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use App\Repository\PanierRepository;


class DashboardController extends AbstractController
{
    /**
     * @Route("/tableau_board", name="tableau_board")
     */
    public function index(CommandeRepository $CommandeRepository ,  ProduitRepository $ProduitRepository , CategorieRepository $CategorieRepository )
    {   $em = $this->getDoctrine()->getManager();

        $user = 'SELECT COUNT(*) FROM fos_user';
        $statement = $em->getConnection()->prepare($user);
        $statement->execute();
        $user = $statement->fetchAll();
        $user=intval($user);

        $category = count($CategorieRepository->findAll());
        $product = count($ProduitRepository->findAll());
        $command =count($CommandeRepository->findAll());

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController', 'nombre_utilisateur' => $user ,
            'category'=> $category , 'product' => $product , 'command' => $command
        ]);
    }

    /**
     * @Route("/utilisateurs", name="utilisateur")
     */
    public function user_index()
    {

        return $this->render('dashboard/user.html.twig');
    }
}
