<?php
namespace NYPL\Services\Model;

use NYPL\Starter\Model;
use NYPL\Starter\Model\ModelTrait\TranslateTrait;

/**
 * @SWG\Definition(title="ElectronicDocumentRequest", type="object")
 *
 * @package NYPL\Services\Model
 */
class ElectronicDocumentData extends Model
{
    use TranslateTrait;

    /**
     * @SWG\Property(example="user@example.com")
     * @var string
     */
    public $emailAddress;

    /**
     * @SWG\Property(example="Chapter One")
     * @var string
     */
    public $chapterTitle;

    /**
     * @SWG\Property(example="100")
     * @var string
     */
    public $startPage;

    /**
     * @SWG\Property(example="150")
     * @var string
     */
    public $endPage;

    /**
     * @SWG\Property(example="Summer 2017")
     * @var string
     */
    public $issue;

    /**
     * @SWG\Property(example="159")
     * @var string
     */
    public $volume;

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return string
     */
    public function getChapterTitle()
    {
        return $this->chapterTitle;
    }

    /**
     * @param string $chapterTitle
     */
    public function setChapterTitle($chapterTitle)
    {
        $this->chapterTitle = $chapterTitle;
    }

    /**
     * @return string
     */
    public function getStartPage()
    {
        return $this->startPage;
    }

    /**
     * @param string $startPage
     */
    public function setStartPage($startPage)
    {
        $this->startPage = $startPage;
    }

    /**
     * @return string
     */
    public function getEndPage()
    {
        return $this->endPage;
    }

    /**
     * @param string $endPage
     */
    public function setEndPage($endPage)
    {
        $this->endPage = $endPage;
    }

    /**
     * @return string
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * @param string $issue
     */
    public function setIssue($issue)
    {
        $this->issue = $issue;
    }

    /**
     * @return string
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * @param string $volume
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
    }
}
