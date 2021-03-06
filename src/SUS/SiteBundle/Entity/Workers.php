<?php

namespace SUS\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * Workers
 *
 * @ORM\Table(name="workers", options={"comment":"Περιέχει πληροφορίες σχετικά με τα Στοιχεία Εργαζόμενων."}, uniqueConstraints={@ORM\UniqueConstraint(name="registry_no_UNIQUE", columns={"registry_no"})}, indexes={@ORM\Index(name="tax_number_idx", columns={"tax_number"}), @ORM\Index(name="lastname_idx", columns={"lastname"}), @ORM\Index(name="firstname_idx", columns={"firstname"}), @ORM\Index(name="fathername_idx", columns={"fathername"}), @ORM\Index(name="sex_idx", columns={"sex"})})
 * @ORM\Entity
 */
//class Workers extends MMSyncableEntity
class Workers
{
    /**
     * @var integer
     *
     * @ORM\Column(name="worker_id", type="integer", nullable=false, options={"comment":"Ο Κωδικός ID του Εργαζόμενου."})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $workerId;

    /**
     * @var Unit
     *
     * @ORM\OneToOne(targetEntity="Unit", inversedBy="manager")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unit_id", referencedColumnName="unit_id")
     * })
     */
    private $unit;

    /**
     * @var Unit
     *
     * @ORM\ManyToMany(targetEntity="Unit", mappedBy="responsibles")
     */
    private $responsibleUnits;

    /**
     * @var string
     *
     * @ORM\Column(name="registry_no", type="string", length=255, nullable=true, options={"comment":"Ο Κωδικός Μητρώου του Εργαζόμενου."})
     */
    private $registryNo;

    /**
     * @var string
     *
     * @ORM\Column(name="tax_number", type="string", length=255, nullable=true, options={"comment":"Το ΑΦΜ του Εργαζόμενου."})
     */
    private $taxNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true, options={"comment":"Το Επίθετο του Εργαζόμενου."})
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true, options={"comment":"Το Όνομα του Εργαζόμενου."})
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="fathername", type="string", length=255, nullable=true, options={"comment":"Το Όνομα Πατρός του Εργαζόμενου."})
     */
    private $fathername;

    /**
     * @var string
     *
     * @ORM\Column(name="sex", type="string", length=1, nullable=true, options={"comment":"Το Φύλο του Εργαζόμενου."})
     */
    private $sex;

    /**
     * @var WorkerSpecializations
     *
     * @ORM\ManyToOne(targetEntity="WorkerSpecializations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="worker_specialization_id", referencedColumnName="worker_specialization_id")
     * })
     */
    private $workerSpecialization;

    public function __construct() {
        $this->responsibleUnits = new ArrayCollection();
    }

    public function getId() {
        return $this->getWorkerId();
    }

    public function getWorkerId() {
        return $this->workerId;
    }

    public function setWorkerId($workerId) {
        $this->workerId = $workerId;
    }

    public function getUnit() {
        return $this->unit;
    }

    public function setUnit(Unit $unit) {
        $this->unit = $unit;
    }

    public function getResponsibleUnits() {
        return $this->responsibleUnits;
    }

    public function setResponsibleUnits(Unit $responsibleUnits) {
        $this->responsibleUnits = $responsibleUnits;
    }

    public function getRegistryNo() {
        return $this->registryNo;
    }

    public function setRegistryNo($registryNo) {
        $this->registryNo = $registryNo;
    }

    public function getTaxNumber() {
        return $this->taxNumber;
    }

    public function setTaxNumber($taxNumber) {
        $this->taxNumber = $taxNumber;
    }

    public function getLastname() {
        return $this->lastname;
    }

    public function setLastname($lastname) {
        $this->lastname = $lastname;
    }

    public function getFirstname() {
        return $this->firstname;
    }

    public function setFirstname($firstname) {
        $this->firstname = $firstname;
    }

    public function getFathername() {
        return $this->fathername;
    }

    public function setFathername($fathername) {
        $this->fathername = $fathername;
    }

    public function getSex() {
        return $this->sex;
    }

    public function setSex($sex) {
        $this->sex = $sex;
    }

    public function getWorkerSpecialization() {
        return $this->workerSpecialization;
    }

    public function setWorkerSpecialization(WorkerSpecializations $workerSpecialization) {
        $this->workerSpecialization = $workerSpecialization;
    }

    public function __toString() {
        return $this->getFirstname().' '.$this->getLastname();
    }

    public function isActive() {
        return true;
    }
}
