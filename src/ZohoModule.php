<?php

namespace Asciisd\Zoho;

use Asciisd\Zoho\Exceptions\APIException;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\modules\Module;
use com\zoho\crm\api\modules\ModulesOperations;
use com\zoho\crm\api\modules\ResponseWrapper as ModulesResponseWrapper;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\ResponseWrapper as RecordResponseWrapper;
use com\zoho\crm\api\util\APIException as SDKAPIException;
use com\zoho\crm\api\util\APIResponse;
use Illuminate\Support\Arr;

class ZohoModule
{
    protected $module_api_name;
    /** @var ModulesOperations */
    protected $modulesOperations;
    /** @var RecordOperations */
    protected $recordOperations;

    /**
     * ZohoModule constructor.
     */
    public function __construct(string $module_api_name = 'Leads')
    {
        $this->module_api_name = $module_api_name;
        $this->modulesOperations = new ModulesOperations;
        $this->recordOperations = new RecordOperations;
    }

    public function getApiName(): string
    {
        return $this->module_api_name;
    }

    public function setModulesOperations(ModulesOperations $modulesOperations): self
    {
        $this->modulesOperations = $modulesOperations;

        return $this;
    }

    public function setRecordOperations(RecordOperations $recordOperations): self
    {
        $this->recordOperations = $recordOperations;

        return $this;
    }

    /**
     * to get the the modules in form of ZCRMModule instances array
     *
     * @return Module[]
     * @throws APIException
     */
    public function getAllModules(): array
    {
        $response = $this->modulesOperations->getModules();

        return $this->processResponseObject($response, ModulesResponseWrapper::class, function (ModulesResponseWrapper $responseObj) {
            return $responseObj->getModules();
        }, []);
    }

    /**
     * to get the module in form of ZCRMModule instance
     * @throws APIException
     */
    public function getModule(): ?Module
    {
        $response = $this->modulesOperations->getModule($this->module_api_name);

        return Arr::first($this->processResponseObject($response, ModulesResponseWrapper::class, function (ModulesResponseWrapper $responseObj) {
            return $responseObj->getModules();
        }, []));
    }

    /**
     * Get record instance.
     */
    public function getRecordInstance(string $record_id = null): Record
    {
        $record = new Record;
        if ($record_id) {
            $record->setId($record_id);
        }

        return $record;
    }

    /**
     * Get dummy Module object.
     */
    public function getModuleInstance(): Module
    {
        $module = new Module;
        $module->setAPIName($this->module_api_name);

        return $module;
    }

    /**
     * get the records array of given module api name
     *
     * @return Record[]
     * @throws APIException
     */
    public function getRecords(): array
    {
        $response = $this->recordOperations->getRecords($this->module_api_name);

        return $this->processResponseObject($response, RecordResponseWrapper::class, function (RecordResponseWrapper $responseObj) {
            return $responseObj->getData();
        }, []);
    }

    /**
     * Get the record object of given module api name and record id.
     *
     * @throws APIException
     */
    public function getRecord(string $record_id): ?Record
    {
        $response = $this->recordOperations->getRecord($record_id, $this->module_api_name);

        return Arr::first($this->processResponseObject($response, RecordResponseWrapper::class, function (RecordResponseWrapper $responseObj) {
            return $responseObj->getData();
        }, []));
    }

    /**
     * Search module records by word.
     *
     * @return Record[]
     * @throws SDKException
     * @throws APIException
     */
    public function searchRecordsByWord(string $word, int $page = 1, int $perPage = 200): array
    {
        $param_map = CriteriaBuilder::byWord($word, $page, $perPage);

        $response = $this->recordOperations->searchRecords($this->module_api_name, $param_map);

        return $this->processResponseObject($response, RecordResponseWrapper::class, function (RecordResponseWrapper $responseObj) {
            return $responseObj->getData();
        }, []);
    }

    /**
     * Search module records by phone number.
     *
     * @return Record[]
     * @throws SDKException
     * @throws APIException
     */
    public function searchRecordsByPhone(string $phone, int $page = 1, int $perPage = 200): array
    {
        $param_map = CriteriaBuilder::byPhone($phone, $page, $perPage);

        $response = $this->recordOperations->searchRecords($this->module_api_name, $param_map);

        return $this->processResponseObject($response, RecordResponseWrapper::class, function (RecordResponseWrapper $responseObj) {
            return $responseObj->getData();
        }, []);
    }

