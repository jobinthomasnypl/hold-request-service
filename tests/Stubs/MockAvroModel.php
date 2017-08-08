<?php
namespace NYPL\Services\Test\Stubs;

use NYPL\Starter\AvroLoader;

class MockAvroModel
{
    public $model;
    public $data;

    public function __construct(\NYPL\Starter\Model $model, string $data)
    {
        $this->setModel($model);
        $this->setData($data);
    }

    public function modelAsArray()
    {
        $this->getModel()->translate(json_decode($this->getData(), true));

        AvroLoader::load();

        $io = new \AvroStringIO();
        $writer = new \AvroIODatumWriter($this->getModel()->getAvroSchema());
        $encoder = new \AvroIOBinaryEncoder($io);

        $dataArray = json_decode(json_encode($this->getModel()), true);

        $writer->write($dataArray, $encoder);

        return $io->string();
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
