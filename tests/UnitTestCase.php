<?php
use Phalcon\DI,
    \Phalcon\Test\UnitTestCase as PhalconTestCase;

abstract class UnitTestCase extends PhalconTestCase {

    /**
     * @var \Voice\Cache
     */
    protected $_cache;

    /**
     * @var \Phalcon\Config
     */
    protected $_config;

    /**
     * @var bool
     */
    private $_loaded = false;

    protected $di;

    public function setUp(Phalcon\DiInterface $di = NULL, Phalcon\Config $config = NULL) {

        $this->_loaded = true;
        // Load any additional services that might be required during testing
        $this->di = DI::getDefault();

        // get any DI components here. If you have a config, be sure to pass it to the parent
        parent::setUp($this->di);
    }

    public function tearDown()
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     * Check if the test case is setup properly
     * @throws \PHPUnit_Framework_IncompleteTestError;
     */
    public function __destruct() {
        if(!$this->_loaded) {
            throw new \PHPUnit_Framework_IncompleteTestError('Please run parent::setUp().');
        }
    }
}