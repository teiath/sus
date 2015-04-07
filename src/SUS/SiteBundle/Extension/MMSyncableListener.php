<?php

namespace SUS\SiteBundle\Extension;

//use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\HttpKernel\KernelInterface;
use SUS\SiteBundle\Entity\MMSyncableEntity;

class MMSyncableListener
{
    protected $mmservice;
    protected $kernel;

    public function __construct(MMService $mmservice, KernelInterface $kernel) {
        $this->mmservice = $mmservice;
        $this->kernel = $kernel;
    }

    /*public function prePersist(EventArgs $args) {
        $entity = $args->getEntity();
        if(!$entity instanceof MMSyncableEntity) {
            return;
        }

        if($this->kernel->getEnvironment() == 'prod') {
            $this->mmservice->persistMM($entity);
        }
    }

    public function preUpdate(EventArgs $args) {
        $entity = $args->getEntity();
        if(!$entity instanceof MMSyncableEntity) {
            return;
        }

        if($this->kernel->getEnvironment() == 'prod') {
            $this->mmservice->persistMM($entity);
            $em = $args->getEntityManager();
            $uow = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($entity));
            $uow->recomputeSingleEntityChangeSet($meta, $entity);
        }
    }*/

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {
            if(!$entity instanceof MMSyncableEntity) {
                continue;
            }

            if($this->kernel->getEnvironment() == 'prod') {
                $this->mmservice->persistMM($entity);
                $em->persist($entity);
                $meta = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($meta, $entity);
            }
        }
    }

    public function preRemove(EventArgs $args) {
        $entity = $args->getEntity();
        if(!$entity instanceof MMSyncableEntity) {
            return;
        }
        if($this->kernel->getEnvironment() == 'prod') {
            $oldDeletedAt = $entity->getDeletedAt();
            $entity->setDeletedAt(new \DateTime('now'));
            $this->mmservice->persistMM($entity);
            $entity->setDeletedAt($oldDeletedAt);
        }
    }
}