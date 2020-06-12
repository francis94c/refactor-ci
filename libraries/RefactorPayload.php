<?php
declare(strict_types=1);

defined('BASEPATH') OR exit('No direct script access allowed');

class RefactorPayload
{
  protected $payload;
  /**
   * [__construct description]
   * @date  2019-11-02
   * @param array      $payload [description]
   */
  public function __construct($payload=[])
  {
    $this->payload = $payload;
  }
  /**
   * [__set description]
   * @date  2019-11-02
   * @param string     $key   [description]
   * @param [type]     $value [description]
   */
  public function __set(string $key, $value):void
  {
    if (is_object($this->payload)) {
      $this->payload->$key = $value;
      return;
    }
    $this->payload[$key] = $value;
  }
  /**
   * [__unset description]
   * @date  2019-11-02
   * @param string     $key [description]
   */
  public function __unset(string $key):void
  {
    if (is_object($this->payload)) {
      unset($this->payload->$key);
      return;
    }
    unset($this->payload[$key]);
  }
  /**
   * [__get description]
   * @date   2019-11-02
   * @param  string     $key [description]
   * @return [type]          [description]
   */
  public function __get(string $key)
  {
    if (is_object($this->payload)) return $this->payload->$key;
    return $this->payload[$key];
  }
  /**
   * [__toString description]
   * @date   2019-11-03
   * @return string     [description]
   */
  public function __toString():string
  {
    $buff = $refactor->toArray();
    if ($buff != null) return json_encode($buff);

    return json_encode($this->payload);
  }
  /**
   * [__debugInfo description]
   * @date   2019-11-03
   * @return [type]     [description]
   */
  public function __debugInfo():array
  {
    return [
      'payload' => $this->payload
    ];
  }
  /**
   * [setPayload description]
   * @date  2019-11-02
   * @param [type]     $payload [description]
   */
  public function setPayload($payload):void
  {
    $this->payload = $payload;
  }
  /**
   * [set_payload description]
   * @date  2019-11-02
   * @param array      $payload [description]
   */
  public function switchPayload(&$payload):void
  {
    $this->payload =& $payload;
  }
  /**
   * [toArray description]
   * @date   2019-11-02
   * @return array      [description]
   */
  public function toArray():array
  {
    if (is_array($this->payload)) return $this->payload;
    return json_decode(json_encode($this->payload), true);
  }
}
?>
