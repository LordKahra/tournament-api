<?php

namespace kahra\test\database;

use PHPUnit\Framework\TestCase;


abstract class ObjectCase extends TestCase implements ObjectCaseStaticFunctions {
    protected $backupGlobals = FALSE;
    const INVALID_ID = 999999;

    public static function setUpBeforeClass() {
        TestData::resetData();
    }

    public static function tearDownAfterClass() {
        // TODO: Test data teardown.
    }
}

interface ObjectCaseStaticFunctions {
    static function getTestData() : array;
}