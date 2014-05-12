<?php

namespace SUS\SiteBundle\Extension;

use SUS\SiteBundle\Exception\MMException;
use SUS\SiteBundle\Entity\Unit;
use SUS\SiteBundle\Extension\MMSyncableListener;
use SUS\SiteBundle\Entity\MMSyncableEntity;

class MMService {
    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function findUnit($mmid) {
        $em = $this->container->get('doctrine')->getManager();
        $repo = $em->getRepository('SUS\SiteBundle\Entity\Unit');
        $unit = $repo->find($mmid);
        $yesterday = new \DateTime('yesterday');
        if(!isset($unit) || $unit->getUpdatedAt() < $yesterday) {
            // Query the MM and try to find the unit
            $mmUnitEntries = $this->queryUnits(array(
                'mm_id' => $mmid,
                'count' => 1,
            ));
            if(count($mmUnitEntries) == 1) {
                $unit = $this->hydrateUnit($mmUnitEntries[0]);
            } elseif(count($mmUnitEntries) > 1) {
                throw new MMException('Found more than one unit: '.count($mmUnitEntries));
            } else {
                $unit = null;
            }
        }
        return $unit;
    }

    public function findUnitsBy(array $filters = array()) {
        $results = array();
        $params = array();
        if(isset($filters['mm_id']) && $filters['mm_id'] != '') {
            $params['mm_id'] = $filters['mm_id'];
        }
        if(isset($filters['registry_no']) && $filters['registry_no'] != '') {
            $params['registry_no'] = $filters['registry_no'];
        }
        if(isset($filters['name']) && $filters['name'] != '') {
            $params['name'] = $filters['name'];
        }
        if(isset($filters['fy']) && $filters['fy'] != '') {
            $params['implementation_entity'] = $filters['fy'];
        }
        if(isset($filters['ldapuid']) && $filters['ldapuid'] != '') {
            /* ldap – Πίνακας λογαριασμών ldap
            Πεδίο	Τύπος	Όνομα Πεδίου	Περιγραφή
            ldap_id	int(11)		Ο κωδικός του λογαριασμού ldap
            uid	varchar(255)		To uid του λογαριασμού ldap
            unit_id	int(11)		Η μοναδα που ανήκει ο λογαριασμός ldap */
            $params['mm_id'] = '1000003';
        }
        $mmUnitEntries = $this->queryUnits($params);
        /*foreach($mmUnitEntries as $curMmUnitEntry) {
            $results[] = $this->hydrateUnit($curMmUnitEntry);
        }
        $this->container->get('doctrine')->getManager()->flush($results);
        return $results;*/
        return $mmUnitEntries;
    }

    public function findOneUnitBy(array $filters = array()) {
        $units = $this->findUnitsBy($filters+array('limit' => 1));
        if(!isset($units[0])) {
            throw new MMException('The unit was not found');
        }
        if(count($units) > 1) {
            throw new MMException('Found more than one unit: '.count($units));
        }
        return $units[0];
    }

    public function persistMM(MMSyncableEntity $entity) {
        if($entity instanceof Unit) {
            return $this->persistUnit($entity);
        } else {
            throw new MMException('Unsupported entity');
        }
    }

    protected function hydrateUnit($entry, $flush = false) {
        throw new \Exception('Not supported');
        // Unit not found or its too old. Query the WS for fresh data.
        /*$em = $this->container->get('doctrine')->getManager();

        $unit = new Unit;
        $unit->setMmId($entry->mm_id);
        $unit->setUnitId($entry->mm_id);
        $unit->setState($em->find('SUS\SiteBundle\Entity\States', $entry->state));
        $unit->setName($entry->name);
        $unit->setPostalCode($entry->postal_code);
        $unit->setRegistryNo($entry->registry_no);
        $unit->setStreetAddress($entry->street_address);
        $unit->setCategoryName($entry->category);
        $unit->setCreatedAt(new \DateTime('now'));
        $unit->setUpdatedAt(new \DateTime('now'));

        $unit = $em->merge($unit);
        if($flush == true) {
            $em->flush($unit);
        }

        return $unit;*/
    }

