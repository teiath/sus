<?php

namespace SUS\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkerSpecializations
 *
 * @ORM\Table(name="worker_specializations", options={"comment":"Λεξικό με τις Ειδικότητες Εργαζομένων."}, uniqueConstraints={@ORM\UniqueConstraint(name="name_UNIQUE", columns={"name"})})
 * @ORM\Entity
 */
class WorkerSpecializations
{
    /**
     * @var integer
     *
     * @ORM\Column(name="worker_specialization_id", type="integer", nullable=false, options={"comment":"Ο Κωδικός ID της Ειδικότητας Εργαζόμενου."})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $workerSpecializationId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false, options={"comment":"Το Όνομα της Ειδικότητας Εργαζόμενου."})
     */
    private $name;
    
    public function getWorkerSpecializationId() {
        return $this->workerSpecializationId;
    }

    public function setWorkerSpecializationId($workerSpecializationId) {
        $this->workerSpecializationId = $workerSpecializationId;
    }

    public function getId() {
        return $this->getWorkerSpecializationId();
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

}
