<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Form\PanierType;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/categorie")
 */
class CategorieController extends AbstractController
{
    /**
     * @Route("/", name="categorie_index", methods={"GET"})
     */
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('categorie/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }


    /**
     * @Route("/user", name="categorie_index_user", methods={"GET"})
     */
    public function userindex(Request $request , ProduitRepository $produitRepository ,
    Security $security , PanierRepository $panierRepository ,CategorieRepository $categorieRepository): Response
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

        $prixTotal=0;
        for ($i = 0; $i <= $panierCount-1; $i++) {
            $prix = intval($totale[$i]["totale"]) ;
            $prixTotal = $prix + $prixTotal;
        }
        
        return $this->render('categorie/user_index.html.twig', [
            'categories' => $categorieRepository->findAll(),
            'produits' => $produitRepository->findAll(),
            'paniers' => $panierRepository->findBy(['user'=>$user]),
            'panierCount' => $panierCount,
            'prixTotal'=> $prixTotal
        ]);
    }

    /**
     * @Route("/new", name="categorie_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $categorie->getimage();
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

            $categorie->setimage($fileName);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('categorie_index');
        }

        return $this->render('categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="categorie_show", methods={"GET"})
     */
    public function show(Categorie $categorie): Response
    {

        
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
         
        ]);
    }


    /**
     * @Route("/user/{id}", name="categorie_show", methods={"GET"})
     */
    public function usershow(Security $security , PanierRepository $panierRepository ,
     Categorie $categorie , ProduitRepository $produitRepository , Request $request): Response
    {
        $id = $request->get('id');
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

        $prixTotal=0;
        for ($i = 0; $i <= $panierCount-1; $i++) {
            $prix = intval($totale[$i]["totale"]) ;
            $prixTotal = $prix + $prixTotal;
        }
        return $this->render('categorie/user_show.html.twig', [
            'categorie' => $categorie, 
            'produits' => $produitRepository->findBy(array('categorie'=>$id)),
            'produitsa' => $produitRepository->findAll(),
            'paniers' => $panierRepository->findBy(['user'=>$user]),
            'panierCount' => $panierCount,
            'prixTotal'=> $prixTotal
        ]);
    }

    /**
     * @Route("/{id}/edit", name="categorie_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Categorie $categorie): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('categorie_index', [
                'id' => $categorie->getId(),
            ]);
        }

        return $this->render('categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="categorie_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Categorie $categorie): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('categorie_index');
    }
}
