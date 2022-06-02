<?php

namespace Asciisd\Zoho\Tests\Integration;

use Asciisd\Zoho\Exceptions\APIException;
use Asciisd\Zoho\ZohoOrganization;
use com\zoho\crm\api\org\APIException as OrgAPIException;
use com\zoho\crm\api\org\Org;
use com\zoho\crm\api\org\OrgOperations;
use com\zoho\crm\api\org\ResponseWrapper as OrgResponseWrapper;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\users\APIException as UsersAPIException;
use com\zoho\crm\api\users\ResponseWrapper as UsersResponseWrapper;
use com\zoho\crm\api\users\User;
use com\zoho\crm\api\users\UsersOperations;
use com\zoho\crm\api\util\APIResponse;
use com\zoho\crm\api\util\Choice;
use Mockery;

class ZohoOrganizationTest extends IntegrationTestCase
{
    /** @var ZohoOrganization */
    private $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = new ZohoOrganization();
    }

    public function test_it_can_instantiate_an_organization(): void
    {
        $organization = $this->org->getOrganizationInstance();

        self::assertEquals(new Org(), $organization);
    }

    public function test_it_can_get_organization_details(): void
    {
        $responseOrg = new Org();
        $responseWrapper = Mockery::mock(OrgResponseWrapper::class)->shouldReceive(['getOrg' => [$responseOrg]])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $orgOps = Mockery::mock(OrgOperations::class);
        $orgOps->shouldReceive(['getOrganization' => $apiResponse]);
        $this->org->setOrgOperations($orgOps);

        $actual = $this->org->getOrganizationDetails();

        self::assertSame($responseOrg, $actual);
    }

    public function test_it_throws_exception_when_get_organization_details_receives_exception_response(): void
    {
        $responseWrapper = Mockery::mock(OrgAPIException::class);
        $responseWrapper->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $orgOps = Mockery::mock(OrgOperations::class);
        $orgOps->shouldReceive(['getOrganization' => $apiResponse]);
        $this->org->setOrgOperations($orgOps);

        $this->expectExceptionObject(new APIException($responseWrapper));
        $this->org->getOrganizationDetails();
    }

    public function test_it_can_get_current_user(): void
    {
        $responseUser = new User();
        $responseWrapper = Mockery::mock(UsersResponseWrapper::class)->shouldReceive(['getUsers' => [$responseUser]])->getMock();
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $usersOps = Mockery::mock(UsersOperations::class);
        $usersOps->shouldReceive(['getUsers' => $apiResponse]);
        $this->org->setUsersOperations($usersOps);

        $actual = $this->org->getCurrentUser();

        self::assertSame($responseUser, $actual);
    }

    public function test_it_throws_exception_when_get_user_details_receives_exception_response(): void
    {
        $responseWrapper = Mockery::mock(UsersAPIException::class);
        $responseWrapper->shouldReceive(['getMessage' => new Choice('the exception message')]);
        $apiResponse = Mockery::mock(APIResponse::class)->shouldReceive(['getObject' => $responseWrapper])->getMock();
        $usersOps = Mockery::mock(UsersOperations::class);
        $usersOps->shouldReceive('getUsers')
            ->withArgs(function (ParameterMap $arg0) {
                return ['type' => 'CurrentUser'] == $arg0->getParameterMap();
            })
            ->andReturn($apiResponse);
        $this->org->setUsersOperations($usersOps);

        $this->expectExceptionObject(new APIException($responseWrapper));
        $this->org->getCurrentUser();
    }
}
