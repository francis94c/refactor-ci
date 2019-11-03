<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RefactorArray implements ArrayAccess, Countable
{
  /**
   * [private description]
   * @var [type]
   */
  private $data = array();
  /**
   * [offsetSet description]
   * @date  2019-11-02
   * @param [type]     $offset [description]
   * @param [type]     $value  [description]
   */
  public function offsetSet($offset, $value)
  {
    $this->data[$offset] = $value;
  }
  /**
   * [offsetExists description]
   * @date  2019-11-02
   * @param [type]     $offset [description]
   */
  public function offsetExists($offset)
  {
    return isset($this->data[$offset]);
  }
  /**
   * [offsetUnset description]
   * @date  2019-11-02
   * @param [type]     $offset [description]
   */
  public function offsetUnset($offset)
  {
    unset($this->data[$offset]);
  }
  /**
   * [offsetGet description]
   * @date  2019-11-02
   * @param [type]     $offset [description]
   */
  public function offsetGet($offset)
  {
    return ($this->offsetExists($offset)) ? $this->data[$offset] : null;
  }
  /**
   * [count description]
   * @date   2019-11-02
   * @return [type]     [description]
   */
  public function count()
  {
    return count($this->_data);
  }
}
