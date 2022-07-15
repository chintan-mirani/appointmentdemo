<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/users", name="users")
     * 
     * @return Template
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        
        $usersPagination = $paginator->paginate(
            $users, 
            $request->query->getInt('page', 1), 
            7
        );

        return $this->render('user/index.html.twig', [
            'users' => $usersPagination,
        ]);
    }
    
    /**
     * @Route("user/create", name = "user_create")
     * 
     * @param Request $request
     * @return Templete
     */
    public function createUser(Request $request)
    {   
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $userData = $form->getData();    
            $user->setFirstName($userData->getFirstName());
            $user->setLastName($userData->getLastName());
            $user->setEmail($userData->getEmail());
            $user->updatedTimestamps();
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            return $this->redirectToRoute('users');
        }
        
        return $this->render('user/create.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("user/update/{id}", name = "user_update")
     * 
     * @param Request $request
     * @return Templete
     */
    public function updateUser(Request $request, $id)
    {   
        $user = new User();
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->redirectToRoute('users');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $userData = $form->getData();
            $user->setFirstName($userData->getFirstName());
            $user->setLastName($userData->getLastName());
            $user->setEmail($userData->getEmail());
            $user->updatedTimestamps();
            $this->entityManager->flush();
            
            return $this->redirectToRoute('users');
        }

        return $this->render('user/update.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/delete/{id}", name="user_delete")
     * 
     * @param $id
     * @return RedirectToRoute users
     */
    public function deleteUser($id)
    {

        $user = $this->entityManager->getRepository(User::class)->find($id);
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('users');
    }

}
