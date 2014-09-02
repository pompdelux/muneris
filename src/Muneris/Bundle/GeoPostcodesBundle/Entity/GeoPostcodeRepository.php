<?php

namespace Muneris\Bundle\GeoPostcodesBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * MaxMindCacheRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GeoPostcodeRepository extends EntityRepository
{
    protected $country;
    protected $fuzzy;
    protected $params = [];

    /**
     * Find All Cities grouped by city name.
     *
     * @param array $criterias Criterias including country and zipCode
     *
     * @return mixed
     */
    public function findCities($criterias)
    {
        $qb = $this->createQueryBuilder('g')
            ->groupBy('g.city')
            ->where('g.country = :country');
        ;

        $result = $qb->setParameters($criterias)
            ->getQuery()
            ->getResult()
        ;

        if (count($result)) {
            return $result;
        }

        return null;
    }

    /**
     * FindByFuzzy tries to find cities based on either zip code or city name
     * The zip code is guessed from many formats, some are known, some are not.
     *
     * @param  String $country Iso 2 country code
     * @param  String $fuzzy   Search value
     *
     * @return mixed
     */
    public function findByFuzzy($country, $fuzzy)
    {
        $qb = $this->createQueryBuilder('g')
            ->addSelect('CONCAT(g.country, g.city, g.zipCode) AS HIDDEN _my_sort')
            ->addGroupBy('_my_sort')
            ->where('g.country = :country')
            ->orderBy('g.city, g.zipCode')
        ;

        $fb = new FuzzyBuilder($qb, $country, $fuzzy);
        $fb->fuzz([':country' => $country]);

        $result =  $qb->setParameters($fb->getParameters())
            ->getQuery()
            ->getResult()
        ;

        if (count($result)) {
            return $result;
        }

        return null;
    }
}
