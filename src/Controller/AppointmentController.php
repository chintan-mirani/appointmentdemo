<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\AppointmentUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AppointmentType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class AppointmentController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/appointments", name="appointment")
     * 
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Templete
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $appointments = $this->entityManager->getRepository(Appointment::class)->findAll();

        $appointmentsPagination = $paginator->paginate(
            $appointments, 
            $request->query->getInt('page', 1), 
            7
        );
    
        return $this->render('appointment/index.html.twig', [
            'appointments' => $appointmentsPagination,
        ]);
    }

    /**
     * @Route("appointment/create", name = "appointment_create")
     * 
     * @param Request $request
     * @return Templete
     */
    public function createAppointment(Request $request)
    {   
        $appointment = new Appointment();

        $form = $this->createForm(AppointmentType::class, $appointment, ['existingUsers' => []]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $appointmentData = $form->getData();
            $appointmentUsers = $form->get('users')->getData();
            
            $appointment->setName($appointmentData->getName());
            $appointment->setDate($appointmentData->getDate());
            $appointment->setStartTime($appointmentData->getStartTime());
            $appointment->setEndTime($appointmentData->getEndTime());
            $appointment->updatedTimestamps();

            $this->entityManager->persist($appointmentData);
            $this->entityManager->flush();
            
            foreach ($appointmentUsers as $user) {

                $appointmentUser = new AppointmentUser();
                $appointmentUser->setAppointment($appointmentData);
                $appointmentUser->setUser($user);

                $this->entityManager->persist($appointmentUser);
                $this->entityManager->flush();
            }
        
            return $this->redirectToRoute('appointment');
        }
        
        return $this->render('appointment/create.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("appointment/update/{id}", name = "appointment_update")
     * 
     * @param Request $request
     * @return Templete
     */
    public function updateAppointment(Request $request, $id)
    {   
        
        $existingUsers = [];
        $appointment = $this->entityManager->getRepository(Appointment::class)->find($id);

        if (!$appointment) {
            return $this->redirectToRoute('appointment');
        }

        $appointmentUsers = $this->entityManager->getRepository(AppointmentUser::class)->findBy(['appointment' => $appointment]);

        foreach($appointmentUsers as $as) {
            $existingUsers[] = $as->getUser();
        }

        $form = $this->createForm(AppointmentType::class, $appointment, ['existingUsers' => $existingUsers]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $appointmentData = $form->getData();
            $appointmentUsers = $form->get('users')->getData();
            
            $appointment->setName($appointmentData->getName());
            $appointment->setDate($appointmentData->getDate());
            $appointment->setStartTime($appointmentData->getStartTime());
            $appointment->setEndTime($appointmentData->getEndTime());
            $appointment->updatedTimestamps();

            $this->entityManager->persist($appointmentData);
            $this->entityManager->flush();

            $deleteAppointmentUsers = $this->entityManager->getRepository(AppointmentUser::class)->findBy(['appointment' => $appointmentData->getId()]);

            if ($deleteAppointmentUsers) {

                foreach ($deleteAppointmentUsers as $deleteAppointmentUser) {
                    
                    $this->entityManager->remove($deleteAppointmentUser);
                    $this->entityManager->flush();
                }
            }
            
            foreach ($appointmentUsers as $user) {
                
                $appointmentUser = new AppointmentUser();
                $appointmentUser->setAppointment($appointmentData);
                $appointmentUser->setUser($user);

                $this->entityManager->persist($appointmentUser);
                $this->entityManager->flush();
            }
        
            return $this->redirectToRoute('appointment');
        }

        return $this->render('appointment/update.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("appointment/delete/{id}", name="appointment_delete")
     * 
     * @param $id
     * @return RedirectToRoute appointment
     */
    public function deleteAppointment($id)
    {

        $user = $this->entityManager->getRepository(Appointment::class)->find($id);
        
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $deleteAppointmentUsers = $this->entityManager->getRepository(AppointmentUser::class)->findBy(['appointment' => $id]);
            
            foreach ($deleteAppointmentUsers as $deleteAppointmentUser) {
                
                $this->entityManager->remove($deleteAppointmentUser);
                $this->entityManager->flush();
            }

        return $this->redirectToRoute('appointment');
    }


}
