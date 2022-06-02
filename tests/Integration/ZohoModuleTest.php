<?php

namespace Asciisd\Zoho\Tests\Integration;

use Asciisd\Zoho\Exceptions\APIException;
use Asciisd\Zoho\ZohoModule;
use Asciisd\Zoho\CriteriaBuilder;
use com\zoho\crm\api\modules\APIException as ModulesAPIException;
use com\zoho\crm\api\modules\Module;
use com\zoho\crm\api\modules\ModulesOperations;
use com\zoho\crm\api\modules\ResponseWrapper as ModulesResponseWrapper;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\ActionWrapper as RecordActionWrapper;
use com\zoho\crm\api\record\APIException as RecordAPIException;
use com\zoho\crm\api\record\BodyWrapper as RecordBodyWrapper;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\ResponseWrapper as RecordResponseWrapper;
use com\zoho\crm\api\util\APIResponse;
use com\zoho\crm\api\util\Choice;
use Hamcrest\Core\IsEqual;
use Mockery;
use PHPUnit\Framework\ExpectationFailedException;

class ZohoModuleTest extends IntegrationTestCase
{
    public function test_it_can_get_all_modules()
    {
        $sdkModules = [new Module(), new Module()];
        $responseWrapper = Mockery::mock(ModulesResponseWrapper::class)->shouldReceive(['getModules' => $sdkModules])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $moduleOps = Mockery::mock(ModulesOperations::class);
        $moduleOps->shouldReceive(['getModules' => $apiResponse]);
        $module = (new ZohoModule)->setModulesOperations($moduleOps);

        $actual = $module->getAllModules();

        self::assertSame($sdkModules, $actual);
    }

