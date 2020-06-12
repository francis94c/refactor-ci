<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RefactorCI
{
  /**
   * [private description]
   * @var [type]
   */
  private $ci;

  /**
   * [private description]
   * @var [type]
   */
  private $primaryKey;

  /**
   * [public description]
   * @var [type]
   */
  public const PACKAGE = 'francis94c/refactor-ci';

  /**
   * [__construct description]
   * @date  2020-04-10
   * @param [type]     $params [description]
   */
  public function __construct($params=null)
  {
    $this->ci =& get_instance();
    $this->ci->load->config("refactor", false, true);
    $this->ci->load->splint('francis94c/jsonp', '+JSONP', null, 'jsonp');

    $this->init($params == null ? [] : $params);

    spl_autoload_register(function($name) {
      if ($name == 'RefactorPayload') {
        require(APPPATH . 'splints/' . self::PACKAGE . '/libraries/RefactorPayload.php');
        return;
      }

      if (file_exists(APPPATH . "payloads/$name.php")) {
        require(APPPATH . "payloads/$name.php");
        return;
      }

      if (file_exists(APPPATH . "libraries/refactor/$name.php")) {
        require(APPPATH . "libraries/refactor/$name.php"); // @deprecated
      }      
    });
  }
  /**
   * [init description]
   * @date  2019-11-02
   * @param array      $params [description]
   */
  public function init(array $params):void
  {
    $this->primaryKey = $params['primary_key'] ?? 'id';
  }
  /**
   * [load description]
   * @date   2019-11-02
   * @return RefactorCI [description]
   */
  public function load($class):RefactorCI
  {
    require_once(APPPATH.'libraries/refactor/'.$class.'.php');
    return $this;
  }
  /**
   * [payload description]
   * @date   2019-11-02
   * @param  string     $class  [description]
   * @param  [type]     $object [description]
   * @return [type]             [description]
   */
  public function payload(string $class, $object)
  {
    return (new $class($object))->toArray();
  }
  /**
   * [array description]
   * @date   2019-11-02
   * @param  string     $class [description]
   * @param  array      $array [description]
   * @return array             [description]
   */
  public function array(string $class, array $array):array
  {
    $refactor = new $class();
    $buff;
    for ($x = 0; $x < count($array); $x++) {
      $refactor->switchPayload($array[$x]);
      $buff = $refactor->toArray();
      if ($buff != null) $array[$x] = $buff;
    }
    return $array;
  }
  /**
   * [run description]
   * @param array|string  $object   [description]
   * @param string        $ruleName [description]
   */
  function run(array &$object, $ruleName):void
  {
    if ($object == null) return;
    // Reolve Rules.
    if (is_scalar($ruleName)) {
      $rule = $this->ci->config->item("refactor_$ruleName");
    } else {
      // Rule was probablt passed in as an array (associative) and we support
      // that.
      $rule = is_array($ruleName) ? $ruleName : null;
    }

    if ($rule == null) return; // No need to go further as rule doesn't exist.
    // Keep
    if (isset($rule['keep'])) {
      $keys = array_keys($object);
      for ($x = 0; $x < count($object); $x++) {
        if (!in_array($keys[$X], $rule['keep'])) {
          unset($object[$keys[$x]]);
        }
      }
    }
    // Unset
    if (isset($rule['unset'])) {
      $this->unset_values($object, $rule);
    }
    // Replace
    if (isset($rule['replace'])) {
      $this->replace_fields($object, $rule);
    }
    // Bools
    if (isset($rule['bools'])) {
      foreach($rule['bools'] as $boolKey) {
        $object[$boolKey] = $object[$boolKey] == 1 || $object[$boolKey] == 'true';
      }
    }
    // Cast
    if (isset($rule['cast']))  {
      $this->cast_fields($object, $rule);
    }
    // Inflate
    if (isset($rule['inflate'])) {
      foreach($rule['inflate'] as $field => $data) {
        $ids = json_decode($object[$field], true);
        if (is_scalar($ids)) {
          // JSON Array wasn't supplied. Let's treat it as a scaler ID.
          $this->ci->db->where($this->primaryKey, $ids);
          $query = $this->ci->db->get($data['table']);
          if ($query->num_rows() == 0) {
            $object[$field] = json_encode (json_decode ("{}"));
            continue;
          }
          $object[$field] = $query->result_array()[0];
          if (isset($data['refactor'])) $this->run($object[$field], $data['refactor']);
          continue;
        }
        if (isset($data['path'])) {
          if ($ids == null) return;
          $object[$field] = $ids;
          $this->ci->jsonp->parse($object[$field]);
          if (is_array($object[$field])) {
            $refs = $this->ci->jsonp->get_reference($data['path']);
            for ($x = 0; $x < count($refs); $x++) {
              $refs[$x] = $this->inflate_value($data['table'], $refs[$x]);
              // Recursion
              if (isset($data['refactor'])) $this->run($refs[$x], $data['refactor']);
            }
          } else {
            $this->ci->jsonp->set($data['path'], $this->inflate_value($data['table'], $ids));
            // Recursion
            if (isset($data['refactor'])) $this->run($this->ci->jsonp->get_reference($data['path']), $data['refactor']);
          }
          return;
        }
        $object[$field] = [];
        if ($ids == null) return;
        foreach($ids as $id) {
          $this->ci->db->where($this->primaryKey, $id);
          $query = $this->ci->db->get($data['table']);
          if ($query->num_rows() == 0) {
            continue;
          }
          $object[$field][] = $query->result_array()[0];
          // Recursion
          if (isset($data['refactor'])) $this->run($object[$field][count($object[$field]) - 1], $data['refactor']);
        }
      }
    }
  }
  private function inflate_value(string $table, $value)
  {
    $this->ci->db->where($this->primaryKey, $value);
    $query = $this->ci->db->get($table);
    return $query->num_rows() > 0 ? $query->result_array()[0] : null;
  }
  /**
   * [unset_values description]
   * @param array  $object Object to Refactor.
   * @param array  $rule   Rule data, containing keys to unset in  the given
   *                       associative array.
   */
  private function unset_values(array &$object, &$rule):void {
    foreach($rule['unset'] as $key) {
      unset($object[$key]);
    }
  }
  /**
   * [replace_fields description]
   * @param array  $object [description]
   * @param [type] $rule   [description]
   */
  private function replace_fields(array &$object, &$rule):void
  {
    foreach ($rule['replace'] as $oldKey => $newKey) {
      $object[$newKey] = $object[$oldKey];
      unset($object[$oldKey]);
    }
  }
  /**
   * [cast_fields description]
   * @param array  $object [description]
   * @param [type] $rule   [description]
   */
  private function cast_fields(array &$object, &$rule):void
  {
    foreach ($rule['cast'] as $key => $type) {
      switch ($type) {
        case 'int':
          $object[$key] = (int) $object[$key];
          break;
        case 'string':
          $object[$key] = (string) $object[$key];
          break;
      }
    }
  }
}
?>
