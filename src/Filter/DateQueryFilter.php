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
     * Validate a string as a valid date ensuring it has not been adjusted by the DateTime::createFromFormat()
     * construct which errs on the side of caution creating a valid date from invalid input,
     * i.e. 2017-08-32 becomes 2017-09-01.
     *
     * @param $date
     * @return bool
     */
    protected function isValidDate($date)
    {
        $dateTime = \DateTime::createFromFormat("Y-m-d", $date);
        return $dateTime !== false && !array_sum($dateTime->getLastErrors());
    }

}
