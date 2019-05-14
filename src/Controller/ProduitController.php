<?php

namespace App\Controller;


use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Entity\Panier;
use App\Form\PanierType;
use App\Repository\PanierRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;



/**
 * @Route("/produit")
 */
class ProduitController extends AbstractController
{
    /**
     * @Route("/", name="produit_index", methods={"GET"})
     */
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }
    /**
     * @Route("/user", name="produit_index_user", methods={"GET"})
     */
    public function userindex(Request $request , ProduitRepository $produitRepository ,
     Security $security , PanierRepository $panierRepository): Response
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
        $prix=0;
        $prixTotal=0;
        for ($i = 0; $i <= $panierCount-1; $i++) {
            $prix = intval($totale[$i]["totale"]) ;
            $prixTotal = $prix + $prixTotal;
        }
   
      
        

        return $this->render('produit/user_index.html.twig', [
            'produits' => $produitRepository->findAll(),
            'paniers' => $panierRepository->findBy(['user'=>$user]),
            'panierCount' => $panierCount,
            'prixTotal'=> $prixTotal
            
        ]);
    }

    /**
     * @Route("/new", name="produit_new", methods={"GET","POST"})
     */
        public function new(Request $request): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $file = $produit->getimage();
            $fileName =''.md5(uniqid()).'.'.$file->guessExtension();
            // Move the file to the directory where images are stored
            try {
                $file->move(
                    $this->getParameter('upload_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
            // updates the 'image' property to store the PDF file name
            // instead of its contents

            $produit->setimage($fileName);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('produit_index');
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),

        ]);
    }


    /**
     * @Route("/{id}", name="produit_show", methods={"GET"})
     */
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    /**
     * @Route("/user/{id}", name="produit_show_user",methods={"GET","POST"})
     */
    public function usershow(Produit $produit , ProduitRepository $produitRepository , Request $request, PanierRepository $panierRepository,Security $security): Response
    {  $em = $this->getDoctrine()->getManager();



        $panier = new Panier;
        $user = $security->getUser();
        $id = $request->get('id');
        $id=intval($id);
        $panierCount = count($panierRepository->findBy(['user'=>$user]));
        $connection = $em->getConnection();
        $statement = $connection->prepare("SELECT totale FROM panier where user_id=2");
        $statement->bindValue('id', $id);
        $statement->execute();
        $totale = $statement->fetchAll();
        $statement = $connection->prepare("SELECT categorie_id FROM produit WHERE id = :id");
        $statement->bindValue('id', $id);
        $statement->execute();
        $id_categorie = $statement->fetchAll();
        $id_categorie=intval($id_categorie);
        $authChecker = $this->container->get('security.authorization_checker');
        $prixTotal=0;
        if ($authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
        if (isset($_POST['ajout'])) {
            $Quantite = $_POST['product_quantity'];
            $prix = $produit->getPrix();
            $prix =  $prix * $Quantite;
            $panier = new Panier();
            $user = $security->getUser();
            $panier->setProduit($produit);
            $panier->setTotale($prix);
            $panier->setNombreProduit($Quantite);
            $panier->setUser($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($panier);
            $entityManager->flush();
            return $this->redirectToRoute('produit_index_user');

        }



        for ($i = 0; $i <= $panierCount-1; $i++) {
            $prix = intval($totale[$i]["totale"]) ;
            $prixTotal = $prix + $prixTotal;
        }
    }
        return $this->render('produit/user_show.html.twig', [
            'produit' => $produit,
             'produits' => $produitRepository->findBy(array('categorie'=>$id_categorie)),
  
             'paniers' => $panierRepository->findBy(['user'=>$user]),
             'panierCount' => $panierCount,
             'prixTotal'=> $prixTotal
        ]);
    }

    /**
     * @Route("/{id}/edit", name="produit_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Produit $produit): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $produit->getimage();

            $fileName =''.md5(uniqid()).'.'.$file->guessExtension();
            // Move the file to the directory where images are stored
            try {
                $file->move(
                    $this->getParameter('upload_directory'),
                    $fileName
                );
                $produit->setimage($fileName);

            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('produit_index', [
                'id' => $produit->getId(),
            ]);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="produit_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Produit $produit): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('produit_index');
    }
}
