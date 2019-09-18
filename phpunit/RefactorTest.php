<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class RefactorTest extends TestCase {
  /**
   * Code Igniter Instance.
   * @var object
   */
  private static $ci;
  /**
   * Package name for simplicity
   * @var string
   */
  private const PACKAGE = "francis94c/refactor-ci";

  /**
   * Prerquisites for the Unit Tests.
   *
   * @covers JWT::__construct
   */
  public static function setUpBeforeClass(): void {
    self::$ci =& get_instance();
  }
  /**
   * [testLoadPackage description]
   */
  public function testLoadPackage():void {
    self::$ci->load->package(self::PACKAGE);
    $this->asserTrue(isset($ci->refactor));
  }
}
