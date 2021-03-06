<?php

namespace App\Controller;

use App\Entity\Fiche;
use App\Form\FicheType;
use App\Entity\Medicament;
use App\Form\MedicamentType;
use App\Repository\FicheRepository;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/fiche")
 */
class FicheController extends AbstractController
{
    /**
     * @Route("/", name="fiche_index", methods={"GET"})
     */
    public function index(FicheRepository $ficheRepository): Response
    {
        return $this->render('fiche/index.html.twig', [
            'fiches' => $ficheRepository->findAll(),
            'x' => Null
        ]);
    }

    /**
     * @Route("/new/{id}", name="fiche_new", methods={"GET","POST"})
     */
    public function new(Request $request, $id): Response
    {
        $fiche = new Fiche();
        $form = $this->createForm(FicheType::class, $fiche);
        $form->handleRequest($request);
        $med = $this->getDoctrine()->getRepository(Medicament::class)->findOneBy(['id' => $id]);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $fiche->setQte($form->get('qte')->getData());
            $fiche->setPrixVente($form->get('prix_vente')->getData());
            $med->setFicheExist(true);
            $fiche->setIdMed($med);
            $entityManager->persist($fiche);
            $entityManager->flush();

            return $this->redirectToRoute('fiche_index');
        }

        return $this->render('fiche/new.html.twig', [
            'fiche' => $fiche,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="fiche_show", methods={"GET"})
     */
    public function show(Medicament $med): Response
    {
        $fiche = $this->getDoctrine()->getRepository(Fiche::class)->findOneBy(['id_med' => $med]);

        return $this->render('fiche/show.html.twig', [
            'fiche' => $fiche,
        ]);
    }

    /**
     * @Route("/qtea/{id}", name="fiche_qtea", methods={"POST"})
     */
    public function qtea(Fiche $fiche, Request $request): Response
    {
       
        $entityManager = $this->getDoctrine()->getManager();
        $fiche->setQte($fiche->getQte()+(int)$request->request->get('qte1'));
        $entityManager->flush();
        return $this->redirectToRoute('fiche_index');
    }

    /**
     * @Route("/qtem/{id}", name="fiche_qtem", methods={"POST"})
     */
    public function qtem(Fiche $fiche, Request $request): Response
    {   
        if($fiche->getQte() == 0 || (int)$request->request->get('qt2') > $fiche->getQte())
        return $this->redirectToRoute('fiche_index');
        
        $fiche->setQte($fiche->getQte()-(int)$request->request->get('qte2'));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();
        return $this->redirectToRoute('fiche_index');
    }

    /**
     * @Route("/{id}/edit", name="fiche_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Fiche $fiche): Response
    {
        $form = $this->createForm(FicheType::class, $fiche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('fiche_index');
        }

        return $this->render('fiche/edit.html.twig', [
            'fiche' => $fiche,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="fiche_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Fiche $fiche): Response
    {
        if ($this->isCsrfTokenValid('delete' . $fiche->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($fiche);
            $entityManager->flush();
        }

        return $this->redirectToRoute('fiche_index');
    }
}
