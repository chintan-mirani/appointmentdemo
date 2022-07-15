<?php

namespace App\Entity;

use App\Repository\AppointmentUserRepository;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Annotation as MappingAnnotation;

/**
 * @ORM\Entity(repositoryClass=AppointmentUserRepository::class)
 */
class AppointmentUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Appointment", fetch="EAGER")
     * @ORM\JoinColumn(name="appointment_id", referencedColumnName="id")
     */
    private $appointment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppointment()
    {
        return $this->appointment;
    }

    public function setAppointment(Appointment $appointment): self
    {
        $this->appointment = $appointment;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
