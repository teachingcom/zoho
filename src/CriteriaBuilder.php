<?php

namespace Asciisd\Zoho;

use Asciisd\Zoho\Exceptions\APIException;
use Asciisd\Zoho\Facades\ZohoManager;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\SearchRecordsParam;

class CriteriaBuilder
{
    public const OPERATOR_EQUALS = 'equals';
    public const OPERATOR_STARTS_WITH = 'starts_with';

    public const DEFAULT_PAGE = 1;
    public const DEFAULT_PER_PAGE = 200;

    protected $criteria = ''
    /** @var ZohoModule */;
    protected $module;
    protected $page = self::DEFAULT_PAGE;
    protected $perPage = self::DEFAULT_PER_PAGE;

    public function __construct($module)
    {
        $this->module = $module ?? ZohoManager::useModule();
    }

    /**
     * Builds a criteria with a `where` condition applied.
     *
     * @param string $operator One of `OPERATOR_*` constant values. Defaults to `equals`.
     */
    public static function where(string $field, $value, string $operator = self::OPERATOR_EQUALS, ZohoModule $module = null): self
    {
        $builder = new self($module);
        $builder->criteria = static::queryBuilder($field, $operator, $value);

        return $builder;
    }

    private static function queryBuilder(string $field, string $operator, $value): string
    {
        return "({$field}:{$operator}:{$value})";
    }

    /**
     * Builds a `ParameterMap` instance for searching records by email.
     *
     * @return ParameterMap
     * @throws SDKException
     */
    public static function byEmail(string $email, int $page = self::DEFAULT_PAGE, int $perPage = self::DEFAULT_PER_PAGE): ParameterMap
    {
        $params = self::buildParameterMap($page, $perPage);
        $params->add(SearchRecordsParam::email(), $email);

        return $params;
    }

    /**
     * Builds a `ParameterMap` instance for searching records by phone.
     *
     * @return ParameterMap
     * @throws SDKException
     */
    public static function byPhone(string $phone, int $page = self::DEFAULT_PAGE, int $perPage = self::DEFAULT_PER_PAGE): ParameterMap
    {
        $params = self::buildParameterMap($page, $perPage);
        $params->add(SearchRecordsParam::phone(), $phone);

        return $params;
    }

    /**
     * Builds a `ParameterMap` instance for searching records by word.
     *
     * @return ParameterMap
     * @throws SDKException
     */
    public static function byWord(string $word, int $page = self::DEFAULT_PAGE, int $perPage = self::DEFAULT_PER_PAGE): ParameterMap
    {
        $params = self::buildParameterMap($page, $perPage);
        $params->add(SearchRecordsParam::word(), $word);

        return $params;
    }

    /**
     * Builds a `ParameterMap` instance for searching records by email.
     *
     * @return ParameterMap
     * @throws SDKException
     */
    public static function fromCriteriaString(string $criteria, int $page = self::DEFAULT_PAGE, int $perPage = self::DEFAULT_PER_PAGE): ParameterMap
    {
        $params = self::buildParameterMap($page, $perPage);
        $params->add(SearchRecordsParam::criteria(), $criteria);

        return $params;
    }

    /**
     * Builds a `ParameterMap` instance preconfigured for pagination.
     *
     * @return ParameterMap
     * @throws SDKException
     */
    private static function buildParameterMap(int $page = self::DEFAULT_PAGE, int $perPage = self::DEFAULT_PER_PAGE): ParameterMap
    {
        $params = new ParameterMap();
        $params->add(SearchRecordsParam::page(), $page);
        $params->add(SearchRecordsParam::perPage(), $perPage);

        return $params;
    }

    /**
     * Adds an `and` condition using the `starts_with` operator.
     */
    public function startsWith(string $field, $value): self
    {
        $this->criteria .= ' and ' . $this->queryBuilder($field, 'starts_with', $value);

        return $this;
    }

    /**
     * Adds an `and` condition using one of the `OPERATOR_*` constants.
     *
     * @param string $operator One of `OPERATOR_*` constant values. Defaults to `equals`.
     */
    public function andWhere(string $field, $value, string $operator = self::OPERATOR_EQUALS): self
    {
        $this->criteria .= ' and ' . $this->queryBuilder($field, $operator, $value);

        return $this;
    }

    /**
     * Adds an `or` condition using one of the `OPERATOR_*` constants.
     *
     * @param string $operator One of `OPERATOR_*` constant values. Defaults to `equals`.
     */
    public function orWhere(string $field, $value, string $operator = self::OPERATOR_EQUALS): self
    {
        $this->criteria .= ' or ' . $this->queryBuilder($field, $operator, $value);

        return $this;
    }

    public function toString(): string
    {
        return $this->getCriteria() ?? '';
    }

    public function getCriteria(): string
    {
        return $this->criteria;
    }

    public function page(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function perPage(int $per_page): self
    {
        $this->perPage = $per_page;

        return $this;
    }

    /**
     * Compiles the current criteria into a `ParameterMap` instance.
     * @throws SDKException
     */
    public function toParameterMap(): ParameterMap
    {
        $params = new ParameterMap();
        $params->add(SearchRecordsParam::criteria(), $this->criteria);
        $params->add(SearchRecordsParam::page(), $this->page);
        $params->add(SearchRecordsParam::perPage(), $this->perPage);

        return $params;
    }

    /**
     * Performs the search using the configured criteria and returns the result.
     *
     * @return Record[]
     * @throws APIException
     * @throws SDKException
     */
    public function get(): array
    {
        return $this->module->searchRecordsByCriteria($this->getCriteria(), $this->page, $this->perPage);
    }

    /**
     * Performs the search using the configured criteria and returns the result.
     *
     * @return Record[]
     * @throws APIException
     * @throws SDKException
     */
    public function search(): array
    {
        return $this->get();
    }
}
