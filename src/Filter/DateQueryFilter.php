<?php
namespace NYPL\Services\Filter;

use NYPL\Starter\Filter;

class DateQueryFilter extends Filter
{
    /**
     * DateQueryFilter constructor.
     *
     * @param string $filterColumn
     * @param string $filterValue
     * @param bool   $isJsonColumn
     * @param string $id
     * @param string $operator
     */
    public function __construct($filterColumn = '', $filterValue = '', $isJsonColumn = false, $id = '', $operator = '')
    {
        // A properly formatted date should be sent as the value.
        if ($this->isValidDate($filterValue)) {
            $filterValue = $filterValue . '%';
        } else {
            $filterValue = '';
        }

        parent::__construct($filterColumn, $filterValue, $isJsonColumn, $id, $operator);
    }

    /**
     * @param $date
     * @return bool
     */
    protected function isValidDate($date)
    {
        $dateTime = \DateTime::createFromFormat("Y-m-d", $date);
        return $dateTime !== false && !array_sum($dateTime->getLastErrors());
    }

}
