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
  function run(array &$object, string $ruleName):void {
    $rule = $this->ci->config->item("refactor_$ruleName");
    if ($rule == null) return; // No need to go further as rule doesn't exist.

    // Unset
    if (isset($rule['unset'])) {
      foreach($rule['unset'] as $key) {
        unset($object[$key]);
      }
    }

    // Replace
    if (isset($rule['replace'])) {
      foreach ($rule['replace'] as $oldKey => $newKey) {
        $object[$newKey] = $object[$oldKey];
        unset($object[$oldKey]);
      }
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
          if (isset($data['refactor'])) $this->run($object[$field][count($object[$field]) - 1], $data['refactor']);
        }
      }
    }
  }
}
?>