    /**
     * Search module records by email.
     *
     * @return Record[]
     * @throws SDKException
     * @throws APIException
     */
    public function searchRecordsByEmail(string $email, int $page = 1, int $perPage = 200): array
    {
        $param_map = CriteriaBuilder::byEmail($email, $page, $perPage);

        $response = $this->recordOperations->searchRecords($this->module_api_name, $param_map);

        return $this->processResponseObject($response, RecordResponseWrapper::class, function (RecordResponseWrapper $responseObj) {
            return $responseObj->getData();
        }, []);
    }

    /**
     * Search module records by criteria string.
     *
     * @return Record[]
     * @throws APIException
     * @throws SDKException
     */
    public function searchRecordsByCriteria(string $criteria, int $page = 1, int $perPage = 200): array
    {
        $params = CriteriaBuilder::fromCriteriaString($criteria, $page, $perPage);

        $response = $this->recordOperations->searchRecords($this->module_api_name, $params);

        return $this->processResponseObject($response, RecordResponseWrapper::class, function (RecordResponseWrapper $responseObj) {
            return $responseObj->getData();
        }, []);
    }

    /**
     * Add new entities to a module.
     *
     * @param string ...$trigger Optional trigger(s) to include.
     *
     * @throws APIException
     */
    public function insert(Record $record, string ...$trigger): Record
    {
        $request = new BodyWrapper();
        $request->setData([$record]);
        $request->setTrigger($trigger);

        $response = $this->recordOperations->createRecords($this->module_api_name, $request);

        return Arr::first($this->processResponseObject($response, ActionWrapper::class, function (ActionWrapper $responseObj) {
            return $responseObj->getData();
        }, []));
    }

    /**
     * Create record instance that contains the array keys and values.
     *
     * @param array $args Key/value pairs to be set on the `Record` instance.
     * @param string ...$trigger Optional trigger(s) to include.
     *
     * @throws APIException
     */
    public function create(array $args, string ...$trigger): Record
    {
        $record = $this->getRecordInstance();

        foreach ($args as $key => $value) {
            $record->addKeyValue($key, $value);
        }

        return $this->insert($record, ...$trigger);
    }

    /**
     * update existing entities in the module.
     *
     * @param Record $record Record to update.
     * @param string ...$trigger Optional trigger(s) to include.
     *
     * @return Record
     * @throws APIException
     */
    public function update(Record $record, string ...$trigger): Record
    {
        $request = new BodyWrapper();
        $request->setData([$record]);
        $request->setTrigger($trigger);

        $response = $this->recordOperations->updateRecord($record->getId(), $this->module_api_name, $request);

        return Arr::first($this->processResponseObject($response, ActionWrapper::class, function (ActionWrapper $responseObj) {
            return $responseObj->getData();
        }, []));
    }

    /**
     * Searches records within the module using the given criteria.
     *
     * @return Record[]
     * @throws APIException
     * @throws SDKException
     */
    public function search(CriteriaBuilder $criteria): array
    {
        $response = $this->recordOperations->searchRecords($this->module_api_name, $criteria->toParameterMap());

        return $this->processResponseObject($response, RecordResponseWrapper::class, function (RecordResponseWrapper $responseObj) {
            return $responseObj->getData();
        }, []);
    }

    /**
     * Builds a `where` criteria from the given field, value, and operator.
     *
     * @param string $operator One of `CriteriaBuilder::OPERATOR_*` constant values. Defaults to `equals`.
     */
    public function where(string $field, $value, string $operator = CriteriaBuilder::OPERATOR_EQUALS): CriteriaBuilder
    {
        return CriteriaBuilder::where($field, $value, $operator, $this);
    }

    /**
     * If `$response->getObject()` returns an instance of `$successClass`, return the data from the `$dataGetter`
     * callable, otherwise throw an APIException.
     *
     * @param callable $dataGetter A callable which accepts an instance of the `$successClass` as an argument and returns the data.
     *
     * @return mixed
     * @throws APIException
     */
    private function processResponseObject(APIResponse $response, string $successClass, callable $dataGetter, $default = null)
    {
        $responseObj = $response->getObject();
        if (null === $responseObj) {
            return $default;
        }
        if (is_a($responseObj, $successClass)) {
            return call_user_func($dataGetter, $responseObj) ?? $default;
        }

        /** @var SDKAPIException $responseObj */
        throw new APIException($responseObj);
    }
}
