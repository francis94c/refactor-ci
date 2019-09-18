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
    $this->assertTrue(isset(self::$ci->refactor));
  }
  /**
   * [testDirectUnsetRule description]
   *
   * @depends testLoadPackage
   */
  public function testDirectUnsetRule():void {
    $payload = [
      'name'     => 'collins',
      'pc_brand' => 'HP',
      'phone'    => '+2349086756453',
      'school'   => 'Delloite',
      'company'  => 'Google'
    ];
    $rule = [
      'unset' => [
        'school',
        'phone'
      ]
    ];
    self::$ci->refactor->run($payload, $rule);
    $this->assertFalse(isset($payload['school']));
    $this->assertFalse(isset($payload['phone']));
    $this->assertTrue(isset($payload['company']));
  }
}
