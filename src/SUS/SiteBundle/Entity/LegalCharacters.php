<?php

namespace SUS\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LegalCharacters
 *
 * @ORM\Table(name="legal_characters", options={"comment":"Λεξικό με τους Νομικούς Χαρακτήρες Μονάδων."})
 * @ORM\Entity
 */
class LegalCharacters
{
    /**
     * @var integer
     *
     * @ORM\Column(name="legal_character_id", type="integer", nullable=false, options={"comment":"Ο Κωδικός ID του Νομικού Χαρακτήρα Μονάδων."})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $legalCharacterId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false, options={"comment":"Το Όνομα του Νομικού Χαρακτήρα Μονάδων."})
     */
    private $name;
    
    public function getLegalCharacterId() {
        return $this->legalCharacterId;
    }

    public function setLegalCharacterId($legalCharacterId) {
        $this->legalCharacterId = $legalCharacterId;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }
    
    public function __toString() {
        return $this->getName();
    }
}