    public function test_it_returns_empty_array_when_get_all_modules_returns_null_response(): void
    {
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => null])->getMock();
        $moduleOps = Mockery::mock(ModulesOperations::class);
        $moduleOps->shouldReceive(['getModules' => $apiResponse]);
        $module = (new ZohoModule)->setModulesOperations($moduleOps);

        $actual = $module->getAllModules();

        self::assertSame([], $actual);
    }

    public function test_it_throws_exception_when_get_all_modules_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(ModulesAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $moduleOps = Mockery::mock(ModulesOperations::class);
        $moduleOps->shouldReceive(['getModules' => $apiResponse]);
        $module = (new ZohoModule)->setModulesOperations($moduleOps);

        $this->expectExceptionObject(new APIException($sdkException));
        $module->getAllModules();
    }

    public function test_it_can_get_module_by_name(): void
    {
        $sdkModule = new Module();
        $responseWrapper = Mockery::mock(ModulesResponseWrapper::class)->shouldReceive(['getModules' => [$sdkModule]])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $moduleOps = Mockery::mock(ModulesOperations::class);
        $moduleOps->shouldReceive('getModule')->with('a-module')->andReturn($apiResponse);
        $module = (new ZohoModule('a-module'))->setModulesOperations($moduleOps);

        $actual = $module->getModule();

        self::assertSame($sdkModule, $actual);
    }

    public function test_it_returns_null_when_get_module_by_name_returns_null_response(): void
    {
        $responseWrapper = Mockery::mock(ModulesResponseWrapper::class)->shouldReceive(['getModules' => null])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $moduleOps = Mockery::mock(ModulesOperations::class);
        $moduleOps->shouldReceive('getModule')->with('a-module')->andReturn($apiResponse);
        $module = (new ZohoModule('a-module'))->setModulesOperations($moduleOps);

        $actual = $module->getModule();

        self::assertNull($actual);
    }

    public function test_it_throws_exception_when_get_module_by_name_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(ModulesAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $moduleOps = Mockery::mock(ModulesOperations::class);
        $moduleOps->shouldReceive(['getModule' => $apiResponse]);
        $module = (new ZohoModule)->setModulesOperations($moduleOps);

        $this->expectExceptionObject(new APIException($sdkException));
        $module->getModule();
    }

    public function test_it_can_instantiate_a_record_with_id(): void
    {
        $actual = (new ZohoModule('Blueprints'))->getRecordInstance('abc123');

        self::assertInstanceOf(Record::class, $actual);
        self::assertEquals('abc123', $actual->getId());
        self::assertNull($actual->getModifiedBy());
        self::assertNull($actual->getModifiedTime());
        self::assertNull($actual->getCreatedBy());
        self::assertNull($actual->getCreatedTime());
        self::assertNull($actual->getTag());
        self::assertEquals(['id' => 'abc123'], $actual->getKeyValues());
    }

    public function test_it_can_get_a_dummy_module_instance(): void
    {
        $module = new ZohoModule('the-module');

        $actual = $module->getModuleInstance();

        self::assertNull($actual->getName());
        self::assertNull($actual->getGlobalSearchSupported());
        self::assertNull($actual->getKanbanView());
        self::assertNull($actual->getDeletable());
        self::assertNull($actual->getDescription());
        self::assertNull($actual->getCreatable());
        self::assertNull($actual->getFilterStatus());
        self::assertNull($actual->getInventoryTemplateSupported());
        self::assertNull($actual->getModifiedTime());
        self::assertNull($actual->getPluralLabel());
        self::assertNull($actual->getPresenceSubMenu());
        self::assertNull($actual->getTriggersSupported());
        self::assertNull($actual->getId());
        self::assertNull($actual->getRelatedListProperties());
        self::assertNull($actual->getProperties());
        self::assertNull($actual->getPerPage());
        self::assertNull($actual->getVisibility());
        self::assertNull($actual->getConvertable());
        self::assertNull($actual->getEditable());
        self::assertNull($actual->getEmailtemplateSupport());
        self::assertNull($actual->getProfiles());
        self::assertNull($actual->getFilterSupported());
        self::assertNull($actual->getDisplayField());
        self::assertNull($actual->getSearchLayoutFields());
        self::assertNull($actual->getKanbanViewSupported());
        self::assertNull($actual->getShowAsTab());
        self::assertNull($actual->getWebLink());
        self::assertNull($actual->getSequenceNumber());
        self::assertNull($actual->getSingularLabel());
        self::assertNull($actual->getViewable());
        self::assertNull($actual->getAPISupported());
        self::assertSame('the-module', $actual->getAPIName());
        self::assertNull($actual->getQuickCreate());
        self::assertNull($actual->getModifiedBy());
        self::assertNull($actual->getGeneratedType());
        self::assertNull($actual->getFeedsRequired());
        self::assertNull($actual->getScoringSupported());
        self::assertNull($actual->getWebformSupported());
        self::assertNull($actual->getArguments());
        self::assertNull($actual->getModuleName());
        self::assertNull($actual->getBusinessCardFieldLimit());
        self::assertNull($actual->getCustomView());
        self::assertNull($actual->getParentModule());
        self::assertNull($actual->getTerritory());
    }

    public function test_it_can_get_records_for_given_module_api_name(): void
    {
        $sdkRecords = [new Record(), new Record()];
        $responseWrapper = Mockery::mock(RecordResponseWrapper::class)->shouldReceive(['getData' => $sdkRecords])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive('getRecords')->with('the-module')->andReturn($apiResponse);
        $module = (new ZohoModule('the-module'))->setRecordOperations($recordOps);

        $actual = $module->getRecords();

        self::assertSame($sdkRecords, $actual);
    }

    public function test_it_throws_an_exception_when_get_records_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(RecordAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive(['getRecords' => $apiResponse]);
        $module = (new ZohoModule)->setRecordOperations($recordOps);

        $this->expectExceptionObject(new APIException($sdkException));
        $module->getRecords();
    }

    public function test_it_can_get_record_by_module_api_name_and_record_id(): void
    {
        $sdkRecord = new Record();
        $responseWrapper = Mockery::mock(RecordResponseWrapper::class)->shouldReceive(['getData' => [$sdkRecord]])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive('getRecord')->with('the-record-id', 'the-module')->andReturn($apiResponse);
        $module = (new ZohoModule('the-module'))->setRecordOperations($recordOps);

        $actual = $module->getRecord('the-record-id');

        self::assertSame($sdkRecord, $actual);
    }

    public function test_it_throws_exception_when_get_record_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(RecordAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive(['getRecord' => $apiResponse]);
        $module = (new ZohoModule)->setRecordOperations($recordOps);

        $this->expectExceptionObject(new APIException($sdkException));
        $module->getRecord('record-id');
    }

    public function test_it_can_search_for_word_on_specific_module(): void
    {
        $sdkRecords = [new Record(), new Record()];
        $responseWrapper = Mockery::mock(RecordResponseWrapper::class)->shouldReceive(['getData' => $sdkRecords])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive('searchRecords')
            ->withArgs(function (string $arg0, ParameterMap $arg1): bool {
                $match = 'the-module' == $arg0;
                $match &= ['word' => 'the-word', 'page' => 2, 'per_page' => 3] == $arg1->getParameterMap();
                return $match;
            })->andReturn($apiResponse);
        $module = (new ZohoModule('the-module'))->setRecordOperations($recordOps);

        $actual = $module->searchRecordsByWord('the-word', 2, 3);

        self::assertSame($sdkRecords, $actual);
    }

    public function test_it_throws_exception_when_search_for_word_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(RecordAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive(['searchRecords' => $apiResponse]);
        $module = (new ZohoModule)->setRecordOperations($recordOps);

        $this->expectExceptionObject(new APIException($sdkException));
        $module->searchRecordsByWord('the-word');
    }

    public function test_it_can_search_for_phone_on_specific_module(): void
    {
        $sdkRecords = [new Record(), new Record()];
        $responseWrapper = Mockery::mock(RecordResponseWrapper::class)->shouldReceive(['getData' => $sdkRecords])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive('searchRecords')
            ->withArgs(function (string $arg0, ParameterMap $arg1): bool {
                $match = 'the-module' == $arg0;
                $match &= ['phone' => '855-867-5309', 'page' => 3, 'per_page' => 4] == $arg1->getParameterMap();
                return $match;
            })->andReturn($apiResponse);
        $module = (new ZohoModule('the-module'))->setRecordOperations($recordOps);

        $actual = $module->searchRecordsByPhone('855-867-5309', 3, 4);

        self::assertSame($sdkRecords, $actual);
    }

    public function test_it_throws_exception_when_search_for_phone_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(RecordAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive(['searchRecords' => $apiResponse]);
        $module = (new ZohoModule)->setRecordOperations($recordOps);

        $this->expectExceptionObject(new APIException($sdkException));
        $module->searchRecordsByPhone('800-CALL-ATT');
    }

    public function test_it_can_search_for_email_on_specific_module(): void
    {
        $sdkRecords = [new Record(), new Record()];
        $responseWrapper = Mockery::mock(RecordResponseWrapper::class)->shouldReceive(['getData' => $sdkRecords])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive('searchRecords')
            ->withArgs(function (string $arg0, ParameterMap $arg1): bool {
                $match = 'the-module' == $arg0;
                $match &= ['email' => 'bill@microsoft.com', 'page' => 4, 'per_page' => 8] == $arg1->getParameterMap();
                return $match;
            })->andReturn($apiResponse);
        $module = (new ZohoModule('the-module'))->setRecordOperations($recordOps);

        $actual = $module->searchRecordsByEmail('bill@microsoft.com', 4, 8);

        self::assertSame($sdkRecords, $actual);
    }

    public function test_it_throws_exception_when_search_for_email_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(RecordAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive(['searchRecords' => $apiResponse]);
        $module = (new ZohoModule)->setRecordOperations($recordOps);

        $this->expectExceptionObject(new APIException($sdkException));
        $module->searchRecordsByEmail('joe@dirt.com');
    }

    public function test_it_can_search_by_criteria(): void
    {
        $sdkRecords = [new Record(), new Record()];
        $responseWrapper = Mockery::mock(RecordResponseWrapper::class)->shouldReceive(['getData' => $sdkRecords])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive('searchRecords')
            ->withArgs(function (string $arg0, ParameterMap $arg1): bool {
                try {
                    self::assertEquals('the-module', $arg0);
                    self::assertEquals(['criteria' => '(Email:equals:bill@microsoft.com)', 'page' => 5, 'per_page' => 6], $arg1->getParameterMap());
                    return true;
                } catch (ExpectationFailedException $e) {
                    return false;
                }
            })->andReturn($apiResponse);
        $module = (new ZohoModule('the-module'))->setRecordOperations($recordOps);

        $actual = $module->searchRecordsByCriteria('(Email:equals:bill@microsoft.com)', 5, 6);

        self::assertSame($sdkRecords, $actual);
    }

    public function test_it_throws_exception_when_search_by_criteria_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(RecordAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive(['searchRecords' => $apiResponse]);
        $module = (new ZohoModule)->setRecordOperations($recordOps);

        $this->expectExceptionObject(new APIException($sdkException));
        $module->searchRecordsByCriteria('(Email:equals:joe@dirt.com)');
    }

    public function test_it_can_search_by_criteria_builder(): void
    {
        $sdkRecords = [new Record(), new Record()];
        $responseWrapper = Mockery::mock(RecordResponseWrapper::class)->shouldReceive(['getData' => $sdkRecords])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $criteria = new CriteriaBuilder('the-module');
        $criteria->andWhere('the_field', 'the_value', CriteriaBuilder::OPERATOR_EQUALS);
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive('searchRecords')->with('the-module', IsEqual::equalTo($criteria->toParameterMap()))->andReturn($apiResponse);
        $module = (new ZohoModule('the-module'))->setRecordOperations($recordOps);

        $actual = $module->search($criteria);

        self::assertSame($sdkRecords, $actual);
    }

    public function test_it_throws_exception_when_search_by_criteria_builder_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(RecordAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive(['searchRecords' => $apiResponse]);
        $criteria = new CriteriaBuilder('the-module');
        $module = (new ZohoModule)->setRecordOperations($recordOps);

        $this->expectExceptionObject(new APIException($sdkException));
        $module->search($criteria);
    }

    public function test_it_can_search_by_field_name(): void
    {
        $sdkRecords = [new Record(), new Record()];
        $responseWrapper = Mockery::mock(RecordResponseWrapper::class)->shouldReceive(['getData' => $sdkRecords])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $module = (new ZohoModule('the-module'))->setRecordOperations($recordOps);
        $criteria = CriteriaBuilder::where('City', 'Al Wasitah', CriteriaBuilder::OPERATOR_EQUALS, $module);
        $recordOps->shouldReceive('searchRecords')->with('the-module', IsEqual::equalTo($criteria->toParameterMap()))->andReturn($apiResponse);

        $actual = $module->where('City', 'Al Wasitah')->search();

        self::assertSame($sdkRecords, $actual);
    }

    public function test_it_throws_exception_when_search_by_field_name_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(RecordAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive(['searchRecords' => $apiResponse]);
        $module = (new ZohoModule)->setRecordOperations($recordOps);

        $this->expectExceptionObject(new APIException($sdkException));
        $module->where('City', 'Al Wasitah')->search();
    }

    public function test_it_can_search_with_multiple_criteria()
    {
        $sdkRecords = [new Record(), new Record()];
        $responseWrapper = Mockery::mock(RecordResponseWrapper::class)->shouldReceive(['getData' => $sdkRecords])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $module = (new ZohoModule('the-module'))->setRecordOperations($recordOps);
        $criteria = CriteriaBuilder::where('City', 'Al Wasitah', CriteriaBuilder::OPERATOR_EQUALS, $module)->andWhere('State', 'Al Fayyum');
        $recordOps->shouldReceive('searchRecords')->with('the-module', IsEqual::equalTo($criteria->toParameterMap()))->andReturn($apiResponse);

        $actual = $module
            ->where('City', 'Al Wasitah')
            ->andWhere('State', 'Al Fayyum')
            ->search();

        self::assertSame($sdkRecords, $actual);
    }

    public function test_it_can_insert_new_record(): void
    {
        $requestRecord = new Record;
        $responseRecord = new Record;
        $responseWrapper = Mockery::mock(RecordActionWrapper::class)->shouldReceive(['getData' => [$responseRecord]])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive('createRecords')
            ->withArgs(function(string $arg0, RecordBodyWrapper $arg1) use ($requestRecord): bool {
                try {
                    self::assertEquals('the-module', $arg0);
                    self::assertEquals(['trigger-one'], $arg1->getTrigger());
                    self::assertCount(1, $records = $arg1->getData());
                    self::assertSame($requestRecord, $records[0] ?? null);
                    return true;
                } catch (ExpectationFailedException $e) {
                    return false;
                }
            })
            ->andReturn($apiResponse);
        $module = (new ZohoModule('the-module'))->setRecordOperations($recordOps);

        $actual = $module->insert($requestRecord, 'trigger-one');

        self::assertSame($responseRecord, $actual);
    }

    public function test_it_throws_exception_when_insert_new_record_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(RecordAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive(['createRecords' => $apiResponse]);
        $module = (new ZohoModule)->setRecordOperations($recordOps);

        $this->expectExceptionObject(new APIException($sdkException));
        $module->insert(new Record);
    }

    public function test_it_can_create_new_record(): void
    {
        $sdkRecord = new Record();
        $responseWrapper = Mockery::mock(RecordActionWrapper::class)->shouldReceive(['getData' => [$sdkRecord]])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive('createRecords')
            ->withArgs(function(string $arg0, RecordBodyWrapper $arg1): bool {
                try {
                    self::assertEquals('the-module', $arg0);
                    self::assertEquals(['trigger-one', 'trigger-two'], $arg1->getTrigger());
                    self::assertCount(1, $records = $arg1->getData());
                    self::assertInstanceOf(Record::class, $record = $records[0] ?? null);
                    /** @var Record $record */
                    self::assertNull($record->getId());
                    self::assertEquals('Amr', $record->getKeyValue('First_Name'));
                    self::assertEquals('Emad', $record->getKeyValue('Last_Name'));
                    self::assertEquals('test@caveo.com.kw', $record->getKeyValue('Email'));
                    self::assertEquals('012345678910', $record->getKeyValue('Phone'));
                    return true;
                } catch (ExpectationFailedException $e) {
                    return false;
                }
            })
            ->andReturn($apiResponse);
        $module = (new ZohoModule('the-module'))->setRecordOperations($recordOps);

        $actual = $module->create(
            [
                'First_Name' => 'Amr',
                'Last_Name' => 'Emad',
                'Email' => 'test@caveo.com.kw',
                'Phone' => '012345678910',
            ],
            'trigger-one',
            'trigger-two',
        );

        self::assertSame($sdkRecord, $actual);
    }

    public function test_it_throws_exception_when_create_new_record_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(RecordAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive(['createRecords' => $apiResponse]);
        $module = (new ZohoModule)->setRecordOperations($recordOps);

        $this->expectExceptionObject(new APIException($sdkException));
        $module->create(['First_Name', 'Amr']);
    }

    public function test_it_can_update_records(): void
    {
        $requestRecord = new Record;
        $requestRecord->setId('record-id');
        $responseRecord = new Record;
        $responseWrapper = Mockery::mock(RecordActionWrapper::class)->shouldReceive(['getData' => [$responseRecord]])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive('updateRecord')
            ->withArgs(function(string $arg0, string $arg1, RecordBodyWrapper $arg2) use ($requestRecord): bool {
                try {
                    self::assertEquals('record-id', $arg0);
                    self::assertEquals('the-module', $arg1);
                    self::assertEquals([], $arg2->getTrigger());
                    self::assertCount(1, $records = $arg2->getData());
                    self::assertSame($requestRecord, $records[0] ?? null);
                    return true;
                } catch (ExpectationFailedException $e) {
                    return false;
                }
            })
            ->andReturn($apiResponse);
        $module = (new ZohoModule('the-module'))->setRecordOperations($recordOps);

        $actual = $module->update($requestRecord);

        self::assertSame($responseRecord, $actual);
    }

    public function test_it_throws_exception_when_update_record_returns_exception_response(): void
    {
        $sdkException = Mockery::mock(RecordAPIException::class);
        $sdkException->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $sdkException])->getMock();
        $recordOps = Mockery::mock(RecordOperations::class);
        $recordOps->shouldReceive(['updateRecord' => $apiResponse]);
        $module = (new ZohoModule)->setRecordOperations($recordOps);
        $record = new Record;
        $record->setId('record-id');

        $this->expectExceptionObject(new APIException($sdkException));
        $module->update($record);
    }

    public function test_it_can_build_where_criteria(): void
    {
        $module = new ZohoModule;

        $actual = $module->where('the_field', 'the value', CriteriaBuilder::OPERATOR_STARTS_WITH);

        self::assertEquals(CriteriaBuilder::where('the_field', 'the value', CriteriaBuilder::OPERATOR_STARTS_WITH, $module), $actual);
    }
}
