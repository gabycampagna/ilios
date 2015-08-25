<?php

namespace Ilios\CoreBundle\Entity\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Id\AssignedGenerator;
use Ilios\CoreBundle\Entity\CohortInterface;

/**
 * Class CohortManager
 * @package Ilios\CoreBundle\Entity\Manager
 */
class CohortManager extends AbstractManager implements CohortManagerInterface
{
    /**
     * @param array $criteria
     * @param array $orderBy
     *
     * @return CohortInterface
     */
    public function findCohortBy(
        array $criteria,
        array $orderBy = null
    ) {
        return $this->repository->findOneBy($criteria, $orderBy);
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param integer $limit
     * @param integer $offset
     *
     * @return ArrayCollection|CohortInterface[]
     */
    public function findCohortsBy(
        array $criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param CohortInterface $cohort
     * @param bool $andFlush
     * @param bool $forceId
     */
    public function updateCohort(
        CohortInterface $cohort,
        $andFlush = true,
        $forceId = false
    ) {
        $this->em->persist($cohort);

        if ($forceId) {
            $metadata = $this->em->getClassMetaData(get_class($cohort));
            $metadata->setIdGenerator(new AssignedGenerator());
        }

        if ($andFlush) {
            $this->em->flush();
        }
    }

    /**
     * @param CohortInterface $cohort
     */
    public function deleteCohort(
        CohortInterface $cohort
    ) {
        $this->em->remove($cohort);
        $this->em->flush();
    }

    /**
     * @return CohortInterface
     */
    public function createCohort()
    {
        $class = $this->getClass();
        return new $class();
    }
}