<?php

namespace Asciisd\Zoho;

use Asciisd\Zoho\Exceptions\APIException;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\org\APIException as OrgAPIException;
use com\zoho\crm\api\org\Org;
use com\zoho\crm\api\org\OrgOperations;
use com\zoho\crm\api\org\ResponseWrapper as OrgResponseWrapper;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\users\GetUsersParam;
use com\zoho\crm\api\users\ResponseWrapper as UserResponseWrapper;
use com\zoho\crm\api\users\User;
use com\zoho\crm\api\users\UsersOperations;
use Illuminate\Support\Arr;

class ZohoOrganization
{
    /** @var OrgOperations */
    protected $orgOperations;
    /** @var UsersOperations */
    protected $userOperations;

    public function __construct()
    {
        $this->orgOperations = new OrgOperations;
        $this->userOperations = new UsersOperations;
    }

    public function setOrgOperations(OrgOperations $orgOperations): self
    {
        $this->orgOperations = $orgOperations;

        return $this;
    }

    public function setUsersOperations(UsersOperations $userOperations): self
    {
        $this->userOperations = $userOperations;

        return $this;
    }

    /**
     * Get the organization in form of Organization instance.
     *
     * @throws APIException
     */
    public function getOrganizationDetails(): Org
    {
        $responseObj = $this->orgOperations->getOrganization()->getObject();
        if ($responseObj instanceof OrgResponseWrapper) {
            return Arr::first($responseObj->getOrg());
        }

        /** @var OrgAPIException $responseObj */
        throw new APIException($responseObj);
    }

    /**
     * Get dummy organization object.
     */
    public function getOrganizationInstance(): Org
    {
        return new Org();
    }

    /**
     * Get the current user.
     *
     * @throws APIException
     * @throws SDKException
     */
    public function getCurrentUser(): User
    {
        $params = new ParameterMap();
        $params->add(GetUsersParam::type(), 'CurrentUser');
        $responseObj = $this->userOperations->getUsers($params)->getObject();
        if ($responseObj instanceof UserResponseWrapper) {
            return Arr::first($responseObj->getUsers());
        }

        /** @var OrgAPIException $responseObj */
        throw new APIException($responseObj);
    }
}
