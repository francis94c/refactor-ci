<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RefactorCI {

  /**
   * [private description]
   * @var [type]
   */
  private $ci;

  private $primaryKey;

  function __construct($params=null) {
    $this->ci =& get_instance();
    $this->ci->load->config("refactor", false, true);
    $this->init($params == null ? [] : $params);
  }

  public function init(array $params):void {
    $this->primaryKey = $params['primary_key'] ?? 'id';
  }
  /**
   * [refactorObject description]
   * @param array  $object   [description]
   * @param string $ruleName [description]
   */
  function refactorObject(array &$object, string $ruleName):void {
    $rule = $this->ci->config->item("refactor_$ruleName");
    if ($rule == null) return; // No need to go further as rule doesn't exist.
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
    // Objects
    if (isset($rule['objects'])) {
      foreach($rule['objects'] as $field => $data) {
        $ids = json_decode($object[$field]);
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
        $object[$field] = [];
        foreach($ids as $id) {
          $this->ci->db->where($this->primaryKey, $id);
          $query = $this->ci->db->get($data['table']);
          if ($query->num_rows() == 0) {
            continue;
          }
          $object[$field][] = $query->result_array()[0];
          // Recursion
          if (isset($data['refactor'])) $this->refactorObject($object[$field][count($object[$field]) - 1], $data['refactor']);
        }
      }
    }
  }
  /**
   * [unset_values u]
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
  private function replace_fields(array &$object, &$rule):void {
    foreach ($rule['replace'] as $oldKey => $newKey) {
      $object[$newKey] = $object[$oldKey];
      unset($object[$oldKey]);
    }
  }
}
?>