    protected function queryUnits($params = array()) {
        if(!isset($params['limit']) || $params['limit'] == '') {
            $params['count'] = 10;
        } else {
            $params['count'] = $params['limit'];
        }
        if(!isset($params['startat']) || $params['startat'] == '') {
            $params['startat'] = 0;
        }
        /*if(!isset($params['category']) || $params['category'] == '') {
            "category" => "ΣΧΟΛΙΚΕΣ ΚΑΙ ΔΙΟΙΚΗΤΙΚΕΣ ΜΟΝΑΔΕΣ",
        }*/
        return $this->queryMM('units', $params);
    }

    protected function queryMM($resource, $params = array()) {
        $username = "mmsch";
        $password = "mmsch";
        $server = 'http://mmsch.teiath.gr/ver4/api/'.$resource;

        $curl = curl_init ($server);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD,  $username.":".$password);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode( $params ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


        $data = curl_exec ($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($http_status == 200)
        {
            $decodedData = json_decode($data);
            if(!$decodedData || !isset($decodedData->data)) {
                throw new MMException('MMSCH Error: '.$data);
            }
            return $decodedData->data;
        }
        else
        {
            throw new MMException('MMSCH Error: '.$data);
        }
    }

    public function persistUnit(Unit $unit) {
        if($unit->getMmSyncId() != null) {
            $method = 'PUT';
            $extraParams = array('unit_id' => $unit->getMmSyncId());
        } else {
            $curUnit = $this->findUnitsBy(array('name' => $unit->getName()));
            if(isset($curUnit[0])) { // Check if already exists
                $unit->setMmSyncId($curUnit->mm_id);
                $unit->setMmSyncLastUpdateDate(new \DateTime('now'));
                return;
            }
            $method = 'POST';
            $extraParams = array();
        }
        $params = array_merge($extraParams, array(
                "mm_id" => $unit->getMmSyncId(),
                "name" => $unit->__toString(),
                "source" => 'SUS',
                "category" => $unit->getCategory()->getCategoryId(),
                "suspended" => $unit->getDeletedAt() instanceof \DateTime ? true : false,
                "state" => $unit->getState()->getStateId(),
                //"education_level" => $unit->getEducationLevel()->getEducationalLevelId(),
                "special_name" => $unit->getSpecialName(),
                "region_edu_admin" => $unit->getRegionEduAdmin()->getRegionEduAdminId(),
                "edu_admin" => $unit->getEduAdmin()->getEduAdminId(),
                //"implementation_entity" => $unit->getEduAdmin()->getImplementationEntity()->getImplementationEntityId(),
                //"transfer_area" => $unit->getTransferArea()->getId(),
                "municipality" => $unit->getMunicipality() != null ? $unit->getMunicipality()->getMunicipalityId() : null,
                "prefecture" => $unit->getPrefecture() != null ? $unit->getPrefecture()->getPrefectureId() : null,
                "unit_type" => $unit->getUnitType() != null ? $unit->getUnitType()->getUnitTypeId() : null,
                //"operation_shift" => $unit->getOperationShift()->getOperationShiftId(),
                //"legal_character" => $unit->getLegalCharacter()->getLegalCharacterId(),
                //"orientation_type" => $unit->getOrientationType()->getOrientationTypeId(),
                //"special_type" => $unit->getSpecialType()->getSpecialTypeId(),
                "postal_code" => $unit->getPostalCode(),
                //"area_team_number" => $unit->getAreaTeamNumber(),
                "email" => $unit->getEmail(),
                "fax_number" => $unit->getFaxNumber(),
                "street_address" => $unit->getStreetAddress(),
                "phone_number" => $unit->getPhoneNumber(),
                "tax_number" => $unit->getTaxNumber(),
                "tax_office" => $unit->getTaxOffice() != null ? $unit->getTaxOffice()->getTaxOfficeId() : null,
                "comments" => $unit->getComments(),
                //"latitude" => '',
                //"longitude" => '',
                "positioning" => $unit->getPositioning(),
                //"fek" => '',
        ));

        $curl = curl_init("http://mmsch.teiath.gr/ver3git/api/units");

        $username = 'mmschadmin';
        $password = 'mmschadmin';
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD,  $username.":".$password);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode( $params ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $origData = curl_exec($curl);
        $data = json_decode($origData);
        if($data->status == 200) {
            if($method == 'POST') {
                $unit->setMmSyncId($data->mm_id);
                $unit->setMmSyncLastUpdateDate(new \DateTime('now'));
            }
        } else {
            throw new MMException('Error adding unit: '.$origData);
        }
    }
}
?>
