<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Sensor;

/**
 * SensorRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SensorRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param array $filter
     * @return Sensor
     */
    public function getSensorByFilter(array $filter) {
        /**
         * @var Sensor $sensor
         */
        $sensor = $this->findOneBy($filter);
        return $sensor;
    }

    /**
     * @param array $filter
     * @return Sensor[]
     */
    public function getSensors(array $filter) {
        /**
         * @var Sensor[] $sensor
         */
        $sensor = $this->findBy($filter);
        return $sensor;
    }
}
